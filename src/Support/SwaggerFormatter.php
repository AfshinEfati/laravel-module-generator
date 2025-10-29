<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Str;

class SwaggerFormatter
{
    /**
     * Build an @OA\Schema method block for the given fields.
     */
    public static function buildSchemaBlock(string $schemaName, array $fields): string
    {
        $normalized = self::normalizeFields($fields);

        if (empty($normalized)) {
            return '';
        }

        $methodName = Str::camel($schemaName) . 'Schema';
        $schemaId   = Str::studly($schemaName) . 'Resource';
        $required   = self::requiredFieldNames($normalized);
        $properties = self::buildPropertyLines($normalized, '     *     ');
        $example    = self::formatJsonExample(self::exampleData($normalized));

        $lines   = [];
        $lines[] = '    /**';
        $lines[] = '     * @OA\Schema(';
        $lines[] = '     *     schema="' . addslashes($schemaId) . '",';
        $lines[] = '     *     type="object",';

        if (!empty($required)) {
            $lines[] = '     *     required={' . self::quoteList($required) . '},';
        }

        foreach ($properties as $propertyLine) {
            $lines[] = $propertyLine . ',';
        }

        $lines[] = '     *     example=' . $example;
        $lines[] = '     * )';
        $lines[] = '     */';
        $lines[] = '    public function ' . $methodName . '(): void';
        $lines[] = '    {';
        $lines[] = '    }';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Render @OA\JsonContent for a request or response payload.
     *
     * Options:
     *  - collection: bool     Whether to wrap items in @OA\Items (array response).
     *  - required: array      Explicit required field list.
     *  - indent: int          Spaces to indent nested lines (default 8).
     *  - include_example: bool Whether to include example payloads (default true).
     */
    public static function buildJsonContent(array $fields, array $options = []): string
    {
        $normalized = self::normalizeFields($fields);

        if (empty($normalized)) {
            return '@OA\\JsonContent()';
        }

        $collection      = (bool) ($options['collection'] ?? false);
        $required        = $options['required'] ?? null;
        $includeExample  = array_key_exists('include_example', $options) ? (bool) $options['include_example'] : true;
        $indentSize      = is_int($options['indent'] ?? null) ? (int) $options['indent'] : 8;
        $indent          = str_repeat(' ', max(0, $indentSize));
        $innerIndent     = $indent . '    ';
        $innerInnerIndent = $innerIndent . '    ';

        if ($required === null) {
            $required = self::requiredFieldNames($normalized);
        }

        $lines   = [];
        $lines[] = '@OA\\JsonContent(';

        if ($collection) {
            $lines[] = $indent . 'type="array",';
            $lines[] = $indent . '@OA\\Items(';
            $lines[] = $innerIndent . 'type="object",';

            $propertyLines = self::buildPropertyLines($normalized, $innerIndent);
            $propertyCount = count($propertyLines);

            foreach ($propertyLines as $index => $propertyLine) {
                $suffix = $index === $propertyCount - 1 && !$includeExample ? '' : ',';
                $lines[] = $propertyLine . $suffix;
            }

            if ($includeExample) {
                $itemExample = self::formatJsonExample(self::exampleData($normalized));
                $lines[]     = $innerIndent . 'example=' . $itemExample;
            }

            $lines[] = $indent . ')';
        } else {
            $lines[] = $indent . 'type="object",';

            if (!empty($required)) {
                $lines[] = $indent . 'required={' . self::quoteList($required) . '},';
            }

            $propertyLines = self::buildPropertyLines($normalized, $indent);
            $propertyCount = count($propertyLines);

            foreach ($propertyLines as $index => $propertyLine) {
                $suffix = $index === $propertyCount - 1 && !$includeExample ? '' : ',';
                $lines[] = $propertyLine . $suffix;
            }

            if ($includeExample) {
                $lines[] = $indent . 'example=' . self::formatJsonExample(self::exampleData($normalized));
            }
        }

        $lines[] = '    )';

        return implode("\n", $lines);
    }

    /**
     * Filter fields suitable for request payloads.
     *
     * Removes auto-managed timestamps and identifiers by default.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function requestFields(array $fields): array
    {
        $normalized = self::normalizeFields($fields);

        return array_values(array_filter($normalized, static function (array $field): bool {
            $name = $field['name'];

            if (($field['auto_managed'] ?? false) === true) {
                return false;
            }

            if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Compute required field names for the provided metadata.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, string>
     */
    public static function requiredFieldNames(array $fields): array
    {
        $required = [];

        foreach ($fields as $field) {
            if (($field['nullable'] ?? true) === false) {
                $required[] = $field['name'];
            }
        }

        return $required;
    }

    /**
     * Normalize field metadata to ensure each entry has a name.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $field) {
            if (!is_array($field)) {
                continue;
            }

            $name = $field['name'] ?? (is_string($key) ? $key : null);

            if (!is_string($name) || $name === '') {
                continue;
            }

            $field['name'] = $name;
            $normalized[]  = $field;
        }

        return $normalized;
    }

    /**
     * Build @OA\Property lines with the given indentation.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, string>
     */
    private static function buildPropertyLines(array $fields, string $indent): array
    {
        $lines = [];

        foreach ($fields as $field) {
            $attributes = [];
            $attributes[] = 'property="' . addslashes($field['name']) . '"';

            [$type, $format] = self::mapFieldType($field);
            $attributes[] = 'type="' . $type . '"';

            if ($format !== null) {
                $attributes[] = 'format="' . $format . '"';
            }

            if (!empty($field['enum']) && is_array($field['enum'])) {
                $attributes[] = 'enum={' . self::quoteList(array_map('strval', $field['enum'])) . '}';
            }

            if (($field['nullable'] ?? false) === true) {
                $attributes[] = 'nullable=true';
            }

            $example = self::formatScalarExample(self::exampleValue($field));
            $attributes[] = 'example=' . $example;

            // For array types, add @OA\Items
            if ($type === 'array') {
                $attributes[] = '@OA\\Items(type="object")';
            }

            $lines[] = $indent . '@OA\\Property(' . implode(', ', $attributes) . ')';
        }

        return $lines;
    }

    /**
     * Determine schema field type/format pair.
     *
     * @return array{0: string, 1: string|null}
     */
    private static function mapFieldType(array $field): array
    {
        $typeRaw = strtolower((string) ($field['type'] ?? 'string'));

        if (str_contains($typeRaw, 'int')) {
            return ['integer', null];
        }

        if (str_contains($typeRaw, 'bool')) {
            return ['boolean', null];
        }

        if (str_contains($typeRaw, 'decimal') || str_contains($typeRaw, 'float') || str_contains($typeRaw, 'double')) {
            return ['number', 'float'];
        }

        if (str_contains($typeRaw, 'json')) {
            return ['object', null];
        }

        if (str_contains($typeRaw, 'array')) {
            return ['array', null];
        }

        if (str_contains($typeRaw, 'date_time') || str_contains($typeRaw, 'datetime') || str_contains($typeRaw, 'timestamp')) {
            return ['string', 'date-time'];
        }

        if (str_contains($typeRaw, 'date')) {
            return ['string', 'date'];
        }

        if (str_contains($typeRaw, 'time')) {
            return ['string', 'time'];
        }

        if (str_contains($typeRaw, 'uuid')) {
            return ['string', 'uuid'];
        }

        return ['string', null];
    }

    /**
     * Generate example key-value pairs for the provided fields.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private static function exampleData(array $fields): array
    {
        $example = [];

        foreach ($fields as $field) {
            $example[$field['name']] = self::exampleValue($field);
        }

        return $example;
    }

    /**
     * Produce an example value for a given field definition.
     *
     * @return mixed
     */
    private static function exampleValue(array $field)
    {
        if (array_key_exists('default', $field) && $field['default'] !== null) {
            return $field['default'];
        }

        if (!empty($field['enum']) && is_array($field['enum'])) {
            return reset($field['enum']);
        }

        $name    = $field['name'];
        $typeRaw = strtolower((string) ($field['type'] ?? 'string'));

        if (str_contains($typeRaw, 'bool')) {
            return true;
        }

        if (str_contains($typeRaw, 'int')) {
            if ($name === 'id' || str_ends_with($name, '_id')) {
                return 1;
            }

            if (str_contains($name, 'count') || str_contains($name, 'quantity')) {
                return 3;
            }

            return 42;
        }

        if (str_contains($typeRaw, 'decimal') || str_contains($typeRaw, 'float') || str_contains($typeRaw, 'double')) {
            if (str_contains($name, 'price') || str_contains($name, 'amount') || str_contains($name, 'total')) {
                return 149.99;
            }

            return 99.99;
        }

        if (str_contains($typeRaw, 'date_time') || str_contains($typeRaw, 'datetime') || str_contains($typeRaw, 'timestamp')) {
            return '2024-01-01T10:00:00Z';
        }

        if (str_contains($typeRaw, 'date')) {
            return '2024-01-01';
        }

        if (str_contains($typeRaw, 'time')) {
            return '10:00:00';
        }

        if (str_contains($typeRaw, 'uuid') || str_contains($name, 'uuid')) {
            return '550e8400-e29b-41d4-a716-446655440000';
        }

        if (str_contains($name, 'email')) {
            return 'user@example.com';
        }

        if (str_contains($name, 'phone')) {
            return '+1-555-0100';
        }

        if (str_contains($name, 'status')) {
            return 'active';
        }

        if (str_contains($name, 'title')) {
            return 'Sample ' . Str::title(str_replace('_', ' ', $name));
        }

        if (str_contains($name, 'name')) {
            return 'Sample ' . Str::title(str_replace('_', ' ', $name));
        }

        if (str_contains($name, 'description') || str_contains($name, 'bio') || str_contains($name, 'notes')) {
            return 'Sample ' . Str::title(str_replace('_', ' ', $name)) . ' goes here.';
        }

        return Str::title(str_replace('_', ' ', $name));
    }

    /**
     * Quote a list of scalar values for annotations.
     *
     * @param  array<int, string>  $values
     */
    private static function quoteList(array $values): string
    {
        $quoted = array_map(static fn (string $value): string => '"' . addslashes($value) . '"', $values);

        return implode(',', $quoted);
    }

    /**
     * Format a scalar example value for annotation usage.
     */
    private static function formatScalarExample($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return self::formatJsonExample($value);
        }

        return '"' . addslashes((string) $value) . '"';
    }

    /**
     * Format nested examples (arrays/objects) as JSON without escaping slashes.
     *
     * @param  mixed  $data
     */
    private static function formatJsonExample($data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }
}
