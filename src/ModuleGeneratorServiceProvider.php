<?php

namespace Efati\ModuleGenerator;

use Illuminate\Support\ServiceProvider;
use Efati\ModuleGenerator\Commands\MakeModuleCommand;

class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/module-generator.php',
            'module-generator'
        );

        $this->commands([
            MakeModuleCommand::class,
        ]);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/Stubs/BaseRepository.php'          => app_path('Repositories/Eloquent/BaseRepository.php'),
            __DIR__ . '/Stubs/BaseRepositoryInterface.php' => app_path('Repositories/Contracts/BaseRepositoryInterface.php'),
            __DIR__ . '/Stubs/BaseService.php'             => app_path('Services/BaseService.php'),
            __DIR__ . '/Stubs/BaseServiceInterface.php'    => app_path('Services/Contracts/BaseServiceInterface.php'),
            __DIR__ . '/config/module-generator.php'       => config_path('module-generator.php'),
        ], 'module-generator');
    }
}
