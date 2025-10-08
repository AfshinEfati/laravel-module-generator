<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\MigrationFieldParser;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Support\SchemaParser;

class ResourceGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        bool $force = false,
        ?array $fields = null,
        array $migrationRelations = []
    ): array {

        $paths = config('module-generator.paths', []);
        $resourceRel = $paths['resource'] ?? ($paths['resources'] ?? 'Http/Resources');

        $resourcePath = app_path($resourceRel);
        File::ensureDirectoryExists($resourcePath);

        $className = "{$name}Resource";
        $filePath  = $resourcePath . "/{$className}.php";

        $modelFqcn  = "{$baseNamespace}\\Models\\{$name}";
        $helperFqcn = "{$baseNamespace}\\Helpers\\ApiResponseHelper";

        $fillable  = self::resolveFillable($modelFqcn, $fields);
        $casts     = self::resolveCasts($modelFqcn, $fields);
        $relations = self::resolveRelations($modelFqcn, $baseNamespace, $migrationRelations);


        $content = self::build($className, $baseNamespace, $helperFqcn, $fillable, $relations, $casts);

        return [$filePath => self::writeFile($filePath, $content, $force)];
    }

    private static function resolveFillable(string $modelFqcn, ?array $fields): array
    {
        if (is_array($fields) && !empty($fields)) {
            return MigrationFieldParser::buildFillableFromFields($fields);
        }

        return self::getFillable($modelFqcn);
    }

    private static function resolveCasts(string $modelFqcn, ?array $fields): array
    {
        if (is_array($fields) && !empty($fields)) {
            return MigrationFieldParser::buildCastsFromFields($fields);
        }

        return self::getModelCasts($modelFqcn);
    }

    private static function getFillable(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) {
            return [];
        }
        $m = new $modelFqcn();
        return method_exists($m, 'getFillable') ? $m->getFillable() : [];
    }

    private static function getModelCasts(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) {
            return [];
        }
        $m = new $modelFqcn();
        return method_exists($m, 'getCasts') ? $m->getCasts() : [];
    }

    private static function detectRelations(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) {
            return [];
        }
        $m = new $modelFqcn();
        $rels = [];

        foreach (get_class_methods($m) as $method) {
            if (in_array($method, ['boot', 'booted'])) {
                continue;
            }
            try {
                $ret = $m->$method();
                if (is_object($ret) && method_exists($ret, 'getRelated')) {
                    $rels[$method] = get_class($ret->getRelated());
                }
            } catch (\Throwable $e) {
                // ignore relation that throws
            }
        }

        return $rels;
    }

    private static function resolveRelations(string $modelFqcn, string $baseNamespace, array $migrationRelations): array
    {
        $relations = [];

        foreach ($migrationRelations as $key => $info) {
            if (!is_array($info)) {
                continue;
            }
            $name = $info['name'] ?? (is_string($key) ? $key : null);
            if (!$name) {
                continue;
            }
            $base = $info['related_model'] ?? Str::studly($name);
            $relations[$name] = [
                'model'    => $baseNamespace . '\\Models\\' . $base,
                'resource' => $baseNamespace . '\\Http\\Resources\\' . $base . 'Resource',
            ];
        }

        foreach (self::detectRelations($modelFqcn) as $rel => $relatedFqcn) {
            $base = class_exists($relatedFqcn) ? class_basename($relatedFqcn) : Str::studly($rel);
            $relations[$rel] = [
                'model'    => $relatedFqcn,
                'resource' => $baseNamespace . '\\Http\\Resources\\' . $base . 'Resource',
            ];
        }

        return $relations;
    }

    private static function build(
        string $className,
        string $baseNamespace,
        string $helperFqcn,
        array $fillable,
        array $relations,
        array $casts
    ): string {
        $ns = "{$baseNamespace}\\Http\\Resources";
        $uses = [
            'Illuminate\\Http\\Resources\\Json\\JsonResource',
            $helperFqcn,
        ];

        $usesBlock = self::buildUses($uses);

        $body = [];
        foreach ($fillable as $field) {
            $castType = self::normalizeCast($casts[$field] ?? null);
            if ($castType === 'datetime' || $castType === 'date' || Str::endsWith($field, ['_at'])) {
                $body[] = "            '{$field}' => ApiResponseHelper::formatDates(\$this->{$field}),";
            } elseif ($castType === 'boolean' || $castType === 'bool' || Str::startsWith($field, ['is_', 'has_'])) {
                $body[] = "            '{$field}' => ApiResponseHelper::getStatus((bool) \$this->{$field}),";
            } else {
                $body[] = "            '{$field}' => \$this->{$field},";
            }
        }

        foreach ($relations as $rel => $meta) {
            $resourceFqcn = $meta['resource'];
            $body[] =
"            '{$rel}' => class_exists('{$resourceFqcn}')
                ? new \\{$resourceFqcn}(\$this->whenLoaded('{$rel}'))
                : \$this->whenLoaded('{$rel}'),";
        }

        $bodyBlock = implode("\n", $body);

        return "<?php\n\nnamespace {$ns};\n\n{$usesBlock}\n\nclass {$className} extends JsonResource\n{\n    public function toArray(\$request): array\n    {\n        return [\n{$bodyBlock}\n        ];\n    }\n}\n";

    }

    private static function buildUses(array $uses): string
    {
        $uses = array_values(array_unique(array_filter($uses)));

        if (empty($uses)) {
            return '';
        }

        return 'use ' . implode(";\nuse ", $uses) . ';';
    }

    private static function writeFile(string $path, string $contents, bool $force): bool
    {
        if (!$force && File::exists($path)) {
            return false;
        }

        File::put($path, $contents);

        return true;
    }

    private static function normalizeCast(?string $cast): ?string
    {
        if ($cast === null) {
            return null;
        }

        $cast = strtolower($cast);
        if (str_contains($cast, ':')) {
            $cast = strstr($cast, ':', true);
        }

        return match ($cast) {
            'datetime', 'immutable_datetime', 'custom_datetime' => 'datetime',
            'date', 'immutable_date' => 'date',
            'bool', 'boolean' => 'boolean',
            default => $cast,
        };
    }
}
