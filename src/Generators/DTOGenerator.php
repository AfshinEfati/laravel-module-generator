<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class DTOGenerator
{
    public static function generate(string $name): void
    {
        $dtoPath = app_path(config('module-generator.paths.dto'));
        File::ensureDirectoryExists($dtoPath);

        File::put("{$dtoPath}/{$name}DTO.php", "<?php

namespace App\\DTOs;

class {$name}DTO
{
    //
}
");
    }
}
