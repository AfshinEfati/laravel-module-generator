<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PolicyGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', bool $force = false): array
    {
        $paths = config('module-generator.paths', []);
        $policyRel = $paths['policy'] ?? ($paths['policies'] ?? 'Policies');

        $policyPath = app_path($policyRel);
        File::ensureDirectoryExists($policyPath);

        $className = "{$name}Policy";
        $filePath  = $policyPath . "/{$className}.php";

        $modelFqcn = "{$baseNamespace}\\Models\\{$name}";
        $userFqcn  = "{$baseNamespace}\\Models\\User";

        // Fallback if User model is in App\User (Laravel < 8)
        if (!class_exists($userFqcn) && class_exists("{$baseNamespace}\\User")) {
            $userFqcn = "{$baseNamespace}\\User";
        }

        $content = Stub::render('Module/Policy/policy', [
            'namespace'      => "{$baseNamespace}\\{$policyRel}",
            'class'          => $className,
            'model_fqcn'     => $modelFqcn,
            'user_fqcn'      => $userFqcn,
            'user_class'     => class_basename($userFqcn),
            'model_class'    => $name,
            'model_variable' => lcfirst($name),
        ]);

        return [$filePath => self::writeFile($filePath, $content, $force)];
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
