<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ProviderGenerator
{
    public static function generate(string $name): void
    {
        $providerPath = app_path(config('module-generator.paths.provider'));
        File::ensureDirectoryExists($providerPath);

        File::put("{$providerPath}/{$name}ServiceProvider.php", "<?php

namespace App\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
}
");
    }
}
