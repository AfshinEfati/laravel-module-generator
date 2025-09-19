<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Support\SchemaParser;

class TestGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        ?string $controllerSubfolder = null,
        bool $force = false,
        array $schema = []
    ): array
    {
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

        $fillable       = self::getFillable($modelFqcn, $schema);
        $fillableExport = self::exportArray($fillable);
        $schemaExport   = self::exportSchema($schema);

        $baseNsLiteral = var_export($baseNamespace, true);

        $content = Stub::render('Test/feature', [
            'class'                  => $className,
            'base_uri'               => $baseUri,
            'test_route_segment'     => $testRouteSegment,
            'controller_fqcn'        => $controllerFqcn,
            'fillable_export'        => $fillableExport,
            'base_namespace_literal' => $baseNsLiteral,
            'model_fqcn'             => $modelFqcn,
        ]);

        $content = <<<PHP
<?php

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

    private static function getFillable(string $modelFqcn, array $schema): array
    {
        if (!class_exists($modelFqcn)) {
            return SchemaParser::fieldNames($schema);
        }
        $m        = new $modelFqcn();
        $fillable = method_exists($m, 'getFillable') ? $m->getFillable() : [];

        if (empty($fillable)) {
            return SchemaParser::fieldNames($schema);
        }

        return $fillable;
    }

    private static function exportArray(array $arr): string
    {
        $items = array_map(fn($v) => var_export($v, true), $arr);
        return '[' . implode(', ', $items) . ']';
    }

    private static function exportSchema(array $schema): string
    {
        if (empty($schema)) {
            return '[]';
        }

        $assoc = [];
        foreach ($schema as $field) {
            if (!isset($field['name'])) {
                continue;
            }

            $foreign = null;
            if (!empty($field['foreign']) && is_array($field['foreign'])) {
                $table  = $field['foreign']['table'] ?? null;
                $column = $field['foreign']['column'] ?? 'id';
                if ($table) {
                    $foreign = ['table' => $table, 'column' => $column];
                }
            }

            $assoc[$field['name']] = [
                'type'     => SchemaParser::normalizeType((string) ($field['type'] ?? 'string')),
                'nullable' => (bool) ($field['nullable'] ?? false),
                'unique'   => (bool) ($field['unique'] ?? false),
                'foreign'  => $foreign,
            ];
        }

        if (empty($assoc)) {
            return '[]';
        }

        return self::exportValue($assoc, 2);
    }

    private static function exportValue(mixed $value, int $indent = 0): string
    {
        if (is_array($value)) {
            if ($value === []) {
                return '[]';
            }

            $indentStr     = str_repeat('    ', $indent);
            $nextIndentStr = str_repeat('    ', $indent + 1);
            $lines         = [];

            foreach ($value as $key => $val) {
                $keyPrefix = is_int($key) ? '' : var_export($key, true) . ' => ';
                $lines[]   = $nextIndentStr . $keyPrefix . self::exportValue($val, $indent + 1);
            }

            return "[\n" . implode(",\n", $lines) . "\n" . $indentStr . "]";
        }

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
