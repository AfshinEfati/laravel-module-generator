<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Efati\ModuleGenerator\Generators\SwaggerDocGenerator;

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
        bool $withSwagger = false,
        bool $force = false,
        bool $withActions = false
    ): array {
        $paths = config('module-generator.paths', []);
        $configuredRel = $paths['controller'] ?? ($paths['controllers'] ?? null);
        $defaultRel = $isApi ? 'Http/Controllers/Api/V1' : 'Http/Controllers';
        $controllerRel = is_string($configuredRel) && $configuredRel !== '' ? $configuredRel : $defaultRel;

        $controllerPath = app_path($controllerRel . ($controllerSubfolder ? '/' . trim($controllerSubfolder, '/\\') : ''));
        File::ensureDirectoryExists($controllerPath);

        $modelFqcn     = "{$baseNamespace}\\Models\\{$name}";
        $serviceFqcn   = "{$baseNamespace}\\Services\\{$name}Service";
        $helperFqcn    = "{$baseNamespace}\\Helpers\\ApiResponseHelper";
        $resourceFqcn  = "{$baseNamespace}\\Http\\Resources\\{$name}Resource";
        $dtoFqcn       = "{$baseNamespace}\\DTOs\\{$name}DTO";
        $requestNamespace = "{$baseNamespace}\\Http\\Requests\\{$name}";
        $storeReqFqcn  = "{$requestNamespace}\\Store{$name}Request";
        $updateReqFqcn = "{$requestNamespace}\\Update{$name}Request";

        $relationsLoad = self::relationsLoadSnippet($modelFqcn, $withActions ? 'model' : null);
        $namespace     = self::controllerNamespace($baseNamespace, $controllerRel, $controllerSubfolder);
        $className     = "{$name}Controller";
        $actionsNamespace = $baseNamespace . '\\Actions\\' . $name;
        $swaggerDocs   = self::swaggerDocs($withSwagger, $isApi, $name, $controllerSubfolder, $baseNamespace);
        if ($swaggerDocs !== null) {
            SwaggerDocGenerator::generate($name, $baseNamespace, $swaggerDocs, $force);
        }

        if ($isApi) {
            $content = $withActions
                ? self::buildApiControllerWithActions(
                    $name,
                    $namespace,
                    $modelFqcn,
                    $helperFqcn,
                    $resourceFqcn,
                    $dtoFqcn,
                    $storeReqFqcn,
                    $updateReqFqcn,
                    $withRequests,
                    $usesDto,
                    $usesResource,
                    $relationsLoad,
                    $actionsNamespace
                )
                : self::buildApiController(
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
            $content = $withActions
                ? self::buildWebControllerWithActions(
                    $name,
                    $namespace,
                    $baseNamespace,
                    $modelFqcn,
                    $dtoFqcn,
                    $storeReqFqcn,
                    $updateReqFqcn,
                    $withRequests,
                    $usesDto,
                    $relationsLoad,
                    $actionsNamespace
                )
                : self::buildWebController(
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
            ? "        return ApiResponseHelper::successResponse({$name}Resource::collection(\$data), 'success');"
            : "        return ApiResponseHelper::successResponse(\$data, 'success');",
        ]);

        $modelVariable = '$' . $nameLc;

        $resourceSingle = $usesResource
            ? "        return ApiResponseHelper::successResponse(new {$name}Resource({$modelVariable}), 'success');"
            : "        return ApiResponseHelper::successResponse({$modelVariable}, 'success');";
        $resourceUpdated = $usesResource
            ? "        return ApiResponseHelper::successResponse(new {$name}Resource({$modelVariable}), 'updated');"
            : "        return ApiResponseHelper::successResponse({$modelVariable}, 'updated');";
        $resourceCreated = $usesResource
            ? "        return ApiResponseHelper::successResponse(new {$name}Resource(\$model), 'created', 201);"
            : "        return ApiResponseHelper::successResponse(\$model, 'created', 201);";

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

    private static function buildApiControllerWithActions(
        string $name,
        string $namespace,
        string $modelFqcn,
        string $helperFqcn,
        string $resourceFqcn,
        string $dtoFqcn,
        string $storeReqFqcn,
        string $updateReqFqcn,
        bool $withRequests,
        bool $usesDto,
        bool $usesResource,
        string $relationsLoad,
        string $actionsNamespace
    ): string {
        $imports = [
            $modelFqcn,
            $helperFqcn,
            $actionsNamespace . '\\List' . $name . 'Action',
            $actionsNamespace . '\\Show' . $name . 'Action',
            $actionsNamespace . '\\Create' . $name . 'Action',
            $actionsNamespace . '\\Update' . $name . 'Action',
            $actionsNamespace . '\\Delete' . $name . 'Action',
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
            '        $data = ($this->listAction)();',
            $usesResource
            ? "        return ApiResponseHelper::successResponse({$name}Resource::collection(\$data), 'success');"
            : "        return ApiResponseHelper::successResponse(\$data, 'success');",
        ]);

        $storeResponse = $usesResource
            ? "        return ApiResponseHelper::successResponse(new {$name}Resource(\$model), 'created', 201);"
            : "        return ApiResponseHelper::successResponse(\$model, 'created', 201);";

        $showResponse = $usesResource
            ? "        return ApiResponseHelper::successResponse(new {$name}Resource(\$model), 'success');"
            : "        return ApiResponseHelper::successResponse(\$model, 'success');";

        $updateResponse = $usesResource
            ? "        return ApiResponseHelper::successResponse(new {$name}Resource(\$model), 'updated');"
            : "        return ApiResponseHelper::successResponse(\$model, 'updated');";

        return Stub::render('Controller/api-actions', [
            'namespace'           => $namespace,
            'uses'                => $usesBlock,
            'class'               => $name . 'Controller',
            'name'                => $name,
            'index_body'          => $indexBody,
            'store_request_type'  => $requestStoreType,
            'store_payload'       => $payloadInitStore,
            'store_argument'      => $storeArgument,
            'store_response'      => $storeResponse,
            'model_class'         => $name,
            'model_variable'      => $nameLc,
            'relations_load'      => $relationsLoad,
            'show_response'       => $showResponse,
            'update_request_type' => $requestUpdateType,
            'update_payload'      => $payloadInitUpdate,
            'update_argument'     => $updateArgument,
            'update_response'     => $updateResponse,
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

    private static function buildWebControllerWithActions(
        string $name,
        string $namespace,
        string $baseNamespace,
        string $modelFqcn,
        string $dtoFqcn,
        string $storeReqFqcn,
        string $updateReqFqcn,
        bool $withRequests,
        bool $usesDto,
        string $relationsLoad,
        string $actionsNamespace
    ): string {
        $imports = [
            $modelFqcn,
            "{$baseNamespace}\\Http\\Controllers\\Controller",
            'Illuminate\\Http\\RedirectResponse',
            'Illuminate\\Http\\Request',
            'Illuminate\\View\\View',
            $actionsNamespace . '\\List' . $name . 'Action',
            $actionsNamespace . '\\Show' . $name . 'Action',
            $actionsNamespace . '\\Create' . $name . 'Action',
            $actionsNamespace . '\\Update' . $name . 'Action',
            $actionsNamespace . '\\Delete' . $name . 'Action',
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

        return Stub::render('Controller/web-actions', [
            'namespace'           => $namespace,
            'uses'                => $usesBlock,
            'class'               => $name . 'Controller',
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

    /**
     * Build Swagger metadata for generating standalone documentation classes.
     *
     * @return array{tag: string, param_name: string, operations: array<int, array<string, mixed>>, base_path: string, namespace: string}|null
     */
    private static function swaggerDocs(bool $enabled, bool $isApi, string $name, ?string $controllerSubfolder, string $baseNamespace): ?array
    {
        if (!$enabled || !$isApi) {
            return null;
        }

        $tag          = Str::studly($name);
        $resourceSlug = Str::kebab(Str::pluralStudly($name));
        $paramName    = Str::camel($name);

        $segments = [];
        if (is_string($controllerSubfolder) && $controllerSubfolder !== '') {
            foreach (preg_split('/[\/\\\\]+/', trim($controllerSubfolder, '/\\\\')) as $segment) {
                if ($segment !== '') {
                    $segments[] = Str::kebab($segment);
                }
            }
        }

        $basePath = '/api';
        if (!empty($segments)) {
            $basePath .= '/' . implode('/', $segments);
        }
        $basePath .= '/' . $resourceSlug;

        $operations = [
            [
                'name'        => 'index',
                'httpMethod'  => 'Get',
                'path'        => $basePath,
                'summary'     => "List {$tag}",
                'requestBody' => false,
                'pathParam'   => false,
                'responses'   => [
                    ['code' => 200, 'description' => 'Successful response'],
                ],
            ],
            [
                'name'        => 'store',
                'httpMethod'  => 'Post',
                'path'        => $basePath,
                'summary'     => "Create {$tag}",
                'requestBody' => true,
                'pathParam'   => false,
                'responses'   => [
                    ['code' => 201, 'description' => 'Created'],
                    ['code' => 422, 'description' => 'Validation error'],
                ],
            ],
            [
                'name'        => 'show',
                'httpMethod'  => 'Get',
                'path'        => $basePath . '/{' . $paramName . '}',
                'summary'     => "Show {$tag}",
                'requestBody' => false,
                'pathParam'   => true,
                'responses'   => [
                    ['code' => 200, 'description' => 'Successful response'],
                    ['code' => 404, 'description' => 'Not found'],
                ],
            ],
            [
                'name'        => 'update',
                'httpMethod'  => 'Put',
                'path'        => $basePath . '/{' . $paramName . '}',
                'summary'     => "Update {$tag}",
                'requestBody' => true,
                'pathParam'   => true,
                'responses'   => [
                    ['code' => 200, 'description' => 'Updated'],
                    ['code' => 422, 'description' => 'Validation error'],
                    ['code' => 404, 'description' => 'Not found'],
                ],
            ],
            [
                'name'        => 'destroy',
                'httpMethod'  => 'Delete',
                'path'        => $basePath . '/{' . $paramName . '}',
                'summary'     => "Delete {$tag}",
                'requestBody' => false,
                'pathParam'   => true,
                'responses'   => [
                    ['code' => 204, 'description' => 'Deleted'],
                    ['code' => 404, 'description' => 'Not found'],
                ],
            ],
        ];

        return [
            'tag'        => $tag,
            'param_name' => $paramName,
            'operations' => $operations,
            'base_path'  => $basePath,
            'namespace'  => $baseNamespace,
        ];
    }

    private static function buildUses(array $imports): string
    {
        $imports = array_values(array_unique(array_filter($imports)));

        return $imports ? 'use ' . implode(";\nuse ", $imports) . ';' : '';
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

    private static function relationsLoadSnippet(string $modelFqcn, ?string $variableOverride = null): string
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
        if ($variableOverride !== null) {
            $varName = '$' . ltrim($variableOverride, '$');
        }

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
