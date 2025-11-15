<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\MigrationFieldParser;
use Efati\ModuleGenerator\Support\ModelInspector;
use Efati\ModuleGenerator\Support\Stub;

use Illuminate\Support\Facades\File;

class DTOGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App', bool $force = false, ?array $fields = null): array
    {
        try {
            $paths = config('module-generator.paths', []);
            $dtoRel = $paths['dto'] ?? ($paths['dtos'] ?? 'DTOs');

            $dtoPath = app_path($dtoRel);
            File::ensureDirectoryExists($dtoPath);

            $className = "{$name}DTO";
            $filePath  = $dtoPath . "/{$className}.php";

            $modelFqcn = "{$baseNamespace}\\Models\\{$name}";
            $fillable  = self::resolveFillable($modelFqcn, $fields);

            if (empty($fillable)) {
                // Return empty DTO if no fields found
                $fillable = [];
            }

            $content   = self::build($className, $baseNamespace, $fillable);

            return [$filePath => self::writeFile($filePath, $content, $force)];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function resolveFillable(string $modelFqcn, ?array $fields): array
    {
        if (is_array($fields) && !empty($fields)) {
            return MigrationFieldParser::buildFillableFromFields($fields);
        }

        return self::getFillable($modelFqcn);
    }

    private static function getFillable(string $modelFqcn): array
    {
        return ModelInspector::extractFillable($modelFqcn);
    }

    private static function build(string $className, string $baseNamespace, array $fillable): string
    {
        $ns = "{$baseNamespace}\\DTOs";

        $properties = [];
        $constructorSignature = [];
        $constructorBody = [];

        foreach ($fillable as $f) {
            $properties[] = "    public mixed \${$f};";
            $constructorSignature[] = "        mixed \${$f} = null";
            $constructorBody[] = "        \$this->{$f} = \${$f};";
        }

        $fromRequestBody = [];
        foreach ($fillable as $f) {
            $fromRequestBody[] = "            \$dto->{$f} = \$request->input('{$f}');";
        }

        $toArrayBody = [];
        foreach ($fillable as $f) {
            $toArrayBody[] = "        if (\$this->{$f} !== null) { \$out['{$f}'] = \$this->{$f}; }";
        }

        return Stub::render('DTO/dto', [
            'namespace'             => $ns,
            'class'                 => $className,
            'properties'            => empty($properties) ? '' : implode("\n", $properties) . "\n",
            'constructor_signature' => implode(",\n", $constructorSignature),
            'constructor_body'      => implode("\n", $constructorBody),
            'from_request_body'     => implode("\n", $fromRequestBody),
            'to_array_body'         => implode("\n", $toArrayBody),
        ]);
    }

    private static function writeFile(string $path, string $content, bool $force): bool
    {
        if (!$force && File::exists($path)) {
            return false;
        }

        File::put($path, $content);

        return true;
    }
}
