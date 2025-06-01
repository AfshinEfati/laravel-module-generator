<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class DTOGenerator
{
    public static function generate(string $name): void
    {
        $dtoPath = app_path(config('module-generator.paths.dto'));
        File::ensureDirectoryExists($dtoPath);

        $modelClass = "App\\Models\\{$name}";
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} does not exist.");
        }

        $model = new $modelClass();
        $fields = $model->getFillable();

        $properties = '';
        $constructorParams = '';
        $signature = '';
        $requestMap = '';
        $args = [];

        foreach ($fields as $field) {
            $properties .= "    public mixed \$$field;\n";
            $signature .= "mixed \$$field, ";
            $constructorParams .= "        \$this->$field = \$$field;\n";
            $requestMap .= "        \$$field = \$request->$field;\n";
            $args[] = "\$$field";
        }

        $signature = rtrim($signature, ', ');
        $argsLine = implode(', ', $args);

        $baseNamespace = config('module-generator.base_namespace');

        File::put("{$dtoPath}/{$name}DTO.php", "<?php

namespace {$baseNamespace}\\DTOs;

use Illuminate\\Http\\Request;

class {$name}DTO
{
{$properties}

    public function __construct({$signature})
    {
{$constructorParams}
    }

    public static function fromRequest(Request \$request): self
    {
{$requestMap}

        return new self({$argsLine});
    }
}
");
    }
}
