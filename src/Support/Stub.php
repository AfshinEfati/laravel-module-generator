<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Facades\File;

class Stub
{
    public static function render(string $stub, array $replacements = []): string
    {
        $path = self::resolvePath($stub);

        $contents = File::get($path);

        foreach ($replacements as $key => $value) {
            $contents = str_replace('{{ ' . $key . ' }}', $value, $contents);
        }

        return $contents;
    }

    public static function path(string $stub): string
    {
        return self::resolvePath($stub);
    }

    protected static function resolvePath(string $stub): string
    {
        $normalized = str_replace(['\\', '//'], '/', $stub);

        $resourcePath = function_exists('resource_path')
            ? resource_path('stubs/module-generator/' . $normalized . '.stub')
            : app()->resourcePath('stubs/module-generator/' . $normalized . '.stub');

        $published = $resourcePath;
        if (File::exists($published)) {
            return $published;
        }

        $package = __DIR__ . '/../Stubs/Module/' . $normalized . '.stub';
        if (File::exists($package)) {
            return $package;
        }

        throw new \RuntimeException("Stub file [{$stub}] not found.");
    }
}
