<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControllerGenerator
{
    public static function generate(string $name, ?string $subfolder = null, bool $api = false): void
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

        $content = "<?php

namespace {$namespace};

use Illuminate\\Http\\Request;
use {base_namespace}\\Http\\Controllers\\Controller;
use {base_namespace}\\Services\\{$serviceName};
use {base_namespace}\\Models\\{$modelName};

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

    public function store(Request \$request)
    {
        //
    }

    public function show({$modelName} \${$varModel})
    {
        //
    }

    public function update(Request \$request, {$modelName} \${$varModel})
    {
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