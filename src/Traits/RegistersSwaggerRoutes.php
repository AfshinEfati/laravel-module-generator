<?php

namespace Efati\ModuleGenerator\Traits;

use Illuminate\Routing\Router;

trait RegistersSwaggerRoutes
{
    /**
     * Register Swagger documentation routes
     *
     * Usage in routes/api.php:
     * use Efati\ModuleGenerator\Traits\RegistersSwaggerRoutes;
     *
     * Route::middleware(['api'])->group(function () {
     *     Route::registerSwaggerRoutes();
     * });
     */
    public static function registerSwaggerRoutes(): void
    {
        $router = app(Router::class);

        $router->group(['prefix' => 'docs', 'name' => 'api.docs.'], function ($router) {
            $router->get('/', function () {
                $uiPath = storage_path('swagger-ui/index.html');

                if (!\Illuminate\Support\Facades\File::exists($uiPath)) {
                    return response()->json([
                        'error' => 'Swagger UI not initialized',
                        'message' => 'Run: php artisan swagger:init',
                    ], 404);
                }

                return response()
                    ->file($uiPath)
                    ->header('Content-Type', 'text/html');
            })->name('index');

            $router->get('swagger.json', function () {
                $specPath = public_path('api/swagger.json');

                if (!\Illuminate\Support\Facades\File::exists($specPath)) {
                    return response()->json([
                        'error' => 'Swagger spec not generated',
                        'message' => 'Run: php artisan swagger:generate',
                    ], 404);
                }

                return response()
                    ->file($specPath)
                    ->header('Content-Type', 'application/json');
            })->name('spec');
        });
    }
}
