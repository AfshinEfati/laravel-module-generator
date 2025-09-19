<?php

namespace Efati\ModuleGenerator\Generators;

use Illuminate\Support\Facades\File;

class ProviderGenerator
{
    public static function generateAndRegister(string $name, string $baseNamespace = 'App', bool $force = false): array
    {
        $paths = config('module-generator.paths', []);
        $providerRel = $paths['provider'] ?? ($paths['providers'] ?? 'Providers');

        $providerPath = app_path($providerRel);
        File::ensureDirectoryExists($providerPath);

        $repoNs    = "{$baseNamespace}\\Repositories\\Eloquent\\{$name}Repository";
        $repoIf    = "{$baseNamespace}\\Repositories\\Contracts\\{$name}RepositoryInterface";
        $serviceNs = "{$baseNamespace}\\Services\\{$name}Service";
        $serviceIf = "{$baseNamespace}\\Services\\Contracts\\{$name}ServiceInterface";
        $provNs    = "{$baseNamespace}\\{$providerRel}";
        $provNs    = str_replace('/', '\\', $provNs);
        $class     = "{$name}ServiceProvider";

        $content = "<?php

namespace {$provNs};

use Illuminate\\Support\\ServiceProvider;

class {$class} extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(\\{$repoIf}::class, \\{$repoNs}::class);
        \$this->app->bind(\\{$serviceIf}::class, \\{$serviceNs}::class);
    }

    public function boot(): void
    {
        //
    }
}
";
        $providerFile = $providerPath . "/{$class}.php";
        $results = [
            $providerFile => self::writeFile($providerFile, $content, $force),
        ];

        $results = array_merge($results, self::registerProvider("{$provNs}\\{$class}"));

        return $results;
    }

    private static function registerProvider(string $fqcn): array
    {
        $results = [];
        $bootstrapProviders = base_path('bootstrap/providers.php');
        if (File::exists($bootstrapProviders)) {
            $contents = File::get($bootstrapProviders);
            if (!str_contains($contents, $fqcn . '::class')) {
                $contents = preg_replace(
                    '/return\s+\[(.*)\];/sU',
                    "return [\n    {$fqcn}::class,\n$1];",
                    $contents,
                    1
                );
                File::put($bootstrapProviders, $contents);
                $results[$bootstrapProviders] = true;
            } else {
                $results[$bootstrapProviders] = false;
            }
            return $results;
        }

        $configApp = config_path('app.php');
        if (File::exists($configApp)) {
            $contents = File::get($configApp);
            if (!str_contains($contents, $fqcn . '::class')) {
                $pattern = '/\'providers\'\s*=>\s*\[(.*?)\],/s';
                if (preg_match($pattern, $contents, $m)) {
                    $block = rtrim($m[1]) . "\n        {$fqcn}::class,\n    ";
                    $contents = preg_replace($pattern, "'providers' => [\n{$block}],", $contents, 1);
                    File::put($configApp, $contents);
                    $results[$configApp] = true;
                } else {
                    $results[$configApp] = false;
                }
            } else {
                $results[$configApp] = false;
            }
        }

        return $results;
    }

    private static function writeFile(string $path, string $contents, bool $force): bool
    {
        if (!$force && File::exists($path)) {
            return false;
        }

        File::put($path, $contents);

        return true;
    }
}
