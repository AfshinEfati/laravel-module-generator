<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\BaseClassLocator;
use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;

class RepositoryGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', bool $force = false): array
    {
        try {
            $paths = config('module-generator.paths', []);

            // Support both 'repository' and 'repositories' keys + safe defaults
            $repoPaths     = $paths['repository']  ?? ($paths['repositories']  ?? []);
            $eloquentRel   = is_array($repoPaths) ? ($repoPaths['eloquent']  ?? 'Repositories/Eloquent')  : 'Repositories/Eloquent';
            $contractsRel  = is_array($repoPaths) ? ($repoPaths['contracts'] ?? 'Repositories/Contracts') : 'Repositories/Contracts';

            $eloquentPath = app_path($eloquentRel);
            $contractPath = app_path($contractsRel);
            File::ensureDirectoryExists($eloquentPath);
            File::ensureDirectoryExists($contractPath);

            $modelFqcn = $baseNamespace . '\\Models\\' . $name;

            $results = [];

            $contractNamespace = $baseNamespace . '\\Repositories\\Contracts';
            $contractClass      = $name . 'RepositoryInterface';
            $contractFile       = $contractPath . "/{$contractClass}.php";

            $baseRepositoryInterface = BaseClassLocator::baseRepositoryInterface($baseNamespace);

            $contractUses = [
                $modelFqcn,
                $baseRepositoryInterface['fqcn'],
            ];

            $contract = Stub::render('Repository/contract', [
                'namespace' => $contractNamespace,
                'uses'      => self::buildUses($contractUses),
                'interface' => $contractClass,
                'model'     => $name,
                'base_interface' => $baseRepositoryInterface['class'],
            ]);

            $results[$contractFile] = self::writeFile($contractFile, $contract, $force);

            $eloquentNamespace = $baseNamespace . '\\Repositories\\Eloquent';
            $eloquentClass     = $name . 'Repository';
            $eloquentFile      = $eloquentPath . "/{$eloquentClass}.php";

            $baseRepository = BaseClassLocator::baseRepository($baseNamespace);

            $concreteUses = [
                $contractNamespace . '\\' . $contractClass,
                $baseRepository['fqcn'],
                $modelFqcn,
            ];

            $concrete = Stub::render('Repository/concrete', [
                'namespace' => $eloquentNamespace,
                'uses'      => self::buildUses($concreteUses),
                'class'     => $eloquentClass,
                'interface' => $contractClass,
                'model'     => $name,
                'base_class' => $baseRepository['class'],
            ]);

            $results[$eloquentFile] = self::writeFile($eloquentFile, $concrete, $force);

            return $results;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function buildUses(array $uses): string
    {
        $uses = array_values(array_unique(array_filter($uses)));

        return $uses ? 'use ' . implode(";\nuse ", $uses) . ';' : '';
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
