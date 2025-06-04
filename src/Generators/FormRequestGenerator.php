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
        $modelClass = "{$baseNamespace}\\Models\\{$studly}";
        $table = Str::snake(Str::pluralStudly($name));

        $rulesArray = [];

        // تلاش برای خواندن مایگریشن
        $migrationDir = base_path('database/migrations');
        $migrationFile = collect(File::allFiles($migrationDir))
            ->first(fn($file) => Str::contains($file->getFilename(), "create_{$table}_table"));

        if ($migrationFile) {
            $lines = File::lines($migrationFile->getPathname());

            foreach ($lines as $line) {
                // فیلدهای معمول مثل string, integer ...
                if (preg_match("/->(string|boolean|integer|text|float|date|datetime)\('(\w+)'(,\s*(\d+))?.*?\)/", $line, $matches)) {
                    $type = $matches[1];
                    $field = $matches[2];
                    $maxLength = $matches[4] ?? null;

                    $rule = match ($type) {
                        'string', 'text' => 'string',
                        'boolean' => 'boolean',
                        'integer' => 'integer',
                        'float' => 'numeric',
                        'date', 'datetime' => 'date',
                        default => 'string'
                    };

                    $rulePrefix = str_contains($line, '->nullable()') ? 'nullable' : 'required';
                    $finalRule = "{$rulePrefix}|{$rule}";

                    if ($type === 'string' && $maxLength) {
                        $finalRule .= "|max:{$maxLength}";
                    }

                    if (str_contains($line, '->unique(') || str_contains($line, '->unique()')) {
                        $finalRule .= "|unique:{$table},{$field}";
                    }

                    $rulesArray[$field] = $finalRule;
                }

                // foreignId فیلدها
                if (preg_match("/->foreignId\('(\w+)'\)/", $line, $matches)) {
                    $field = $matches[1];
                    $relatedTable = Str::plural(Str::beforeLast($field, '_id'));
                    $rulePrefix = str_contains($line, '->nullable()') ? 'nullable' : 'required';
                    $rulesArray[$field] = "{$rulePrefix}|exists:{$relatedTable},id";
                }
            }
        }

        // fallback: خواندن از fillable مدل در صورت نبود migration
        if (empty($rulesArray) && class_exists($modelClass)) {
            $model = new $modelClass();
            $fillable = method_exists($model, 'getFillable') ? $model->getFillable() : [];

            foreach ($fillable as $field) {
                $rulesArray[$field] = 'required|string';
            }
        }

        $rulesString = '';
        foreach ($rulesArray as $key => $rule) {
            $rulesString .= "            '{$key}' => '{$rule}',\n";
        }

        foreach (['Store', 'Update'] as $prefix) {
            $className = "{$prefix}{$studly}Request";

            $content = <<<PHP
<?php

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
{$rulesString}        ];
    }
}

PHP;

            File::put("{$requestPath}/{$className}.php", $content);
        }
    }
}
