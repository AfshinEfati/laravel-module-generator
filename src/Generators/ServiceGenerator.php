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

namespace {base_namespace}\\Services;;

use {base_namespace}\\Services\\Base\\BaseService;;
use {base_namespace}\\Services\\Contracts\\{$name}ServiceInterface;;

class {$name}Service extends BaseService implements {$name}ServiceInterface
{
    //
}
");

        File::put("{$contractPath}/{$name}ServiceInterface.php", "<?php

namespace {base_namespace}\\Services\\Contracts;;

use {base_namespace}\\Services\\Base\\BaseServiceInterface;;

interface {$name}ServiceInterface extends BaseServiceInterface
{
    //
}
");
    }
}