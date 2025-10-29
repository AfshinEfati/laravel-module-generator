<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SwaggerPathGuesser
{
    /**
     * @return array<int, string>
     */
    public static function slugHints(string $name): array
    {
        $pluralStudly = Str::pluralStudly($name);
        $candidates = [
            Str::lower($pluralStudly),
            Str::kebab($pluralStudly),
            Str::snake($pluralStudly),
            Str::plural(Str::lower($name)),
            Str::plural(Str::snake($name)),
        ];

        $filtered = array_filter(array_map(static fn ($value) => trim((string) $value, "/ \t\n\r\0\x0B"), $candidates));

        return array_values(array_unique($filtered));
    }

    public static function defaultResourceSegment(string $name): string
    {
        $hints = self::slugHints($name);

        return $hints[0] ?? Str::kebab(Str::pluralStudly($name));
    }

    /**
     * @return array<int, string>
     */
    public static function controllerSegments(?string $controllerSubfolder = null): array
    {
        return array_values(array_filter(array_merge(
            self::defaultControllerSegments(),
            self::normalizeSegments($controllerSubfolder)
        )));
    }

    /**
     * @param  array<int, string>  $segments
     */
    public static function assemblePath(array $segments, string $resourceSegment): string
    {
        $parts = array_values(array_filter(array_merge($segments, [$resourceSegment]), fn ($segment) => $segment !== ''));

        return '/' . implode('/', $parts);
    }

    /**
     * @return array<int, string>
     */
    private static function defaultControllerSegments(): array
    {
        $pathConfig = config('module-generator.paths.controller') ?? config('module-generator.paths.controllers') ?? 'Http/Controllers/Api/V1';

        if (is_array($pathConfig)) {
            $pathConfig = Arr::first($pathConfig) ?: 'Http/Controllers/Api/V1';
        }

        $trimmed = trim((string) $pathConfig, '/\\');
        $parts = preg_split('/[\/\\\\]+/', $trimmed) ?: [];

        $segments = [];
        $seenControllers = false;

        foreach ($parts as $part) {
            if (!$seenControllers) {
                if (Str::lower($part) === 'controllers') {
                    $seenControllers = true;
                }
                continue;
            }

            if ($part === '') {
                continue;
            }

            $segments[] = Str::kebab($part);
        }

        if (!$seenControllers) {
            // If "Controllers" segment was not found, treat entire path as relative API path.
            foreach ($parts as $part) {
                if ($part !== '') {
                    $segments[] = Str::kebab($part);
                }
            }
        }

        return array_values(array_unique(array_filter($segments)));
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeSegments(?string $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[\/\\\\]+/', trim($value, '/\\')) ?: [];

        return array_values(array_filter(array_map(static fn ($part) => Str::kebab($part), $parts)));
    }
}
