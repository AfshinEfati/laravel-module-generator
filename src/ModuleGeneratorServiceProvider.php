<?php

namespace Efati\ModuleGenerator;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Efati\ModuleGenerator\Commands\MakeModuleCommand;
use Efati\ModuleGenerator\Support\Goli;

class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/module-generator.php',
            'module-generator'
        );

        $this->app->bind('goli', function ($app, array $parameters = []) {
            $datetime = $parameters['datetime'] ?? ($parameters[0] ?? null);
            $timezone = $parameters['timezone'] ?? ($parameters[1] ?? null);

            return Goli::instance($datetime, $timezone);
        });

        $this->app->alias('goli', Goli::class);

        $this->commands([
            MakeModuleCommand::class,
        ]);
    }

    public function boot(): void
    {
        $defaultPublishables = [
            __DIR__ . '/Stubs/BaseRepository.php'           => app_path('Repositories/Eloquent/BaseRepository.php'),
            __DIR__ . '/Stubs/BaseRepositoryInterface.php'  => app_path('Repositories/Contracts/BaseRepositoryInterface.php'),
            __DIR__ . '/Stubs/BaseService.php'              => app_path('Services/BaseService.php'),
            __DIR__ . '/Stubs/BaseServiceInterface.php'     => app_path('Services/Contracts/BaseServiceInterface.php'),
            __DIR__ . '/config/module-generator.php'        => config_path('module-generator.php'),
            __DIR__ . '/Stubs/Helpers/StatusHelper.php'     => app_path('Helpers/StatusHelper.php'),
        ];

        $this->publishes($defaultPublishables, 'module-generator');

        $resourceStubPath = function_exists('resource_path')
            ? resource_path('stubs/module-generator')
            : app()->resourcePath('stubs/module-generator');

        $stubPublishables = [
            __DIR__ . '/Stubs/Module' => $resourceStubPath,
        ];

        $this->publishes($stubPublishables, 'module-generator-stubs');

        if ($this->app->runningInConsole()) {
            $this->ensurePublished($defaultPublishables + $stubPublishables);
        }
    }

    protected function ensurePublished(array $paths): void
    {
        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $this->app->make(Filesystem::class);

        foreach ($paths as $from => $to) {
            if (is_dir($from)) {
                if (! $filesystem->isDirectory($to)) {
                    $filesystem->copyDirectory($from, $to);
                }

                continue;
            }

            if ($filesystem->exists($to)) {
                continue;
            }

            $directory = dirname($to);

            if (! $filesystem->isDirectory($directory)) {
                $filesystem->makeDirectory($directory, 0755, true);
            }

            $filesystem->copy($from, $to);
        }
    }
}
