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
        $constructorBody = '';
        $requestMap = '';

        foreach ($fields as $field) {
            $properties .= "    public mixed \$$field;\n";
            $constructorParams .= "        \$this->$field = \$$field;\n";
            $requestMap .= "            \$$field = \$request->$field;\n";
        }

        $constructorSignature = implode(', ', array_map(fn($f) => "public mixed \$$f", $fields));
        $args = implode(', ', array_map(fn($f) => "\$$f", $fields));

        File::put("{$dtoPath}/{$name}DTO.php", "<?php

namespace App\\DTOs;

use Illuminate\Http\Request;

class {$name}DTO
{
{$properties}

    public function __construct({$constructorSignature})
    {
{$constructorParams}
    }

    public static function fromRequest(Request \$request): self
    {
{$requestMap}

        return new self({$args});
    }
}
");
    }
}
