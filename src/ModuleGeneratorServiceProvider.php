<?php

namespace Efati\ModuleGenerator;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\ServiceProvider;
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
        $this->publishes([
            __DIR__ . '/Stubs/BaseRepository.php'           => app_path('Repositories/Eloquent/BaseRepository.php'),
            __DIR__ . '/Stubs/BaseRepositoryInterface.php'  => app_path('Repositories/Contracts/BaseRepositoryInterface.php'),
            __DIR__ . '/Stubs/BaseService.php'              => app_path('Services/BaseService.php'),
            __DIR__ . '/Stubs/BaseServiceInterface.php'     => app_path('Services/Contracts/BaseServiceInterface.php'),
            __DIR__ . '/config/module-generator.php'        => config_path('module-generator.php'),
            __DIR__ . '/Stubs/Helpers/StatusHelper.php'     => app_path('Helpers/StatusHelper.php'),
        ], 'module-generator');

        static::registerCarbonMacros();
    }

    public static function registerCarbonMacros(): void
    {
        if (! class_exists(Carbon::class)) {
            return;
        }

        if (! Carbon::hasMacro('toJalali')) {
            Carbon::macro('toJalali', function (DateTimeZone|string|null $timezone = null): Goli {
                /** @var \Carbon\Carbon $this */

                return Goli::instance($this, $timezone);
            });
        }

        if (! Carbon::hasMacro('fromJalali')) {
            Carbon::macro('fromJalali', function (string $datetime, DateTimeZone|string|null $timezone = null): Carbon {
                return Goli::parseJalali($datetime, $timezone)->toCarbon();
            });
        }
    }
}
