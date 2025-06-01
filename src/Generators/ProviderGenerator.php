<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ProviderGenerator
{
    public static function generate(string $name): void
    {
        $providerPath = app_path(config('module-generator.paths.provider'));
        File::ensureDirectoryExists($providerPath);

        $content = "<?php

namespace {base_namespace}\\Providers;;

use Illuminate\\Support\\ServiceProvider;
use {base_namespace}\\Repositories\\Contracts\\{$name}RepositoryInterface;;
use {base_namespace}\\Repositories\\Eloquent\\{$name}Repository;;
use {base_namespace}\\Services\\{$name}Service;;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind({$name}RepositoryInterface::class, {$name}Repository::class);

        \$this->app->bind({$name}Service::class, function (\$app) {
            return new {$name}Service(
                \$app->make({$name}RepositoryInterface::class)
            );
        });
    }
}
";

        File::put("{$providerPath}/{$name}ServiceProvider.php", $content);

        // Append provider to bootstrap/providers.php
        $bootstrapFile = base_path('bootstrap/providers.php');
        if (File::exists($bootstrapFile)) {
            $current = File::get($bootstrapFile);

use {base_namespace}\\Providers\\{$name}ServiceProvider;";;
            $providerRegister = "{$name}ServiceProvider::class,";

            if (!str_contains($current, $providerUse)) {
                $current = preg_replace('/<\?php\s+/', "<?php\n\n{$providerUse}\n", $current, 1);
            }

            if (!str_contains($current, $providerRegister)) {
                $current = preg_replace('/return\s+\[\s*/', "return [\n    {$providerRegister}\n", $current, 1);
            }

            File::put($bootstrapFile, $current);
        }
    }
}