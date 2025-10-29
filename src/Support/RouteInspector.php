<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Str;

class RouteInspector
{
    /**
     * Attempt to discover resource-style route URIs for the given controller basename.
     *
     * @param  array<int, string>  $slugHints
     * @return array<string, string>
     */
    public static function discoverResourceUris(string $controllerBasename, array $slugHints = []): array
    {
        if (!function_exists('app')) {
            return [];
        }

        try {
            if (!app()->bound('router')) {
                return [];
            }
            $routes = app('router')->getRoutes();
        } catch (\Throwable $e) {
            return [];
        }

        $result = [];

        foreach ($routes as $route) {
            $action = $route->getAction();
            $controller = $action['controller'] ?? ($action['uses'] ?? null);
            $methodName = method_exists($route, 'getActionMethod') ? strtolower((string) $route->getActionMethod()) : null;

            if (!is_string($controller) || !str_contains($controller, '@') || $methodName === null) {
                continue;
            }

            [$class] = explode('@', $controller, 2);

            if (class_basename($class) === $controllerBasename) {
                if (!isset($result[$methodName])) {
                    $result[$methodName] = self::normalizeUri($route->uri());
                }
                continue;
            }

            if (empty($slugHints)) {
                continue;
            }

            $uri = $route->uri();

            foreach ($slugHints as $hint) {
                if ($hint !== '' && self::uriContainsHint($uri, $hint)) {
                    if (!isset($result[$methodName])) {
                        $result[$methodName] = self::normalizeUri($uri);
                    }
                    break;
                }
            }
        }

        return $result;
    }

    public static function extractParamName(?string $uri): ?string
    {
        if (!is_string($uri) || $uri === '') {
            return null;
        }

        if (preg_match('/\{([^}]+)\}/', $uri, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function normalizeUri(?string $uri): string
    {
        if (!is_string($uri) || $uri === '') {
            return '/';
        }

        return '/' . ltrim($uri, '/');
    }

    private static function uriContainsHint(string $uri, string $hint): bool
    {
        $normalizedHint = Str::of($hint)->trim('/')->lower();
        $normalizedUri  = Str::of($uri)->trim('/')->lower();

        if ($normalizedHint === '') {
            return false;
        }

        if ($normalizedUri === $normalizedHint) {
            return true;
        }

        return Str::of($normalizedUri)->contains($normalizedHint);
    }
}
