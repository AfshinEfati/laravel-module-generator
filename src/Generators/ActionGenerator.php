<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;

class ActionGenerator
{
    /**
     * @return array<string, bool>
     */
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        bool $usesDto = true,
        bool $force = false
    ): array {
        $paths = config('module-generator.paths', []);
        $actionsRel = is_array($paths['actions'] ?? null)
            ? ($paths['actions']['root'] ?? 'Actions')
            : ($paths['actions'] ?? 'Actions');

        $basePath = app_path(trim($actionsRel, '/\\'));
        $modulePath = $basePath . '/' . $name;

        File::ensureDirectoryExists($basePath);
        File::ensureDirectoryExists($modulePath);

        $baseNamespaceActions = $baseNamespace . '\\' . str_replace('/', '\\', trim($actionsRel, '/\\'));
        $moduleNamespace = $baseNamespaceActions . '\\' . $name;

        $serviceFqcn = $baseNamespace . '\\Services\\' . $name . 'Service';
        $modelFqcn   = $baseNamespace . '\\Models\\' . $name;
        $dtoFqcn     = $baseNamespace . '\\DTOs\\' . $name . 'DTO';

        $results = [];

        $baseActionPath = $basePath . '/BaseAction.php';
        $results[$baseActionPath] = self::writeFile(
            $baseActionPath,
            Stub::render('Action/base', [
                'namespace' => $baseNamespaceActions,
            ]),
            $force
        );

        $actions = [
            'List' => Stub::render('Action/list', [
                'namespace'      => $moduleNamespace,
                'base_namespace' => $baseNamespaceActions,
                'service_fqcn'   => $serviceFqcn,
                'service_class'  => $name . 'Service',
                'model_fqcn'     => $modelFqcn,
                'model_class'    => $name,
            ]),

            'Show' => Stub::render('Action/show', [
                'namespace'      => $moduleNamespace,
                'base_namespace' => $baseNamespaceActions,
                'service_fqcn'   => $serviceFqcn,
                'service_class'  => $name . 'Service',
                'model_fqcn'     => $modelFqcn,
                'model_class'    => $name,
            ]),

            'Create' => Stub::render('Action/create', [
                'namespace'      => $moduleNamespace,
                'base_namespace' => $baseNamespaceActions,
                'service_fqcn'   => $serviceFqcn,
                'service_class'  => $name . 'Service',
                'model_fqcn'     => $modelFqcn,
                'model_class'    => $name,
                'payload_doc'    => $usesDto ? $name . 'DTO|array' : 'array',
                'dto_import'     => $usesDto ? 'use ' . $dtoFqcn . ';' : '',
            ]),

            'Update' => Stub::render('Action/update', [
                'namespace'      => $moduleNamespace,
                'base_namespace' => $baseNamespaceActions,
                'service_fqcn'   => $serviceFqcn,
                'service_class'  => $name . 'Service',
                'model_fqcn'     => $modelFqcn,
                'model_class'    => $name,
                'payload_doc'    => $usesDto ? $name . 'DTO|array' : 'array',
                'dto_import'     => $usesDto ? 'use ' . $dtoFqcn . ';' : '',
            ]),

            'Delete' => Stub::render('Action/delete', [
                'namespace'      => $moduleNamespace,
                'base_namespace' => $baseNamespaceActions,
                'service_fqcn'   => $serviceFqcn,
                'service_class'  => $name . 'Service',
                'model_class'    => $name,
            ]),

            // *** اکشن جدید (ListWithRelations) ***
            'ListWithRelations' => Stub::render('Action/list_with_relations', [
                'namespace'      => $moduleNamespace,
                'base_namespace' => $baseNamespaceActions,
                'service_fqcn'   => $serviceFqcn,
                'service_class'  => $name . 'Service',
                'model_fqcn'     => $modelFqcn,
                'model_class'    => $name,
            ]),
        ];

        foreach ($actions as $action => $content) {
            $file = $modulePath . '/' . $action . $name . 'Action.php';
            $results[$file] = self::writeFile($file, $content, $force);
        }

        return $results;
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
