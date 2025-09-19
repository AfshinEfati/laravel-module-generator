<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;

class RepositoryGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', bool $force = false): array
    {
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

        $contract = Stub::render('Repository/contract', [
            'namespace' => $contractNamespace,
            'interface' => $contractClass,
        ]);

        $results[$contractFile] = self::writeFile($contractFile, $contract, $force);

        $eloquentNamespace = $baseNamespace . '\\Repositories\\Eloquent';
        $eloquentClass     = $name . 'Repository';
        $eloquentFile      = $eloquentPath . "/{$eloquentClass}.php";

        $concrete = Stub::render('Repository/concrete', [
            'namespace'             => $eloquentNamespace,
            'interface_fqcn'        => $contractNamespace . '\\' . $contractClass,
            'base_repository_fqcn'  => $baseNamespace . '\\Repositories\\Eloquent\\BaseRepository',
            'model_fqcn'            => $modelFqcn,
            'class'                 => $eloquentClass,
            'interface'             => $contractClass,
            'model'                 => $name,
        ]);

        $results[$eloquentFile] = self::writeFile($eloquentFile, $concrete, $force);

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
