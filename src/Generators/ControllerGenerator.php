<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControllerGenerator
{
    public static function generate(string $name, ?string $subfolder = null, bool $api = false, bool $withFormRequests = false): void
    {
        $baseNamespace = config('module-generator.base_namespace', 'App');
        $basePath = config('module-generator.paths.controller', 'Http/Controllers');
        $fullPath = $basePath . ($subfolder ? '/' . $subfolder : '');

        $controllerPath = app_path($fullPath);
        File::ensureDirectoryExists($controllerPath);

        $namespace = $baseNamespace . '\\' . str_replace('/', '\\', $fullPath);
        $className = Str::studly($name) . 'Controller';
        $modelName = Str::studly($name);
        $serviceName = $modelName . 'Service';
        $varModel = Str::camel($name);
        $dtoName = $modelName . 'DTO';

        $storeRequest = "Store{$modelName}Request";
        $updateRequest = "Update{$modelName}Request";

        $useRequests = $withFormRequests ? "
use {$baseNamespace}\\Http\\Requests\\{$storeRequest};
use {$baseNamespace}\\Http\\Requests\\{$updateRequest};" : "";

        $useDTO = "use {$baseNamespace}\\DTOs\\{$dtoName};";

        $typeStore = $withFormRequests ? $storeRequest : 'Request';
        $typeUpdate = $withFormRequests ? $updateRequest : 'Request';

        $content = "<?php

namespace {$namespace};

use Illuminate\\Http\\Request;
use {$baseNamespace}\\Http\\Controllers\\Controller;
use {$baseNamespace}\\Services\\{$serviceName};
use {$baseNamespace}\\Models\\{$modelName};
{$useDTO}{$useRequests}

class {$className} extends Controller
{
    public function __construct(public {$serviceName} \${$varModel}Service)
    {
        // Middleware can be applied here if needed
    }
";

        if ($api) {
            $content .= "
    public function index()
    {
        //
    }

    public function store({$typeStore} \$request)
    {
        \$dto = {$dtoName}::fromRequest(\$request);
        //
    }

    public function show({$modelName} \${$varModel})
    {
        //
    }

    public function update({$typeUpdate} \$request, {$modelName} \${$varModel})
    {
        \$dto = {$dtoName}::fromRequest(\$request);
        //
    }

    public function destroy({$modelName} \${$varModel})
    {
        //
    }";
        } else {
            $content .= "
    public function handle(Request \$request)
    {
        //
    }";
        }

        $content .= "
}
";

        File::put("{$controllerPath}/{$className}.php", $content);
    }
}
