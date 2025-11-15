<?php

namespace Efati\ModuleGenerator\Http\Controllers;

use Illuminate\Support\Facades\File;

class SwaggerUIController
{
    /**
     * Display Swagger UI
     */
    public function index()
    {
        $uiPath = storage_path('swagger-ui/index.html');

        if (!File::exists($uiPath)) {
            return response()->view('error', [
                'message' => 'Swagger UI not initialized. Run: php artisan swagger:init',
            ], 404);
        }

        return File::get($uiPath);
    }

    /**
     * Serve swagger.json
     */
    public function spec()
    {
        $specPath = public_path('api/swagger.json');

        if (!File::exists($specPath)) {
            return response()->json([
                'error' => 'Swagger spec not generated. Run: php artisan swagger:generate',
            ], 404);
        }

        return response()
            ->file($specPath)
            ->header('Content-Type', 'application/json');
    }
}
