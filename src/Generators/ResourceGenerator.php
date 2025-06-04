<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Illuminate\Database\Eloquent\Model;

class ResourceGenerator
{
    public static function generate(string $name): void
    {
        $baseNamespace = config('module-generator.base_namespace', 'App');
        $resourcePath = app_path('Http/Resources');
        File::ensureDirectoryExists($resourcePath);

        $modelName = Str::studly($name);
        $modelClass = "{$baseNamespace}\\Models\\{$modelName}";
        $resourceClass = "{$modelName}Resource";
        $filename = "{$resourcePath}/{$resourceClass}.php";

        // Base use statements
        $uses = [
            'use Illuminate\Http\Request;',
            'use Illuminate\Http\Resources\Json\JsonResource;',
            'use App\Helpers\StatusHelper;',
            "use {$modelClass};",
        ];

        $fields = [];
        $relations = [];

        if (class_exists($modelClass)) {
            /** @var Model $model */
            $model = new $modelClass();

            // Get fillable fields
            $fillable = method_exists($model, 'getFillable') ? $model->getFillable() : [];

            foreach ($fillable as $field) {
                if (Str::endsWith($field, ['_at'])) {
                    $fields[$field] = "StatusHelper::formatDates(\$this->{$field})";
                } elseif (Str::startsWith($field, ['is_', 'has_'])) {
                    $fields[$field] = "StatusHelper::getStatus(\$this->{$field})";
                } else {
                    $fields[$field] = "\$this->{$field}";
                }
            }

            // Analyze relationships using reflection
            $reflection = new ReflectionClass($model);
            foreach ($reflection->getMethods() as $method) {
                if ($method->class !== $modelClass) continue;

                if ($method->getNumberOfParameters() === 0) {
                    try {
                        $return = $method->invoke($model);
                        $relationClass = get_class($return);

                        if (in_array(class_basename($relationClass), ['BelongsTo', 'HasOne'])) {
                            $relationName = $method->getName();
                            $relatedModel = class_basename($return->getRelated());
                            $relatedResource = "{$relatedModel}Resource";
                            $fields[$relationName] = "new {$relatedResource}(\$this->whenLoaded('{$relationName}'))";
                            $uses[] = "use {$baseNamespace}\\Http\\Resources\\{$relatedResource};";
                        }
                    } catch (\Throwable $e) {
                        // Silent fail for unsupported methods
                    }
                }
            }
        }

        // Convert field array to output string
        $arrayLines = [];
        foreach ($fields as $key => $value) {
            $arrayLines[] = "            '{$key}' => {$value},";
        }
        $arrayString = implode("\n", $arrayLines);

        // Compile all use statements
        $useBlock = implode("\n", array_unique($uses));

        // Final content
        $content = <<<PHP
<?php

namespace {$baseNamespace}\Http\Resources;

{$useBlock}

/** @mixin {$modelName} */
class {$resourceClass} extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
{$arrayString}
        ];
    }
}
PHP;

        File::put($filename, $content);
    }
}
