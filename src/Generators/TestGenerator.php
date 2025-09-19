<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', ?string $controllerSubfolder = null, bool $force = false): array
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

        $fillable       = self::getFillable($modelFqcn);
        $fillableExport = self::exportArray($fillable);

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

    private static function getFillable(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) return [];
        $m = new $modelFqcn();
        return method_exists($m, 'getFillable') ? $m->getFillable() : [];
    }

    private static function exportArray(array $arr): string
    {
        $items = array_map(fn($v) => var_export($v, true), $arr);
        return '[' . implode(', ', $items) . ']';
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
