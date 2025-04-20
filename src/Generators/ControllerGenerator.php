<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControllerGenerator
{
    public static function generate(string $name, ?string $subfolder = null, bool $api = false): void
    {
        $baseNamespace = config('module-generator.base_namespace', 'App');
        $basePath = config('module-generator.paths.controller_base', 'Http/Controllers');
        $fullPath = $basePath . ($subfolder ? '/' . $subfolder : '');

        $controllerPath = app_path($fullPath);
        File::ensureDirectoryExists($controllerPath);

        $namespace = $baseNamespace . '\\' . str_replace('/', '\\', $fullPath);
        $className = Str::studly($name) . 'Controller';

        $content = "<?php

namespace {$namespace};

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class {$className} extends Controller
{
";

        if ($api) {
            $content .= "    public function index() {}
    public function store(Request \$request) {}
    public function show(\$id) {}
    public function update(Request \$request, \$id) {}
    public function destroy(\$id) {}
";
        } else {
            $content .= "    public function handle(Request \$request) {}
";
        }

        $content .= "}
";

        File::put("{$controllerPath}/{$className}.php", $content);
    }
}
