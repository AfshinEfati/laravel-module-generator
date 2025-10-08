<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Str;

class RuntimeFieldParser
{
    /**
     * Inspect the database table associated with the given model class and extract field metadata.
     *
     * @return array{table: string|null, fields: array<string, array<string, mixed>>, relations: array<string, array<string, mixed>>}
     */
    public static function parse(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) {
            return ['table' => null, 'fields' => [], 'relations' => []];
        }

        try {
            $model = new $modelFqcn();
        } catch (\Throwable $e) {
            return ['table' => null, 'fields' => [], 'relations' => []];
        }

        if (!$model instanceof EloquentModel) {
            return ['table' => null, 'fields' => [], 'relations' => []];
        }

        try {
            $connection = $model->getConnection();
        } catch (\Throwable $e) {
            return ['table' => null, 'fields' => [], 'relations' => []];
        }

        if (!$connection instanceof ConnectionInterface) {
            return ['table' => null, 'fields' => [], 'relations' => []];
        }

        $table = $model->getTable();
        $schema = $connection->getSchemaBuilder();

        if (!$schema instanceof SchemaBuilder) {
            return ['table' => $table, 'fields' => [], 'relations' => []];
        }

        $columns = self::fetchColumns($schema, $table);
        if (empty($columns)) {
            return ['table' => $table, 'fields' => [], 'relations' => []];
        }

        $uniqueColumns  = self::detectUniqueColumns($connection, $table);
        $foreignColumns = self::detectForeignKeys($connection, $table);

        $fields    = [];
        $relations = [];

        foreach ($columns as $column) {
            $name = $column['name'] ?? null;
            if (!is_string($name) || $name === '') {
                continue;
            }

            $typeInfo = self::normalizeType((string) ($column['type'] ?? $column['type_name'] ?? 'string'), $column);

            $nullable = null;
            if (array_key_exists('nullable', $column)) {
                $nullable = (bool) $column['nullable'];
            } elseif (array_key_exists('notnull', $column)) {
                $nullable = !$column['notnull'];
            }

            $fieldMeta = [
                'name'         => $name,
                'method'       => 'runtime',
                'type'         => $typeInfo['type'],
                'cast'         => $typeInfo['cast'],
                'length'       => self::extractInt($column['length'] ?? ($column['precision'] ?? null)),
                'scale'        => self::extractInt($column['scale'] ?? null),
                'nullable'     => $nullable ?? true,
                'unique'       => isset($uniqueColumns[$name]),
                'default'      => $column['default'] ?? null,
                'enum'         => $typeInfo['enum'],
                'auto_managed' => self::isAutoManagedColumn($name, $column),
                'foreign'      => null,
            ];

            if (isset($foreignColumns[$name])) {
                $foreign = $foreignColumns[$name];
                $fieldMeta['foreign'] = $foreign;

                $relations[$foreign['relation']] = [
                    'name'          => $foreign['relation'],
                    'type'          => $foreign['type'],
                    'foreign_key'   => $name,
                    'table'         => $foreign['table'],
                    'references'    => $foreign['references'],
                    'related_model' => $foreign['related'],
                ];
            } elseif (self::looksLikeForeignKey($name)) {
                $foreign = self::buildForeignFromName($name);
                $fieldMeta['foreign'] = $foreign;

                $relations[$foreign['relation']] = [
                    'name'          => $foreign['relation'],
                    'type'          => $foreign['type'],
                    'foreign_key'   => $name,
                    'table'         => $foreign['table'],
                    'references'    => $foreign['references'],
                    'related_model' => $foreign['related'],
                ];
            }

            $fields[$name] = $fieldMeta;
        }

        return [
            'table'     => $table,
            'fields'    => $fields,
            'relations' => $relations,
        ];
    }

    private static function fetchColumns(SchemaBuilder $schema, string $table): array
    {
        $columns = [];

        if (method_exists($schema, 'getColumns')) {
            try {
                $columns = $schema->getColumns($table);
            } catch (\Throwable $e) {
                $columns = [];
            }
        }

        if (!empty($columns)) {
            return array_values(array_filter($columns, static function ($column) {
                return is_array($column) && isset($column['name']);
            }));
        }

        try {
            $names = $schema->getColumnListing($table);
        } catch (\Throwable $e) {
            $names = [];
        }

        return array_map(static fn ($name) => ['name' => $name], array_filter($names, static fn ($name) => is_string($name) && $name !== ''));
    }

    private static function detectUniqueColumns(ConnectionInterface $connection, string $table): array
    {
        $result = [];

        try {
            $schemaManager = $connection->getDoctrineSchemaManager();
        } catch (\Throwable $e) {
            $schemaManager = null;
        }

        if ($schemaManager === null) {
            return $result;
        }

        $tableName = self::qualifyTable($connection, $table);

        try {
            $indexes = $schemaManager->listTableIndexes($tableName);
        } catch (\Throwable $e) {
            return $result;
        }

        foreach ($indexes as $index) {
            if (!$index->isUnique()) {
                continue;
            }

            foreach ($index->getColumns() as $column) {
                if (is_string($column) && $column !== '') {
                    $result[self::stripPrefix($column, $connection->getTablePrefix())] = true;
                }
            }
        }

        return $result;
    }

    private static function detectForeignKeys(ConnectionInterface $connection, string $table): array
    {
        $foreign = [];

        try {
            $schemaManager = $connection->getDoctrineSchemaManager();
        } catch (\Throwable $e) {
            $schemaManager = null;
        }

        if ($schemaManager === null) {
            return $foreign;
        }

        $tableName = self::qualifyTable($connection, $table);

        try {
            $constraints = $schemaManager->listTableForeignKeys($tableName);
        } catch (\Throwable $e) {
            return $foreign;
        }

        $prefix = $connection->getTablePrefix();

        foreach ($constraints as $constraint) {
            $foreignTable = self::stripPrefix($constraint->getForeignTableName(), $prefix);
            $localColumns = $constraint->getLocalColumns();
            $foreignCols  = $constraint->getForeignColumns();

            foreach ($localColumns as $index => $local) {
                if (!is_string($local) || $local === '') {
                    continue;
                }

                $local = self::stripPrefix($local, $prefix);
                $foreignColumn = $foreignCols[$index] ?? $foreignCols[0] ?? 'id';

                $base     = self::relationBaseName($local, $foreignTable);
                $relation = Str::camel($base);
                $related  = Str::studly($base);

                $foreign[$local] = [
                    'type'       => 'belongsTo',
                    'references' => $foreignColumn,
                    'table'      => $foreignTable,
                    'related'    => $related,
                    'relation'   => $relation,
                ];
            }
        }

        return $foreign;
    }

    private static function normalizeType(string $type, array $column): array
    {
        $type = strtolower($type);
        $enum = null;

        if (isset($column['allowed']) && is_array($column['allowed'])) {
            $enum = $column['allowed'];
        } elseif (isset($column['enum']) && is_array($column['enum'])) {
            $enum = $column['enum'];
        }

        if (str_contains($type, 'int')) {
            if ($type === 'tinyint' && self::extractInt($column['length'] ?? null) === 1) {
                return ['type' => 'boolean', 'cast' => 'bool', 'enum' => $enum];
            }

            return ['type' => 'integer', 'cast' => 'int', 'enum' => $enum];
        }

        if (str_contains($type, 'decimal') || str_contains($type, 'numeric')) {
            return ['type' => 'decimal', 'cast' => null, 'enum' => $enum];
        }

        if (in_array($type, ['float', 'double', 'real'], true)) {
            return ['type' => 'float', 'cast' => 'float', 'enum' => $enum];
        }

        if (str_contains($type, 'bool')) {
            return ['type' => 'boolean', 'cast' => 'bool', 'enum' => $enum];
        }

        if (in_array($type, ['json', 'jsonb', 'array'], true)) {
            return ['type' => 'json', 'cast' => 'array', 'enum' => $enum];
        }

        if (in_array($type, ['date'], true)) {
            return ['type' => 'date', 'cast' => 'date', 'enum' => $enum];
        }

        if (str_contains($type, 'time') || str_contains($type, 'timestamp') || str_contains($type, 'datetime')) {
            return ['type' => 'datetime', 'cast' => 'datetime', 'enum' => $enum];
        }

        if ($type === 'enum' && $enum !== null) {
            return ['type' => 'string', 'cast' => 'string', 'enum' => $enum];
        }

        if ($type === 'uuid' || str_contains($type, 'uuid')) {
            return ['type' => 'uuid', 'cast' => 'string', 'enum' => $enum];
        }

        return ['type' => 'string', 'cast' => 'string', 'enum' => $enum];
    }

    private static function isAutoManagedColumn(string $name, array $column): bool
    {
        if (in_array($name, ['created_at', 'updated_at', 'deleted_at', 'remember_token'], true)) {
            return true;
        }

        $auto = $column['auto_increment'] ?? ($column['autoincrement'] ?? null);
        if ($auto !== null) {
            return (bool) $auto;
        }

        return false;
    }

    private static function looksLikeForeignKey(string $name): bool
    {
        return Str::endsWith($name, '_id');
    }

    /**
     * @return array{type: string, references: string, table: string, related: string, relation: string}
     */
    private static function buildForeignFromName(string $column): array
    {
        $base = self::stripIdSuffix($column);
        $table = Str::snake(Str::pluralStudly($base));

        return [
            'type'       => 'belongsTo',
            'references' => 'id',
            'table'      => $table,
            'related'    => Str::studly($base),
            'relation'   => Str::camel($base),
        ];
    }

    private static function stripIdSuffix(string $value): string
    {
        if (Str::endsWith($value, '_id')) {
            $value = substr($value, 0, -3);
        }

        return $value ?: 'related';
    }

    private static function relationBaseName(string $column, string $foreignTable): string
    {
        $base = self::stripIdSuffix($column);

        if ($base !== 'related') {
            return $base;
        }

        return Str::singular($foreignTable);
    }

    private static function qualifyTable(ConnectionInterface $connection, string $table): string
    {
        $prefix = $connection->getTablePrefix();

        if ($prefix && !Str::startsWith($table, $prefix)) {
            return $prefix . $table;
        }

        return $table;
    }

    private static function stripPrefix(string $value, ?string $prefix): string
    {
        if ($prefix && Str::startsWith($value, $prefix)) {
            return substr($value, strlen($prefix));
        }

        return $value;
    }

    private static function extractInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
