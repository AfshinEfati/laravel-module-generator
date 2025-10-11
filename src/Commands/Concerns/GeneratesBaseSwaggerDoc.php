<?php

namespace Efati\ModuleGenerator\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait GeneratesBaseSwaggerDoc
{
    private function generateBaseDocFile(bool $force = false): void
    {
        $outputDir = app_path('Docs');
        File::ensureDirectoryExists($outputDir);

        $filePath = $outputDir . '/BaseDoc.php';
        if (!$force && File::exists($filePath)) {
            return;
        }

        $stub = File::get(__DIR__ . '/../../Stubs/BaseDoc.stub');

        $replacements = [
            '{{ namespace }}' => config('module-generator.base_namespace', 'App') . '\\Docs',
            '{{ title }}' => config('app.name', 'Laravel API'),
            '{{ version }}' => '1.0.0',
            '{{ description }}' => 'This is a sample auto-generated API doc. Customize it as needed.',
            '{{ terms_of_service_url }}' => 'https://example.com/terms',
            '{{ contact_name }}' => 'API Support',
            '{{ contact_email }}' => 'support@example.com',
            '{{ contact_url }}' => 'https://example.com/support',
            '{{ license_name }}' => 'MIT',
            '{{ license_url }}' => 'https://opensource.org/licenses/MIT',
            '{{ server_1_url }}' => config('app.url'),
            '{{ server_1_description }}' => 'Local Development Server',
            '{{ server_2_url }}' => 'https://staging-api.example.com',
            '{{ server_2_description }}' => 'Staging Server',
            '{{ server_3_url }}' => 'https://api.example.com',
            '{{ server_3_description }}' => 'Production Server',
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

        File::put($filePath, $content);

        $this->info('✅ Generated: BaseDoc.php');
    }
}