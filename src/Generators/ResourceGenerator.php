<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ResourceGenerator
{
    public static function generate(string $name, string $baseNamespace = 'App'): void
    {
        $paths = config('module-generator.paths', []);
        $resourceRel = $paths['resource'] ?? ($paths['resources'] ?? 'Http/Resources');

        $resourcePath = app_path($resourceRel);
        File::ensureDirectoryExists($resourcePath);

        $className = "{$name}Resource";
        $filePath  = $resourcePath . "/{$className}.php";

        $modelFqcn  = "{$baseNamespace}\\Models\\{$name}";
        $helperFqcn = "{$baseNamespace}\\Helpers\\StatusHelper";

        $fillable   = self::getFillable($modelFqcn);
        $relations  = self::detectRelations($modelFqcn);

        $content    = self::build($className, $baseNamespace, $helperFqcn, $fillable, $relations);

        File::put($filePath, $content);
    }

    private static function getFillable(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) return [];
        $m = new $modelFqcn();
        return method_exists($m, 'getFillable') ? $m->getFillable() : [];
    }

    private static function detectRelations(string $modelFqcn): array
    {
        if (!class_exists($modelFqcn)) return [];
        $m = new $modelFqcn();
        $rels = [];

        foreach (get_class_methods($m) as $method) {
            if (in_array($method, ['boot', 'booted'])) continue;
            try {
                $ret = $m->$method();
                if (is_object($ret) && method_exists($ret, 'getRelated')) {
                    $rels[$method] = get_class($ret->getRelated());
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return $rels;
    }

    private static function build(string $className, string $baseNamespace, string $helperFqcn, array $fillable, array $relations): string
    {
        $ns = "{$baseNamespace}\\Http\\Resources";
        $uses = [
            'Illuminate\\Http\\Resources\\Json\\JsonResource',
            $helperFqcn,
        ];
        $usesBlock = 'use ' . implode(";\nuse ", array_unique($uses)) . ';';

        $body = [];
        foreach ($fillable as $field) {
            if (Str::endsWith($field, ['_at'])) {
                $body[] = "            '{$field}' => StatusHelper::formatDates(\$this->{$field}),";
            } elseif (Str::startsWith($field, ['is_', 'has_'])) {
                $body[] = "            '{$field}' => StatusHelper::getStatus((bool) \$this->{$field}),";
            } else {
                $body[] = "            '{$field}' => \$this->{$field},";
            }
        }

        foreach ($relations as $rel => $relatedFqcn) {
            $relatedModel = class_exists($relatedFqcn) ? class_basename($relatedFqcn) : 'Related';
            $relatedResourceFqcn = "{$baseNamespace}\\Http\\Resources\\{$relatedModel}Resource";
            $body[] =
"            '{$rel}' => class_exists('{$relatedResourceFqcn}')
                ? new \\{$relatedResourceFqcn}(\$this->whenLoaded('{$rel}'))
                : \$this->whenLoaded('{$rel}'),";
        }

        $bodyBlock = implode("\n", $body);

        return "<?php

namespace {$ns};

{$usesBlock}

class {$className} extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
{$bodyBlock}
        ];
    }
}
";
    }
}
