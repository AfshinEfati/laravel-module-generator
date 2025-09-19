<?php

namespace Efati\ModuleGenerator\Support;

class SchemaParser
{
    /**
     * Parse a CLI schema string into an array of field definitions.
     *
     * Each definition supports the syntax:
     *   name:type[:modifier[:modifier ...]]
     * Modifiers can be provided using ":", "|" or spaces as separators.
     * Supported modifiers: nullable, unique, fk=table.column
     *
     * @return array<int, array{name: string, type: string, nullable: bool, unique: bool, foreign: array{table: string, column: string}|null}>
     */
    public static function parse(string $input): array
    {
        $definitions = self::splitDefinitions($input);
        $fields      = [];

        foreach ($definitions as $definition) {
            $field = self::parseField($definition);
            if ($field === null) {
                continue;
            }
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Extract only the field names from a schema array.
     *
     * @param  array<int, array{name: string}>  $schema
     * @return array<int, string>
     */
    public static function fieldNames(array $schema): array
    {
        $names = [];
        foreach ($schema as $field) {
            if (!isset($field['name'])) {
                continue;
            }
            $names[] = (string) $field['name'];
        }

        return $names;
    }

    /**
     * Key the schema array by field name for faster lookups.
     *
     * @param  array<int, array{name: string}>  $schema
     * @return array<string, array{name: string, type: string, nullable: bool, unique: bool, foreign: array{table: string, column: string}|null}>
     */
    public static function keyByName(array $schema): array
    {
        $assoc = [];
        foreach ($schema as $field) {
            if (!isset($field['name'])) {
                continue;
            }
            $assoc[(string) $field['name']] = $field;
        }

        return $assoc;
    }

    /**
     * Convert a raw type string into a canonical type.
     */
    public static function normalizeType(string $type): string
    {
        $type = trim($type);
        if ($type === '') {
            return 'string';
        }

        $type = strtolower($type);
        $type = preg_replace('/\s+/', '', $type) ?? $type;
        $type = preg_replace('/\(.*$/', '', $type) ?? $type;

        return match ($type) {
            'char', 'varchar', 'string' => 'string',
            'text', 'mediumtext', 'longtext' => 'text',
            'int', 'integer', 'bigint', 'biginteger', 'mediumint', 'smallint', 'tinyint',
            'unsignedint', 'unsignedinteger', 'unsignedbigint', 'unsignedbiginteger',
            'unsignedmediumint', 'unsignedsmallint', 'unsignedtinyint',
            'increments', 'bigincrements', 'foreignid', 'foreignkey' => 'integer',
            'decimal', 'double', 'float', 'numeric' => 'numeric',
            'bool', 'boolean' => 'boolean',
            'date' => 'date',
            'datetime', 'datetimetz', 'timestamp', 'timestamptz' => 'datetime',
            'json', 'jsonb' => 'json',
            'array' => 'array',
            'uuid' => 'uuid',
            'email' => 'email',
            'url' => 'url',
            default => $type,
        };
    }

    /**
     * Split the CLI string into individual field definitions while respecting parentheses.
     *
     * @return array<int, string>
     */
    private static function splitDefinitions(string $input): array
    {
        $input = trim($input);
        if ($input === '') {
            return [];
        }

        $items  = [];
        $buffer = '';
        $depth  = 0;
        $len    = strlen($input);

        for ($i = 0; $i < $len; $i++) {
            $char = $input[$i];

            if ($char === '(') {
                $depth++;
                $buffer .= $char;
                continue;
            }

            if ($char === ')') {
                if ($depth > 0) {
                    $depth--;
                }
                $buffer .= $char;
                continue;
            }

            if ($char === ',' && $depth === 0) {
                $trimmed = trim($buffer);
                if ($trimmed !== '') {
                    $items[] = $trimmed;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $trimmed = trim($buffer);
        if ($trimmed !== '') {
            $items[] = $trimmed;
        }

        return $items;
    }

    /**
     * Parse an individual field definition.
     *
     * @return array{name: string, type: string, nullable: bool, unique: bool, foreign: array{table: string, column: string}|null}|null
     */
    private static function parseField(string $definition): ?array
    {
        $definition = trim($definition);
        if ($definition === '') {
            return null;
        }

        $name = $definition;
        $rest = '';

        $colonPos = strpos($definition, ':');
        if ($colonPos !== false) {
            $name = substr($definition, 0, $colonPos);
            $rest = substr($definition, $colonPos + 1);
        }

        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        [$type, $modifiers] = self::splitTypeAndModifiers($rest);

        $nullable = false;
        $unique   = false;
        $foreign  = null;

        foreach ($modifiers as $modifier) {
            $lower = strtolower($modifier);

            if ($lower === '') {
                continue;
            }

            if (in_array($lower, ['nullable', 'null', 'optional'], true)) {
                $nullable = true;
                continue;
            }

            if (in_array($lower, ['required', 'notnull', 'not-null'], true)) {
                $nullable = false;
                continue;
            }

            if (in_array($lower, ['unique', 'uniq'], true)) {
                $unique = true;
                continue;
            }

            if (preg_match('/^(?:fk|foreign|references)\s*(?:=|\()\s*(.+?)\s*\)?$/i', $modifier, $matches)) {
                $foreign = self::parseForeign($matches[1]);
                continue;
            }
        }

        return [
            'name'     => $name,
            'type'     => self::normalizeType($type),
            'nullable' => $nullable,
            'unique'   => $unique,
            'foreign'  => $foreign,
        ];
    }

    /**
     * Split the "type[:modifiers]" segment into a type and modifier tokens.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    private static function splitTypeAndModifiers(string $segment): array
    {
        $segment = trim($segment);
        if ($segment === '') {
            return ['string', []];
        }

        $len  = strlen($segment);
        $type = '';
        $i    = 0;

        while ($i < $len) {
            $char = $segment[$i];
            if (str_contains(':|, ?!\t', $char)) {
                break;
            }
            $type .= $char;
            $i++;
        }

        if ($type === '') {
            $type = 'string';
        }

        $remaining = substr($segment, $i);
        $remaining = ltrim($remaining, " :|,?!\t");

        if ($remaining === '' || $remaining === false) {
            return [$type, []];
        }

        $tokens = preg_split('/[:|,\s]+/', $remaining) ?: [];
        $tokens = array_filter(array_map('trim', $tokens), fn($token) => $token !== '');

        return [$type, array_values($tokens)];
    }

    /**
     * Parse the FK value into table/column.
     *
     * @return array{table: string, column: string}|null
     */
    private static function parseForeign(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value, " \t\n\r\0\x0B(){}");
        if ($value === '') {
            return null;
        }

        $value = str_replace(':', '.', $value);
        $parts = array_values(array_filter(explode('.', $value), fn($part) => $part !== ''));
        if (empty($parts)) {
            return null;
        }

        $table  = $parts[0];
        $column = $parts[1] ?? 'id';

        return [
            'table'  => $table,
            'column' => $column,
        ];
    }
}
