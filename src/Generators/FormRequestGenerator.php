<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FormRequestGenerator
{
    public static function generate(string $name): void
    {
        $baseNamespace = config('module-generator.base_namespace', 'App');
        $requestPath = app_path(config('module-generator.paths.form_request', 'Http/Requests') . '/' . $name);
        File::ensureDirectoryExists($requestPath);

        $namespace = $baseNamespace . '\\' . str_replace('/', '\\', config('module-generator.paths.form_request')) . '\\' . $name;
        $studly = Str::studly($name);

        foreach (['Store', 'Update'] as $prefix) {
            $className = "{$prefix}{$studly}Request";

            $content = "<?php

namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;

class {$className} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // define rules here
        ];
    }
}
";
            File::put("{$requestPath}/{$className}.php", $content);
        }
    }
}