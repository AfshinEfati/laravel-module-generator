<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControllerGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        ?string $controllerSubfolder = null,
        bool $isApi = false,
        bool $withRequests = false,
        bool $usesDto = true,
        bool $usesResource = true,
        bool $force = false
    ): array {
        $paths = config('module-generator.paths', []);
        $configuredRel = $paths['controller'] ?? ($paths['controllers'] ?? null);
        $defaultRel = $isApi ? 'Http/Controllers/Api/V1' : 'Http/Controllers';
        $controllerRel = is_string($configuredRel) && $configuredRel !== '' ? $configuredRel : $defaultRel;

        $controllerPath = app_path($controllerRel . ($controllerSubfolder ? '/' . trim($controllerSubfolder, '/\\') : ''));
        File::ensureDirectoryExists($controllerPath);

        $modelFqcn     = "{$baseNamespace}\\Models\\{$name}";
        $serviceFqcn   = "{$baseNamespace}\\Services\\{$name}Service";
        $helperFqcn    = "{$baseNamespace}\\Helpers\\StatusHelper";
        $resourceFqcn  = "{$baseNamespace}\\Http\\Resources\\{$name}Resource";
        $dtoFqcn       = "{$baseNamespace}\\DTOs\\{$name}DTO";
        $storeReqFqcn  = "{$baseNamespace}\\Http\\Requests\\Store{$name}Request";
        $updateReqFqcn = "{$baseNamespace}\\Http\\Requests\\Update{$name}Request";

        $relationsLoad = self::relationsLoadSnippet($modelFqcn);
        $namespace     = self::controllerNamespace($baseNamespace, $controllerRel, $controllerSubfolder);
        $className     = "{$name}Controller";

        if ($isApi) {
            $content = self::buildApiController(
                $name,
                $namespace,
                $modelFqcn,
                $serviceFqcn,
                $helperFqcn,
                $resourceFqcn,
                $dtoFqcn,
                $storeReqFqcn,
                $updateReqFqcn,
                $withRequests,
                $usesDto,
                $usesResource,
                $relationsLoad
            );
        } else {
            $content = self::buildWebController(
                $name,
                $namespace,
                $baseNamespace,
                $modelFqcn,
                $serviceFqcn,
                $dtoFqcn,
                $storeReqFqcn,
                $updateReqFqcn,
                $withRequests,
                $usesDto,
                $relationsLoad
            );
        }

        $target = $controllerPath . "/{$className}.php";

        return [$target => self::writeFile($target, $content, $force)];
    }

    private static function buildApiController(
        string $name,
        string $namespace,
        string $modelFqcn,
        string $serviceFqcn,
        string $helperFqcn,
        string $resourceFqcn,
        string $dtoFqcn,
        string $storeReqFqcn,
        string $updateReqFqcn,
        bool $withRequests,
        bool $usesDto,
        bool $usesResource,
        string $relationsLoad
    ): string {
        $imports = [
            $modelFqcn,
            $serviceFqcn,
            $helperFqcn,
        ];

        if ($usesResource) {
            $imports[] = $resourceFqcn;
        }
        if ($usesDto) {
            $imports[] = $dtoFqcn;
        }
        if (!$withRequests) {
            $imports[] = 'Illuminate\\Http\\Request';
        }
        if ($withRequests) {
            $imports[] = $storeReqFqcn;
            $imports[] = $updateReqFqcn;
        }

        $usesBlock = self::buildUses($imports);
        $nameLc    = lcfirst($name);

        $requestStoreType  = $withRequests ? "Store{$name}Request" : 'Request';
        $requestUpdateType = $withRequests ? "Update{$name}Request" : 'Request';

        $payloadSource = $withRequests ? '$request->validated();' : '$request->all();';
        $payloadInitStore = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = {$payloadSource}";
        $payloadInitUpdate = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = {$payloadSource}";

        $storeArgument  = $usesDto ? '$dto' : '$payload';
        $updateArgument = $usesDto ? '$dto' : '$payload';

        $indexBody = implode("\n", [
            '        $data = $this->service->index();',
            $usesResource
                ? "        return StatusHelper::successResponse({$name}Resource::collection(\$data), 'success');"
                : "        return StatusHelper::successResponse(\$data, 'success');",
        ]);

        $modelVariable = '$' . $nameLc;

        $resourceSingle = $usesResource
            ? "        return StatusHelper::successResponse(new {$name}Resource({$modelVariable}), 'success');"
            : "        return StatusHelper::successResponse({$modelVariable}, 'success');";
        $resourceUpdated = $usesResource
            ? "        return StatusHelper::successResponse(new {$name}Resource({$modelVariable}), 'updated');"
            : "        return StatusHelper::successResponse({$modelVariable}, 'updated');";
        $resourceCreated = $usesResource
            ? "        return StatusHelper::successResponse(new {$name}Resource(\$model), 'created', 201);"
            : "        return StatusHelper::successResponse(\$model, 'created', 201);";

        return Stub::render('Controller/api', [
            'namespace'           => $namespace,
            'uses'                => $usesBlock,
            'class'               => $name . 'Controller',
            'service_class'       => $name . 'Service',
            'index_body'          => $indexBody,
            'store_request_type'  => $requestStoreType,
            'store_payload'       => $payloadInitStore,
            'store_argument'      => $storeArgument,
            'store_response'      => $resourceCreated,
            'model_class'         => $name,
            'model_variable'      => $nameLc,
            'relations_load'      => $relationsLoad,
            'show_response'       => $resourceSingle,
            'update_request_type' => $requestUpdateType,
            'update_payload'      => $payloadInitUpdate,
            'update_argument'     => $updateArgument,
            'update_response'     => $resourceUpdated,
        ]);
    }

    private static function buildWebController(
        string $name,
        string $namespace,
        string $baseNamespace,
        string $modelFqcn,
        string $serviceFqcn,
        string $dtoFqcn,
        string $storeReqFqcn,
        string $updateReqFqcn,
        bool $withRequests,
        bool $usesDto,
        string $relationsLoad
    ): string {
        $imports = [
            $modelFqcn,
            $serviceFqcn,
            "{$baseNamespace}\\Http\\Controllers\\Controller",
            'Illuminate\\Http\\RedirectResponse',
            'Illuminate\\Http\\Request',
            'Illuminate\\View\\View',
        ];

        if ($usesDto) {
            $imports[] = $dtoFqcn;
        }
        if ($withRequests) {
            $imports[] = $storeReqFqcn;
            $imports[] = $updateReqFqcn;
        }

        $usesBlock = self::buildUses($imports);

        $nameLc    = lcfirst($name);
        $viewBase  = Str::kebab(Str::pluralStudly($name));
        $routeName = $viewBase;

        $requestStoreType  = $withRequests ? "Store{$name}Request" : 'Request';
        $requestUpdateType = $withRequests ? "Update{$name}Request" : 'Request';

        $payloadSource = $withRequests ? '$request->validated();' : '$request->all();';
        $payloadInitStore = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = {$payloadSource}";
        $payloadInitUpdate = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = {$payloadSource}";

        $storeArgument  = $usesDto ? '$dto' : '$payload';
        $updateArgument = $usesDto ? '$dto' : '$payload';

        return Stub::render('Controller/web', [
            'namespace'           => $namespace,
            'uses'                => $usesBlock,
            'class'               => $name . 'Controller',
            'service_class'       => $name . 'Service',
            'view_base'           => $viewBase,
            'route_name'          => $routeName,
            'name'                => $name,
            'store_request_type'  => $requestStoreType,
            'store_payload'       => $payloadInitStore,
            'store_argument'      => $storeArgument,
            'model_class'         => $name,
            'model_variable'      => $nameLc,
            'relations_load'      => $relationsLoad,
            'update_request_type' => $requestUpdateType,
            'update_payload'      => $payloadInitUpdate,
            'update_argument'     => $updateArgument,
        ]);
    }

    private static function buildUses(array $imports): string
    {
        $imports = array_values(array_unique($imports));

        return 'use ' . implode(";\nuse ", $imports) . ';';
    }

    private static function controllerNamespace(string $baseNamespace, string $controllerRel, ?string $sub): string
    {
        $rel = str_replace('/', '\\', trim($controllerRel, '/\\'));
        $ns  = "{$baseNamespace}\\{$rel}";
        if ($sub) {
            $sub = str_replace(['/', '\\'], '\\', trim($sub, '/\\'));
            $ns .= "\\{$sub}";
        }
        return $ns;
    }

    private static function relationsLoadSnippet(string $modelFqcn): string
    {
        if (!class_exists($modelFqcn)) {
            return "        // no relations loaded (model class not found)\n";
        }

        $instance = new $modelFqcn();
        $relations = [];
        foreach (get_class_methods($instance) as $method) {
            if (in_array($method, ['boot', 'booted'])) {
                continue;
            }
            try {
                $relation = $instance->$method();
                if (is_object($relation) && method_exists($relation, 'getRelated')) {
                    $relations[] = $method;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (empty($relations)) {
            return "        // no relations to load\n";
        }

        $relationsList = "'" . implode("','", $relations) . "'";
        $varName = '$' . lcfirst(class_basename($modelFqcn));

        return "        {$varName}->load([{$relationsList}]);\n";
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
