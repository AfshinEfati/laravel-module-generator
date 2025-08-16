<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ControllerGenerator
{
    public static function generate(
        string $name,
        string $baseNamespace = 'App',
        ?string $controllerSubfolder = null,
        bool $isApi = false,
        bool $withRequests = false
    ): void {
        $paths = config('module-generator.paths', []);

        $controllerRel  = $paths['controller'] ?? ($paths['controllers'] ?? 'Http/Controllers/Api/V1');
        $controllerPath = app_path($controllerRel . ($controllerSubfolder ? '/' . trim($controllerSubfolder, '/\\') : ''));
        File::ensureDirectoryExists($controllerPath);

        $modelFqcn     = "{$baseNamespace}\\Models\\{$name}";
        $serviceFqcn   = "{$baseNamespace}\\Services\\{$name}Service";
        $helperFqcn    = "{$baseNamespace}\\Helpers\\StatusHelper";
        $resourceFqcn  = "{$baseNamespace}\\Http\\Resources\\{$name}Resource";
        $dtoFqcn       = "{$baseNamespace}\\DTOs\\{$name}DTO";

        $storeReqFqcn  = "{$baseNamespace}\\Http\\Requests\\Store{$name}Request";
        $updateReqFqcn = "{$baseNamespace}\\Http\\Requests\\Update{$name}Request";

        $useRequests   = $withRequests ? "use {$storeReqFqcn};\nuse {$updateReqFqcn};\n" : '';
        $reqStoreType  = $withRequests ? "Store{$name}Request"  : 'Illuminate\\Http\\Request';
        $reqUpdateType = $withRequests ? "Update{$name}Request" : 'Illuminate\\Http\\Request';

        $relationsLoad = self::relationsLoadSnippet($modelFqcn);
        $ns        = self::controllerNamespace($baseNamespace, $controllerRel, $controllerSubfolder);
        $className = "{$name}Controller";
        $nameLc    = lcfirst($name);

        $content = <<<PHP
<?php

namespace {$ns};

use {$modelFqcn};
use {$serviceFqcn};
use {$helperFqcn};
use {$resourceFqcn};
use {$dtoFqcn};
use Illuminate\\Http\\Request;
{$useRequests}
class {$className}
{
    public function __construct(public {$name}Service \$service) {}

    public function index()
    {
        \$data = \$this->service->index();
        return StatusHelper::successResponse({$name}Resource::collection(\$data), 'success');
    }

    public function store({$reqStoreType} \$request)
    {
        \$dto = {$name}DTO::fromRequest(\$request);
        \$model = \$this->service->store(\$dto);
        return StatusHelper::successResponse(new {$name}Resource(\$model), 'created', 201);
    }

    public function show({$name} \${$nameLc}): mixed
    {
{$relationsLoad}
        return StatusHelper::successResponse(new {$name}Resource(\${$nameLc}), 'success');
    }

    public function update({$reqUpdateType} \$request, {$name} \${$nameLc})
    {
        \$dto = {$name}DTO::fromRequest(\$request);
        \$updated = \$this->service->update(\${$nameLc}->id, \$dto);
        if (!\$updated) {
            return StatusHelper::errorResponse('update failed', 422);
        }
        \${$nameLc}->refresh();
{$relationsLoad}
        return StatusHelper::successResponse(new {$name}Resource(\${$nameLc}), 'updated');
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

        $target = $controllerPath . "/{$className}.php";
        $ok = File::put($target, $content);
        if ($ok === false) {
            throw new \RuntimeException("Failed to write controller file at: " . $target);
        }
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

        $m = new $modelFqcn();
        $rels = [];
        foreach (get_class_methods($m) as $method) {
            if (in_array($method, ['boot', 'booted'])) continue;
            try {
                $ret = $m->$method();
                if (is_object($ret) && method_exists($ret, 'getRelated')) {
                    $rels[] = $method;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        if (empty($rels)) {
            return "        // no relations to load\n";
        }

        $relsList = "'" . implode("','", $rels) . "'";
        $var      = '$' . lcfirst(class_basename($modelFqcn));
        return "        {$var}->load([{$relsList}]);\n";
    }
}
