<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ServiceGenerator
{
    public static function generate(string $name): void
    {
        $servicePath = app_path(config('module-generator.paths.service'));
        $contractPath = app_path(config('module-generator.paths.service_contract'));

        File::ensureDirectoryExists($servicePath);
        File::ensureDirectoryExists($contractPath);

        File::put("{$servicePath}/{$name}Service.php", "<?php

namespace App\\Services;

class {$name}Service implements \\App\\Services\\Contracts\\{$name}ServiceInterface
{
    //
}
");

        File::put("{$contractPath}/{$name}ServiceInterface.php", "<?php

namespace App\\Services\\Contracts;

interface {$name}ServiceInterface
{
    //
}
");
    }
}
