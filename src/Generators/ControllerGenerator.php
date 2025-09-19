<?php

namespace Efati\ModuleGenerator\Generators;

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

        $imports = array_unique($imports);
        $usesBlock = 'use ' . implode(";\nuse ", $imports) . ';';
        $nameLc = lcfirst($name);

        $requestStoreType  = $withRequests ? "Store{$name}Request" : 'Request';
        $requestUpdateType = $withRequests ? "Update{$name}Request" : 'Request';

        $payloadInitStore = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = " . ($withRequests ? '$request->validated();' : '$request->all();');
        $payloadInitUpdate = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = " . ($withRequests ? '$request->validated();' : '$request->all();');

        $storeArgument  = $usesDto ? '$dto' : '$payload';
        $updateArgument = $usesDto ? '$dto' : '$payload';
        $modelVariable  = '$' . $nameLc;

        $resourceCollection = $usesResource
            ? "        return StatusHelper::successResponse({$name}Resource::collection(\$data), 'success');"
            : "        return StatusHelper::successResponse(\$data, 'success');";
        $resourceSingle = $usesResource
            ? "        return StatusHelper::successResponse(new {$name}Resource({$modelVariable}), 'success');"
            : "        return StatusHelper::successResponse({$modelVariable}, 'success');";
        $resourceUpdated = $usesResource
            ? "        return StatusHelper::successResponse(new {$name}Resource({$modelVariable}), 'updated');"
            : "        return StatusHelper::successResponse({$modelVariable}, 'updated');";
        $resourceCreated = $usesResource
            ? "        return StatusHelper::successResponse(new {$name}Resource(\$model), 'created', 201);"
            : "        return StatusHelper::successResponse(\$model, 'created', 201);";

        return <<<PHP
<?php

namespace {$namespace};

{$usesBlock}

class {$name}Controller
{
    public function __construct(public {$name}Service \$service) {}

    public function index()
    {
        \$data = \$this->service->index();
{$resourceCollection}
    }

    public function store({$requestStoreType} \$request)
    {
{$payloadInitStore}
        \$model = \$this->service->store({$storeArgument});
{$resourceCreated}
    }

    public function show({$name} \${$nameLc}): mixed
    {
{$relationsLoad}
{$resourceSingle}
    }

    public function update({$requestUpdateType} \$request, {$name} \${$nameLc})
    {
{$payloadInitUpdate}
        \$updated = \$this->service->update(\${$nameLc}->id, {$updateArgument});
        if (!\$updated) {
            return StatusHelper::errorResponse('update failed', 422);
        }
        \${$nameLc}->refresh();
{$relationsLoad}
{$resourceUpdated}
    }

    public function destroy({$name} \${$nameLc})
    {
        \$deleted = \$this->service->destroy(\${$nameLc}->id);
        return \$deleted
            ? StatusHelper::successResponse(null, 'deleted', 204)
            : StatusHelper::errorResponse('delete failed', 422);
    }
}
PHP;
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

        $imports = array_unique($imports);
        $usesBlock = 'use ' . implode(";\nuse ", $imports) . ';';

        $nameLc    = lcfirst($name);
        $viewBase  = Str::kebab(Str::pluralStudly($name));
        $routeName = $viewBase;

        $requestStoreType  = $withRequests ? "Store{$name}Request" : 'Request';
        $requestUpdateType = $withRequests ? "Update{$name}Request" : 'Request';

        $payloadInitStore = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = " . ($withRequests ? '$request->validated();' : '$request->all();');
        $payloadInitUpdate = $usesDto
            ? "        \$dto = {$name}DTO::fromRequest(\$request);"
            : "        \$payload = " . ($withRequests ? '$request->validated();' : '$request->all();');

        $storeArgument  = $usesDto ? '$dto' : '$payload';
        $updateArgument = $usesDto ? '$dto' : '$payload';

        return <<<PHP
<?php

namespace {$namespace};

{$usesBlock}

class {$name}Controller extends Controller
{
    public function __construct(public {$name}Service \$service) {}

    public function index(): View
    {
        \$items = \$this->service->index();
        return view('{$viewBase}.index', compact('items'));
    }

    public function create(): View
    {
        return view('{$viewBase}.create');
    }

    public function store({$requestStoreType} \$request): RedirectResponse
    {
        // @todo update validation rules and authorisation as needed.
{$payloadInitStore}
        \$this->service->store({$storeArgument});

        return redirect()->route('{$routeName}.index')
            ->with('status', '{$name} created.');
    }

    public function show({$name} \${$nameLc}): View
    {
{$relationsLoad}
        return view('{$viewBase}.show', compact('{$nameLc}'));
    }

    public function edit({$name} \${$nameLc}): View
    {
{$relationsLoad}
        return view('{$viewBase}.edit', compact('{$nameLc}'));
    }

    public function update({$requestUpdateType} \$request, {$name} \${$nameLc}): RedirectResponse
    {
{$payloadInitUpdate}
        \$this->service->update(\${$nameLc}->id, {$updateArgument});

        return redirect()->route('{$routeName}.show', \${$nameLc})
            ->with('status', '{$name} updated.');
    }

    public function destroy({$name} \${$nameLc}): RedirectResponse
    {
        \$this->service->destroy(\${$nameLc}->id);

        return redirect()->route('{$routeName}.index')
            ->with('status', '{$name} deleted.');
    }
}
PHP;
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
