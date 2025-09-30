<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Facades\File;

class BaseClassLocator
{
    /**
     * @return array{fqcn:string, namespace:string, class:string}
     */
    public static function baseRepository(string $baseNamespace = 'App'): array
    {
        $paths = config('module-generator.paths', []);
        $repoPaths = $paths['repository'] ?? ($paths['repositories'] ?? []);
        $eloquentRel = is_array($repoPaths)
            ? ($repoPaths['eloquent'] ?? 'Repositories/Eloquent')
            : 'Repositories/Eloquent';

        $path = app_path(self::normalizePath($eloquentRel) . '/BaseRepository.php');

        return self::resolve($path, self::qualifyNamespace($baseNamespace, $eloquentRel), 'BaseRepository');
    }

    /**
     * @return array{fqcn:string, namespace:string, class:string}
     */
    public static function baseRepositoryInterface(string $baseNamespace = 'App'): array
    {
        $paths = config('module-generator.paths', []);
        $repoPaths = $paths['repository'] ?? ($paths['repositories'] ?? []);
        $contractsRel = is_array($repoPaths)
            ? ($repoPaths['contracts'] ?? 'Repositories/Contracts')
            : 'Repositories/Contracts';

        $path = app_path(self::normalizePath($contractsRel) . '/BaseRepositoryInterface.php');

        return self::resolve($path, self::qualifyNamespace($baseNamespace, $contractsRel), 'BaseRepositoryInterface');
    }

    /**
     * @return array{fqcn:string, namespace:string, class:string}
     */
    public static function baseService(string $baseNamespace = 'App'): array
    {
        $paths = config('module-generator.paths', []);
        $servicePaths = $paths['service'] ?? ($paths['services'] ?? []);
        $serviceRel = is_array($servicePaths)
            ? ($servicePaths['concretes'] ?? 'Services')
            : 'Services';

        $path = app_path(self::normalizePath($serviceRel) . '/BaseService.php');

        return self::resolve($path, self::qualifyNamespace($baseNamespace, $serviceRel), 'BaseService');
    }

    /**
     * @return array{fqcn:string, namespace:string, class:string}
     */
    public static function baseServiceInterface(string $baseNamespace = 'App'): array
    {
        $paths = config('module-generator.paths', []);
        $servicePaths = $paths['service'] ?? ($paths['services'] ?? []);
        $contractsRel = is_array($servicePaths)
            ? ($servicePaths['contracts'] ?? 'Services/Contracts')
            : 'Services/Contracts';

        $path = app_path(self::normalizePath($contractsRel) . '/BaseServiceInterface.php');

        return self::resolve($path, self::qualifyNamespace($baseNamespace, $contractsRel), 'BaseServiceInterface');
    }

    private static function resolve(string $path, string $defaultNamespace, string $defaultClass): array
    {
        $info = self::extractClassInfo($path);

        $namespace = $info['namespace'] ?? $defaultNamespace;
        $class = $info['class'] ?? $defaultClass;

        return [
            'fqcn' => trim($namespace . '\\' . $class, '\\'),
            'namespace' => trim($namespace, '\\'),
            'class' => $class,
        ];
    }

    /**
     * @return array{namespace?:string, class?:string}
     */
    private static function extractClassInfo(string $path): array
    {
        if (!File::exists($path)) {
            return [];
        }

        $contents = File::get($path);
        $tokens = token_get_all($contents);

        $namespace = null;
        $class = null;
        $collectNamespace = false;
        $namespaceBuffer = '';
        $collectClass = false;

        $namespaceTokenIds = [T_STRING, T_NS_SEPARATOR];
        if (defined('T_NAME_QUALIFIED')) {
            $namespaceTokenIds[] = T_NAME_QUALIFIED;
        }
        if (defined('T_NAME_FULLY_QUALIFIED')) {
            $namespaceTokenIds[] = T_NAME_FULLY_QUALIFIED;
        }

        foreach ($tokens as $token) {
            if (is_array($token)) {
                [$id, $text] = $token;

                if ($id === T_NAMESPACE) {
                    $collectNamespace = true;
                    $namespaceBuffer = '';
                    $namespace = null;
                    continue;
                }

                if ($collectNamespace && in_array($id, $namespaceTokenIds, true)) {
                    $namespaceBuffer .= $id === T_NS_SEPARATOR ? '\\' : $text;
                    continue;
                }

                if ($id === T_CLASS || $id === T_INTERFACE) {
                    $collectClass = true;
                    continue;
                }

                if ($collectClass && $id === T_STRING) {
                    $class = $text;
                    break;
                }
            } else {
                if ($collectNamespace && ($token === ';' || $token === '{')) {
                    $namespace = trim($namespaceBuffer, '\\');
                    $collectNamespace = false;
                }

                if ($collectClass && !in_array($token, [' ', '\t', '\n', '\r'], true)) {
                    $collectClass = false;
                }
            }
        }

        $result = [];
        if ($namespace) {
            $result['namespace'] = trim($namespace, '\\');
        }
        if ($class) {
            $result['class'] = $class;
        }

        return $result;
    }

    private static function qualifyNamespace(string $baseNamespace, string $relative): string
    {
        $relative = trim(str_replace(['/', '\\'], '\\', $relative), '\\');

        return trim($baseNamespace . '\\' . $relative, '\\');
    }

    private static function normalizePath(string $path): string
    {
        return trim(str_replace(['\\', '//'], '/', $path), '/');
    }
}
