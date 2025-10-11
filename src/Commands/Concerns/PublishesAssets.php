<?php

namespace Efati\ModuleGenerator\Commands\Concerns;

use Illuminate\Support\Facades\File;

trait PublishesAssets
{
    private function publishInitialAssets(): void
    {
        $this->call('vendor:publish', [
            '--provider' => 'Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider',
            '--tag' => 'module-generator',
        ]);
    }

    private function publishSwaggerAssets(): void
    {
        if (File::exists(public_path('vendor/l5-swagger'))) {
            $this->info('✅ Swagger assets are already published.');
            return;
        }

        $this->info('Publishing Swagger assets...');
        $this->call('vendor:publish', [
            '--provider' => 'L5Swagger\\L5SwaggerServiceProvider',
        ]);
        $this->info('✅ Swagger assets published successfully.');
    }
}