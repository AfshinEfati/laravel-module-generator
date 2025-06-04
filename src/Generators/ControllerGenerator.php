<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class ControllerGenerator
{
    public static function generate(string $name, ?string $subfolder = null, bool $api = false, bool $withRequests = false): void
    {
        $baseNamespace = config('module-generator.base_namespace', 'App');
        $basePath = config('module-generator.paths.controller', 'Http/Controllers');
        $formRequestPath = config('module-generator.paths.form_request', 'Http/Requests');

        $fullPath = $basePath . ($subfolder ? '/' . $subfolder : '');
        $controllerPath = app_path($fullPath);
        File::ensureDirectoryExists($controllerPath);

        $namespace = $baseNamespace . '\\' . str_replace('/', '\\', $fullPath);
        $className = Str::studly($name) . 'Controller';
        $modelName = Str::studly($name);
        $serviceName = $modelName . 'Service';
        $varModel = Str::camel($name);
        $modelClass = "{$baseNamespace}\\Models\\{$modelName}";

        // Base use statements
        $uses = [
            "{$baseNamespace}\\Helpers\\StatusHelper",
            "{$baseNamespace}\\Services\\{$serviceName}",
            "{$baseNamespace}\\Models\\{$modelName}",
            "{$baseNamespace}\\Http\\Resources\\{$modelName}Resource",
            "{$baseNamespace}\\Http\\Controllers\\Controller",
            "Illuminate\\Http\\Request"
        ];

        $storeRequest = 'Request';
        $updateRequest = 'Request';

        if ($withRequests) {
            $requestNamespace = "{$baseNamespace}\\{$formRequestPath}\\{$modelName}";
            $requestNamespace = str_replace('/', '\\', $requestNamespace);
            $storeRequest = "Store{$modelName}Request";
            $updateRequest = "Update{$modelName}Request";
            $uses[] = "{$requestNamespace}\\{$storeRequest}";
            $uses[] = "{$requestNamespace}\\{$updateRequest}";
        }

        // Properly escape backslashes for PHP string output
//        $uses = array_map(fn($use) => 'use ' . str_replace('\\', '\\\\', $use) . ';', array_unique($uses));
        $uses = array_map(fn($use) => 'use ' . $use . ';', array_unique($uses));

        $useBlock = implode("\n", $uses);

        // Detect model relations
        $relations = self::extractModelRelations($modelClass);
        $loadString = empty($relations)
            ? ''
            : "\${$varModel}->load([" . implode(', ', array_map(fn($r) => "'$r'", $relations)) . "]);";

        $content = <<<PHP
<?php

namespace {$namespace};

{$useBlock}

class {$className} extends Controller
{
    public function __construct(public {$serviceName} \${$varModel}Service)
    {
        // Middleware can be applied here if needed
    }

    public function index()
    {
        \$items = \$this->{$varModel}Service->all();
        return StatusHelper::successResponse({$modelName}Resource::collection(\$items), '{$modelName}s retrieved successfully.');
    }

    public function store({$storeRequest} \$request)
    {
        \$created = \$this->{$varModel}Service->create(\$request->validated());
        return StatusHelper::successResponse(new {$modelName}Resource(\$created->refresh()), '{$modelName} created successfully.', 201);
    }

    public function show({$modelName} \${$varModel})
    {
        {$loadString}
        return StatusHelper::successResponse(new {$modelName}Resource(\${$varModel}), '{$modelName} retrieved successfully.');
    }

    public function update({$updateRequest} \$request, {$modelName} \${$varModel})
    {
        \$updated = \$this->{$varModel}Service->update(\${$varModel}->id, \$request->validated());
        if (!\$updated) {
            return StatusHelper::errorResponse('Failed to update {$modelName}.', 500);
        }

        {$loadString}
        return StatusHelper::successResponse(new {$modelName}Resource(\${$varModel}->refresh()), '{$modelName} updated successfully.');
    }

    public function destroy({$modelName} \${$varModel})
    {
        \$deleted = \$this->{$varModel}Service->delete(\${$varModel}->id);
        if (!\$deleted) {
            return StatusHelper::errorResponse('Failed to delete {$modelName}.', 500);
        }

        return StatusHelper::successResponse(null, '{$modelName} deleted successfully.');
    }
}
PHP;

        File::put("{$controllerPath}/{$className}.php", $content);
    }

    /**
     * Extracts all valid Eloquent relationship method names from the model class.
     */
    protected static function extractModelRelations(string $modelClass): array
    {
        if (!class_exists($modelClass)) return [];

        $model = new $modelClass();
        $relations = [];

        try {
            $reflection = new ReflectionClass($model);
            foreach ($reflection->getMethods() as $method) {
                if (
                    $method->class === $modelClass &&
                    $method->getNumberOfParameters() === 0 &&
                    !$method->isStatic()
                ) {
                    try {
                        $return = $method->invoke($model);
                        $relationClass = class_basename(get_class($return));

                        if (in_array($relationClass, [
                            'BelongsTo', 'HasOne', 'HasMany',
                            'MorphOne', 'MorphMany', 'MorphTo', 'MorphToMany'
                        ])) {
                            $relations[] = $method->getName();
                        }
                    } catch (\Throwable $e) {
                        // Skip invalid or non-relation methods
                    }
                }
            }
        } catch (\Throwable $e) {
            return [];
        }

        return $relations;
    }
}
