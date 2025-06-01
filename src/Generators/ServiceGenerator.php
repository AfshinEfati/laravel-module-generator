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

        $baseNamespace = config('module-generator.base_namespace');

        File::put("{$servicePath}/{$name}Service.php", "<?php

namespace {$baseNamespace}\\Services;

use {$baseNamespace}\\Services\\BaseService;
use {$baseNamespace}\\Services\\Contracts\\{$name}ServiceInterface;

class {$name}Service extends BaseService implements {$name}ServiceInterface
{
    //
}
");

        File::put("{$contractPath}/{$name}ServiceInterface.php", "<?php

namespace {$baseNamespace}\\Services\\Contracts;

use {$baseNamespace}\\Services\\Contracts\\BaseServiceInterface;

interface {$name}ServiceInterface extends BaseServiceInterface
{
    //
}
");
    }
}
