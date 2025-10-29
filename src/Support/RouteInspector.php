<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Str;

class RouteInspector
{
    /**
     * Attempt to discover resource-style route URIs for the given controller basename.
     *
     * @param  array<int, string>  $slugHints
     * @return array<string, array<string, mixed>>
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
                    $result[$methodName] = [
                        'uri'        => self::normalizeUri($route->uri()),
                        'middleware' => self::gatherNormalizedMiddleware($route),
                    ];
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
                        $result[$methodName] = [
                            'uri'        => self::normalizeUri($uri),
                            'middleware' => self::gatherNormalizedMiddleware($route),
                        ];
                    }
                    break;
                }
            }
        }

        return $result;
    }

    public static function extractParamName($entry): ?string
    {
        $uri = self::pathFromEntry($entry);

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

    public static function pathFromEntry($entry): ?string
    {
        if (is_array($entry)) {
            return isset($entry['uri']) ? (string) $entry['uri'] : null;
        }

        return is_string($entry) ? self::normalizeUri($entry) : null;
    }

    /**
     * @return array<int, string>
     */
    public static function middlewareFromEntry($entry): array
    {
        if (is_array($entry) && isset($entry['middleware']) && is_array($entry['middleware'])) {
            return array_values(array_unique(array_filter(array_map(static fn ($mw) => is_string($mw) ? trim(strtolower($mw)) : null, $entry['middleware']))));
        }

        return [];
    }

    private static function gatherNormalizedMiddleware($route): array
    {
        try {
            if (method_exists($route, 'gatherMiddleware')) {
                $middleware = (array) $route->gatherMiddleware();
            } else {
                $middleware = (array) $route->middleware();
            }
        } catch (\Throwable $e) {
            $middleware = [];
        }

        return self::normalizeMiddlewareArray($middleware);
    }

    /**
     * @param  array<int, mixed>  $middleware
     * @return array<int, string>
     */
    private static function normalizeMiddlewareArray(array $middleware): array
    {
        $normalized = [];

        foreach ($middleware as $mw) {
            if (!is_string($mw) || $mw === '') {
                continue;
            }

            $normalized[] = strtolower(trim($mw));
        }

        return array_values(array_unique($normalized));
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
