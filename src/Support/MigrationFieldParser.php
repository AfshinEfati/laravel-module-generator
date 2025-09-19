<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Str;

class MigrationFieldParser
{
    public static function parse(string $modelName, ?string $hint = null): array
    {
        $studly = Str::studly($modelName);
        $path   = self::resolveMigrationPath($studly, $hint);

        if (!$path || !is_file($path)) {
            return [
                'path'       => null,
                'table'      => null,
                'fields'     => [],
                'relations'  => [],
            ];
        }

        $code = file_get_contents($path);
        if ($code === false) {
            return [
                'path'       => $path,
                'table'      => null,
                'fields'     => [],
                'relations'  => [],
            ];
        }

        $code = self::stripComments($code);

        $blocks = self::extractSchemaBlocks($code);

        $fields    = [];
        $relations = [];
        $tableName = null;

        foreach ($blocks as $block) {
            [$tbl, $body] = $block;

            if (!$tableName) {
                $tableName = $tbl;
            }

            $statements = self::extractTableStatements($body);

            foreach ($statements as $statement) {
                [$method, $argsString, $chainString] = $statement;
                $methodLower = strtolower($method);

                $args  = self::splitArguments($argsString);
                $chain = self::parseChain($chainString);

                $columns = self::resolveColumns($methodLower, $args);

                foreach ($columns as $column) {
                    $name = $column['name'];

                    if (!isset($fields[$name])) {
                        $fields[$name] = [
                            'name'         => $name,
                            'method'       => $column['method'],
                            'type'         => $column['type'],
                            'cast'         => $column['cast'],
                            'length'       => $column['length'] ?? null,
                            'scale'        => $column['scale'] ?? null,
                            'nullable'     => false,
                            'unique'       => false,
                            'default'      => null,
                            'enum'         => $column['enum'] ?? null,
                            'auto_managed' => (bool) ($column['auto_managed'] ?? false),
                            'foreign'      => null,
                        ];
                    }

                    if (self::chainHas($chain, 'nullable')) {
                        $fields[$name]['nullable'] = true;
                    }
                    if (self::chainHas($chain, 'unique')) {
                        $fields[$name]['unique'] = true;
                    }

                    if (($default = self::chainFirstArgument($chain, 'default')) !== null) {
                        $fields[$name]['default'] = $default;
                    }

                    if (!empty($column['enum']) && !$fields[$name]['enum']) {
                        $fields[$name]['enum'] = $column['enum'];
                    }

                    $foreign = self::buildForeignMetadata($methodLower, $args, $chain, $column, $name);

                    if ($foreign) {
                        $fields[$name]['foreign'] = $foreign;

                        $relationKey = $foreign['relation'] ?? Str::camel(self::stripIdSuffix($name));

                        if (!isset($relations[$relationKey])) {
                            $relations[$relationKey] = [
                                'name'          => $relationKey,
                                'type'          => $foreign['type'],
                                'foreign_key'   => $name,
                                'table'         => $foreign['table'] ?? null,
                                'references'    => $foreign['references'] ?? 'id',
                                'related_model' => $foreign['related'] ?? Str::studly(self::stripIdSuffix($name)),
                            ];
                        }
                    }
                }
            }
        }

        return [
            'path'      => $path,
            'table'     => $tableName,
            'fields'    => $fields,
            'relations' => $relations,
        ];
    }

    public static function buildFillableFromFields(array $fields): array
    {
        $fillable = [];
        foreach ($fields as $name => $meta) {
            if (!empty($meta['auto_managed'])) {
                continue;
            }
            if (in_array($meta['method'], ['id', 'increments', 'bigincrements'], true)) {
                continue;
            }
            $fillable[] = $name;
        }
        return array_values(array_unique($fillable));
    }

    public static function buildCastsFromFields(array $fields): array
    {
        $casts = [];
        foreach ($fields as $name => $meta) {
            if (!empty($meta['auto_managed'])) {
                continue;
            }
            if (!empty($meta['foreign']) && empty($meta['cast'])) {
                $casts[$name] = 'int';
                continue;
            }

            $cast = $meta['cast'] ?? null;
            if ($cast && $cast !== 'string') {
                $casts[$name] = $cast;
            } elseif ($cast === 'string' && str_ends_with($name, '_id')) {
                $casts[$name] = 'int';
            }
        }
        return $casts;
    }

    public static function buildValidationRules(array $fields, ?string $table): array
    {
        $store  = [];
        $update = [];

        foreach ($fields as $name => $meta) {
            if (!empty($meta['auto_managed'])) {
                continue;
            }

            $rules = self::rulesForField($name, $meta, $table, true);
            $store[$name] = implode('|', $rules);

            $updateRules = self::rulesForField($name, $meta, $table, false);
            $update[$name] = implode('|', $updateRules);
        }

        return [$store, $update];
    }

    public static function normaliseFieldMetadata(array $fields, string $baseNamespace): array
    {
        $normalised = [];
        foreach ($fields as $name => $meta) {
            if (!empty($meta['auto_managed'])) {
                continue;
            }

            $entry = [
                'type'     => $meta['type'],
                'cast'     => $meta['cast'],
                'nullable' => (bool) ($meta['nullable'] ?? false),
                'unique'   => (bool) ($meta['unique'] ?? false),
                'enum'     => $meta['enum'] ?? null,
            ];

            if (!empty($meta['foreign'])) {
                $related = $meta['foreign']['related'] ?? Str::studly(self::stripIdSuffix($name));
                $entry['foreign'] = [
                    'table'         => $meta['foreign']['table'] ?? null,
                    'references'    => $meta['foreign']['references'] ?? 'id',
                    'related_model' => $baseNamespace . '\\Models\\' . $related,
                ];
            }

            $normalised[$name] = $entry;
        }

        return $normalised;
    }

    private static function stripComments(string $code): string
    {
        $code = preg_replace('#/\*.*?\*/#s', '', $code);
        $code = preg_replace('#//.*$#m', '', $code);
        return $code ?? '';
    }

    private static function extractSchemaBlocks(string $code): array
    {
        $pattern = '/Schema::(?:create|table)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*function\s*\([^)]*\)\s*\{([\s\S]*?)\}\s*\);/m';
        preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);

        $blocks = [];
        foreach ($matches as $match) {
            $blocks[] = [$match[1], $match[2]];
        }

        return $blocks;
    }

    private static function extractTableStatements(string $body): array
    {
        $pattern = '/\$table->([a-zA-Z0-9_]+)\s*\((.*?)\)(.*?);/s';
        preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);

        $out = [];
        foreach ($matches as $match) {
            $out[] = [$match[1], trim($match[2]), $match[3]];
        }
        return $out;
    }

    private static function resolveColumns(string $method, array $args): array
    {
        $columns = [];

        switch ($method) {
            case 'timestamps':
            case 'timestampstz':
                $columns[] = self::columnMeta('created_at', $method, 'timestamp', 'datetime', ['auto_managed' => true]);
                $columns[] = self::columnMeta('updated_at', $method, 'timestamp', 'datetime', ['auto_managed' => true]);
                break;
            case 'softdeletes':
            case 'softdeletestz':
                $name = $args[0] ?? 'deleted_at';
                $name = self::trimQuotes($name) ?: 'deleted_at';
                $columns[] = self::columnMeta($name, $method, 'timestamp', 'datetime', ['auto_managed' => true]);
                break;
            case 'remembertoken':
                $columns[] = self::columnMeta('remember_token', $method, 'string', 'string', ['auto_managed' => true]);
                break;
            case 'morphs':
            case 'nullablemorphs':
            case 'uuidmorphs':
            case 'nullableuuidmorphs':
                $base   = self::trimQuotes($args[0] ?? 'morphable') ?: 'morphable';
                $idName = $method === 'uuidmorphs' || $method === 'nullableuuidmorphs' ? $base . '_uuid' : $base . '_id';
                $columns[] = self::columnMeta($base . '_type', $method, 'string', 'string');
                $columns[] = self::columnMeta($idName, $method, 'string', $method === 'uuidmorphs' || $method === 'nullableuuidmorphs' ? 'string' : 'int');
                break;
            case 'id':
            case 'increments':
            case 'bigincrements':
                $name = self::trimQuotes($args[0] ?? 'id') ?: 'id';
                $columns[] = self::columnMeta($name, $method, 'integer', 'int', ['auto_managed' => true]);
                break;
            default:
                $name = self::resolveColumnName($method, $args);
                if ($name) {
                    $type = self::mapColumnType($method);
                    $columns[] = self::columnMeta($name, $method, $type['type'], $type['cast'], [
                        'length' => $type['length'] ?? self::parseLength($method, $args),
                        'scale'  => $type['scale'] ?? self::parseScale($method, $args),
                        'enum'   => $type['enum'] ?? self::parseEnum($method, $args),
                    ]);
                }
                break;
        }

        return $columns;
    }

    private static function columnMeta(string $name, string $method, string $type, ?string $cast, array $extra = []): array
    {
        return array_merge([
            'name'         => $name,
            'method'       => $method,
            'type'         => $type,
            'cast'         => $cast,
            'auto_managed' => $extra['auto_managed'] ?? false,
        ], $extra);
    }

    private static function resolveColumnName(string $method, array $args): ?string
    {
        if (!empty($args)) {
            $first = $args[0];
            $trim  = self::trimQuotes($first);
            if ($trim !== null) {
                return $trim;
            }
        }

        if (in_array($method, ['uuid', 'ulid'])) {
            return 'uuid';
        }

        if ($method === 'foreignidfor') {
            if (!empty($args)) {
                $class = self::parseClassReference($args[0]);
                $column = isset($args[1]) ? self::trimQuotes($args[1]) : null;
                if ($column) {
                    return $column;
                }
                if ($class) {
                    return Str::snake(class_basename($class)) . '_id';
                }
            }
        }

        return null;
    }

    private static function mapColumnType(string $method): array
    {
        return match ($method) {
            'string', 'char' => ['type' => 'string', 'cast' => 'string'],
            'text', 'longtext', 'mediumtext', 'tinytext' => ['type' => 'text', 'cast' => 'string'],
            'integer', 'bigint', 'biginteger', 'unsignedbiginteger', 'unsignedinteger', 'unsignedsmallinteger', 'smallinteger', 'tinyinteger' => ['type' => 'integer', 'cast' => 'int'],
            'foreignid', 'foreignuuid', 'foreignulid' => ['type' => 'integer', 'cast' => 'int'],
            'foreignidfor' => ['type' => 'integer', 'cast' => 'int'],
            'decimal' => ['type' => 'decimal', 'cast' => null],
            'float', 'double' => ['type' => 'float', 'cast' => 'float'],
            'boolean' => ['type' => 'boolean', 'cast' => 'bool'],
            'json', 'jsonb' => ['type' => 'json', 'cast' => 'array'],
            'date' => ['type' => 'date', 'cast' => 'date'],
            'datetime', 'datetimetz', 'timestamp', 'timestamptz' => ['type' => 'datetime', 'cast' => 'datetime'],
            'time', 'timetz' => ['type' => 'time', 'cast' => 'string'],
            'enum', 'set' => ['type' => 'enum', 'cast' => 'string'],
            'uuid' => ['type' => 'uuid', 'cast' => 'string'],
            'ulid' => ['type' => 'string', 'cast' => 'string'],
            default => ['type' => 'string', 'cast' => 'string'],
        };
    }

    private static function parseLength(string $method, array $args): ?int
    {
        if (in_array($method, ['string', 'char'], true) && isset($args[1])) {
            $len = self::parseScalarValue($args[1]);
            return is_numeric($len) ? (int) $len : null;
        }
        return null;
    }

    private static function parseScale(string $method, array $args): ?int
    {
        if ($method === 'decimal') {
            $scale = $args[2] ?? $args[1] ?? null;
            $scale = self::parseScalarValue($scale);
            return is_numeric($scale) ? (int) $scale : null;
        }
        return null;
    }

    private static function parseEnum(string $method, array $args): ?array
    {
        if (in_array($method, ['enum', 'set'], true) && isset($args[1])) {
            $values = self::parseArrayValues($args[1]);
            return $values ?: null;
        }
        return null;
    }

    private static function buildForeignMetadata(string $method, array $args, array $chain, array $column, string $columnName): ?array
    {
        $isForeignMethod = in_array($method, ['foreignid', 'foreignidfor', 'foreignuuid', 'foreignulid'], true);
        $chainDefines    = self::chainHas($chain, 'constrained') || self::chainHas($chain, 'references');

        if (!$isForeignMethod && !$chainDefines) {
            return null;
        }

        $foreign = [
            'type'       => 'belongsTo',
            'references' => 'id',
            'table'      => null,
            'related'    => null,
            'relation'   => Str::camel(self::stripIdSuffix($columnName)),
        ];

        if ($method === 'foreignidfor' && isset($args[0])) {
            $class = self::parseClassReference($args[0]);
            if ($class) {
                $foreign['related'] = class_basename($class);
                $foreign['table']   = Str::snake(Str::pluralStudly($foreign['related']));
            }
        }

        if ($method === 'foreignid' && !empty($args)) {
            $columnRef = self::trimQuotes($args[0]);
            if ($columnRef) {
                $foreign['related'] = Str::studly(self::stripIdSuffix($columnRef));
            }
        }

        if (($ref = self::chainFirstArgument($chain, 'references')) !== null) {
            $foreign['references'] = self::trimQuotes($ref) ?? $ref;
        }

        if (($table = self::chainFirstArgument($chain, 'on')) !== null) {
            $foreign['table'] = self::trimQuotes($table) ?? $table;
        }

        if (($table = self::chainFirstArgument($chain, 'constrained')) !== null) {
            $foreign['table'] = self::trimQuotes($table) ?? $table;
        }

        if (empty($foreign['table'])) {
            $foreign['table'] = Str::snake(Str::pluralStudly($foreign['related'] ?? Str::studly(self::stripIdSuffix($columnName))));
        }

        if (empty($foreign['related'])) {
            $foreign['related'] = Str::studly(self::stripIdSuffix($columnName));
        }

        return $foreign;
    }

    private static function chainHas(array $chain, string $method): bool
    {
        foreach ($chain as $part) {
            if (strtolower($part['method']) === strtolower($method)) {
                return true;
            }
        }
        return false;
    }

    private static function chainFirstArgument(array $chain, string $method): mixed
    {
        foreach ($chain as $part) {
            if (strtolower($part['method']) === strtolower($method)) {
                return $part['arguments'][0] ?? null;
            }
        }
        return null;
    }

    private static function parseChain(string $chainString): array
    {
        $pattern = '/->([a-zA-Z0-9_]+)\((.*?)\)/s';
        preg_match_all($pattern, $chainString, $matches, PREG_SET_ORDER);

        $parts = [];
        foreach ($matches as $match) {
            $parts[] = [
                'method'    => $match[1],
                'arguments' => self::splitArguments($match[2]),
            ];
        }
        return $parts;
    }

    private static function splitArguments(string $args): array
    {
        $args = trim($args);
        if ($args === '') {
            return [];
        }

        $result = [];
        $current = '';
        $depth   = 0;
        $inSingle = false;
        $inDouble = false;

        $length = strlen($args);
        for ($i = 0; $i < $length; $i++) {
            $char = $args[$i];

            if ($char === "'" && !$inDouble) {
                $prev = $i > 0 ? $args[$i - 1] : null;
                if ($prev !== '\\') {
                    $inSingle = !$inSingle;
                }
                $current .= $char;
                continue;
            }

            if ($char === '"' && !$inSingle) {
                $prev = $i > 0 ? $args[$i - 1] : null;
                if ($prev !== '\\') {
                    $inDouble = !$inDouble;
                }
                $current .= $char;
                continue;
            }

            if (!$inSingle && !$inDouble) {
                if ($char === '(' || $char === '[' || $char === '{') {
                    $depth++;
                }
                if ($char === ')' || $char === ']' || $char === '}') {
                    $depth--;
                }
                if ($char === ',' && $depth === 0) {
                    $result[] = trim($current);
                    $current = '';
                    continue;
                }
            }

            $current .= $char;
        }

        if ($current !== '') {
            $result[] = trim($current);
        }

        return $result;
    }

    private static function parseScalarValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $trim = strtolower(trim($value));
            if ($trim === 'null') {
                return null;
            }
            if ($trim === 'true') {
                return true;
            }
            if ($trim === 'false') {
                return false;
            }
            if (is_numeric($trim)) {
                return $trim + 0;
            }
            return self::trimQuotes($value);
        }
        return $value;
    }

    private static function parseArrayValues(string $value): array
    {
        $value = trim($value);
        if (!str_starts_with($value, '[') || !str_ends_with($value, ']')) {
            return [];
        }
        $inner = substr($value, 1, -1);
        $parts = self::splitArguments($inner);
        $values = [];
        foreach ($parts as $part) {
            $parsed = self::parseScalarValue($part);
            if ($parsed !== null) {
                $values[] = $parsed;
            }
        }
        return $values;
    }

    private static function trimQuotes(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ((str_starts_with($value, "'") && str_ends_with($value, "'")) || (str_starts_with($value, '"') && str_ends_with($value, '"'))) {
            return stripcslashes(substr($value, 1, -1));
        }
        return $value;
    }

    private static function parseClassReference(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if (str_ends_with($value, '::class')) {
            return trim(substr($value, 0, -7), '\\ ');
        }
        return null;
    }

    private static function stripIdSuffix(string $value): string
    {
        return str_ends_with($value, '_id') ? substr($value, 0, -3) : $value;
    }

    private static function resolveMigrationPath(string $studly, ?string $hint): ?string
    {
        $basePath = self::migrationsPath();
        if (!is_dir($basePath)) {
            return null;
        }

        if ($hint) {
            $candidate = self::resolveHintPath($hint, $basePath);
            if ($candidate) {
                return $candidate;
            }
        }

        $patterns = [
            $basePath . DIRECTORY_SEPARATOR . '*'. $studly . '*.php',
            $basePath . DIRECTORY_SEPARATOR . '*' . Str::snake(Str::pluralStudly($studly)) . '*.php',
            $basePath . DIRECTORY_SEPARATOR . '*' . Str::snake(Str::singular($studly)) . '*.php',
        ];

        foreach ($patterns as $pattern) {
            $files = glob($pattern);
            if (!empty($files)) {
                rsort($files);
                return $files[0];
            }
        }

        return null;
    }

    private static function resolveHintPath(string $hint, string $basePath): ?string
    {
        if (is_file($hint)) {
            return realpath($hint) ?: $hint;
        }

        $potential = $basePath . DIRECTORY_SEPARATOR . $hint;
        if (is_file($potential)) {
            return realpath($potential) ?: $potential;
        }

        $pattern = $basePath . DIRECTORY_SEPARATOR . '*' . $hint . '*.php';
        $files   = glob($pattern);
        if (!empty($files)) {
            rsort($files);
            return $files[0];
        }

        return null;
    }

    private static function migrationsPath(): string
    {
        if (function_exists('database_path')) {
            return database_path('migrations');
        }
        if (function_exists('base_path')) {
            return base_path('database/migrations');
        }
        return getcwd() . '/database/migrations';
    }

    private static function rulesForField(string $name, array $meta, ?string $table, bool $forCreate): array
    {
        $rules = [];

        if ($forCreate) {
            $rules[] = $meta['nullable'] ? 'nullable' : 'required';
        } else {
            $rules[] = 'sometimes';
            if ($meta['nullable']) {
                $rules[] = 'nullable';
            } else {
                $rules[] = 'required';
            }
        }

        foreach (self::rulesFromType($meta['type'], $meta) as $rule) {
            if (!in_array($rule, $rules, true)) {
                $rules[] = $rule;
            }
        }

        if (($meta['unique'] ?? false) && $table) {
            $rules[] = 'unique:' . $table . ',' . $name;
        }

        if (!empty($meta['foreign']['table'])) {
            $refTable = $meta['foreign']['table'];
            $refCol   = $meta['foreign']['references'] ?? 'id';
            $rules[]  = 'exists:' . $refTable . ',' . $refCol;
        }

        if (!empty($meta['enum']) && is_array($meta['enum'])) {
            $rules[] = 'in:' . implode(',', array_map(fn ($v) => (string) $v, $meta['enum']));
        }

        if (str_contains($name, 'email')) {
            $rules[] = 'email';
            $rules[] = 'max:255';
        }

        if (str_contains($name, 'slug') || str_contains($name, 'code')) {
            if (!in_array('string', $rules, true)) {
                $rules[] = 'string';
            }
            $rules[] = 'max:255';
        }

        return $rules;
    }

    private static function rulesFromType(string $type, array $meta): array
    {
        return match ($type) {
            'integer' => ['integer'],
            'decimal', 'float' => isset($meta['scale']) ? ['numeric'] : ['numeric'],
            'boolean' => ['boolean'],
            'json' => ['array'],
            'date' => ['date'],
            'datetime' => ['date'],
            'time' => ['date_format:H:i'],
            'enum' => ['string'],
            default => ['string'],
        };
    }
}
