<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\MigrationFieldParser;
use Efati\ModuleGenerator\Support\ModelInspector;
use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        ?string $controllerSubfolder = null,
        bool $force = false,
        ?array $fields = null
    ): array {
        $testsPath = base_path(config('module-generator.tests.feature', 'tests/Feature'));
        File::ensureDirectoryExists($testsPath);

        $className = $name . 'CrudTest';
        $filePath  = $testsPath . '/' . $className . '.php';

        $modelFqcn = $baseNamespace . '\\Models\\' . $name;

        $paths          = config('module-generator.paths', []);
        $controllerRel  = $paths['controller'] ?? ($paths['controllers'] ?? 'Http/Controllers/Api/V1');
        $controllerNs   = self::controllerNamespaceFromRel($baseNamespace, $controllerRel, $controllerSubfolder);
        $controllerFqcn = $controllerNs . '\\' . $name . 'Controller';

        $resourceSegment  = Str::kebab(Str::pluralStudly($name));
        $testRouteSegment = 'test-' . $resourceSegment;
        $baseUri          = '/' . $testRouteSegment;

        $fieldMetadata  = self::resolveFieldMetadata($modelFqcn, $fields, $baseNamespace);
        $fillable       = array_keys($fieldMetadata);
        $fillableExport = self::exportArray($fillable);
        $metadataExport = self::exportAssoc($fieldMetadata, 2);

        $content = Stub::render('Test/feature', [
            'class'                 => $className,
            'base_uri'              => $baseUri,
            'test_route_segment'    => $testRouteSegment,
            'controller_fqcn'       => $controllerFqcn,
            'fillable_export'       => $fillableExport,
            'field_metadata_export' => $metadataExport,
            'model_fqcn'            => $modelFqcn,
        ]);

        return [$filePath => self::writeFile($filePath, $content, $force)];
    }

    private static function controllerNamespaceFromRel(string $baseNamespace, string $controllerRel, ?string $subfolder): string
    {
        $rel = str_replace('/', '\\', trim($controllerRel, '/\\'));
        $ns  = $baseNamespace . '\\' . $rel;
        if ($subfolder) {
            $ns .= '\\' . str_replace(['/', '\\'], '\\', trim($subfolder, '/\\'));
        }

        return $ns;
    }

    private static function resolveFieldMetadata(string $modelFqcn, ?array $fields, string $baseNamespace): array
    {
        if (is_array($fields) && !empty($fields)) {
            if (array_is_list($fields)) {
                $indexed = [];
                foreach ($fields as $field) {
                    if (is_array($field) && isset($field['name'])) {
                        $indexed[$field['name']] = $field;
                    }
                }
                if (!empty($indexed)) {
                    $fields = $indexed;
                }
            }

            $metadata = MigrationFieldParser::normaliseFieldMetadata($fields, $baseNamespace);
        } else {
            $fillable = self::getFillable($modelFqcn);
            $casts    = [];

            if (class_exists($modelFqcn)) {
                $model = new $modelFqcn();
                $casts = method_exists($model, 'getCasts') ? $model->getCasts() : [];
            }

            $metadata = [];
            foreach ($fillable as $field) {
                $cast = $casts[$field] ?? null;

                $entry = [
                    'type'     => self::inferTypeFromName($field, $cast),
                    'cast'     => $cast,
                    'nullable' => true,
                    'unique'   => false,
                ];

                if (str_ends_with($field, '_id')) {
                    $related = Str::studly(str_replace(['-', '_'], ' ', substr($field, 0, -3)));
                    $related = str_replace(' ', '', $related);
                    $entry['foreign'] = [
                        'table'         => null,
                        'references'    => 'id',
                        'related_model' => $baseNamespace . '\\Models\\' . $related,
                    ];
                }

                $metadata[$field] = $entry;
            }
        }

        foreach ($metadata as $key => &$meta) {
            if (empty($meta['foreign'])) {
                unset($meta['foreign']);
            }
            if (empty($meta['enum'])) {
                unset($meta['enum']);
            }
            if (!array_key_exists('cast', $meta) || $meta['cast'] === null || $meta['cast'] === '') {
                unset($meta['cast']);
            }
            if (isset($meta['type'])) {
                $meta['type'] = (string) $meta['type'];
            }
        }
        unset($meta);

        return $metadata;
    }

    private static function inferTypeFromName(string $field, ?string $cast = null): string
    {
        $normalizedCast = $cast;
        if (is_string($normalizedCast) && str_contains($normalizedCast, ':')) {
            $normalizedCast = strtolower(strtok($normalizedCast, ':'));
        }
        $normalizedCast = is_string($normalizedCast) ? strtolower($normalizedCast) : null;

        return match (true) {
            $normalizedCast === 'bool',
            $normalizedCast === 'boolean',
            str_starts_with($field, 'is_'),
            str_starts_with($field, 'has_') => 'boolean',

            $normalizedCast === 'int',
            $normalizedCast === 'integer',
            str_ends_with($field, '_id') => 'integer',

            $normalizedCast === 'float',
            $normalizedCast === 'double',
            $normalizedCast === 'decimal',
            str_contains($field, 'price'),
            str_contains($field, 'amount'),
            str_contains($field, 'rate') => 'float',

            $normalizedCast === 'array',
            $normalizedCast === 'collection' => 'json',

            $normalizedCast === 'datetime' => 'datetime',
            $normalizedCast === 'date' => 'date',

            $normalizedCast === 'uuid' => 'uuid',
            default => 'string',
        };
    }

    private static function getFillable(string $modelFqcn): array
    {
        return ModelInspector::extractFillable($modelFqcn);
    }

    private static function exportArray(array $arr): string
    {
        if ($arr === []) {
            return '[]';
        }

        $items = array_map(static fn ($value) => var_export($value, true), $arr);

        return '[' . implode(', ', $items) . ']';
    }

    private static function exportAssoc(array $arr, int $indentLevel = 0): string
    {
        if ($arr === []) {
            return '[]';
        }

        $indent     = str_repeat('    ', $indentLevel);
        $nextIndent = str_repeat('    ', $indentLevel + 1);
        $lines      = ['['];

        foreach ($arr as $key => $value) {
            $keyStr = is_int($key) ? '' : "'" . addslashes((string) $key) . "' => ";

            if (is_array($value)) {
                $nested       = self::exportAssoc($value, $indentLevel + 1);
                $nestedLines  = explode("\n", $nested);
                $nestedLines[0] = $nextIndent . $keyStr . ltrim($nestedLines[0]);
                for ($i = 1, $count = count($nestedLines); $i < $count; $i++) {
                    $nestedLines[$i] = $nextIndent . $nestedLines[$i];
                }
                $nestedLines[count($nestedLines) - 1] .= ',';
                $lines = array_merge($lines, $nestedLines);
                continue;
            }

            $lines[] = $nextIndent . $keyStr . self::exportValue($value) . ',';
        }

        $lines[] = $indent . ']';

        return implode("\n", $lines);
    }

    private static function exportValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return var_export($value, true);
    }

    private static function writeFile(string $path, string $contents, bool $force): bool
    {
        if (!$force && File::exists($path)) {
            return false;
        }

        File::put($path, $contents);

        return true;
    }
}
