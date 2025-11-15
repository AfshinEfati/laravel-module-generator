<?php

namespace Efati\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SwaggerUICommand extends Command
{
    protected $signature = 'swagger:ui
                            {--port=8000 : The port to serve on}
                            {--host=localhost : The host to serve on}
                            {--refresh : Regenerate swagger.json before serving}';

    protected $description = 'Start a standalone Swagger UI server (no L5-Swagger dependency required)';

    public function handle(): int
    {
        $this->info('🚀 Starting Custom Swagger UI...');

        // Use config values if not overridden by options
        $port = $this->option('port') !== 8000
            ? $this->option('port')
            : config('module-generator.swagger.server.port', 8000);

        $host = $this->option('host') !== 'localhost'
            ? $this->option('host')
            : config('module-generator.swagger.server.host', 'localhost');

        $refresh = $this->option('refresh');

        if ($refresh) {
            $this->call('swagger:generate');
        }

        // Create a simple HTTP server that serves Swagger UI
        $uiPath = base_path('storage/swagger-ui');

        if (!File::exists($uiPath)) {
            $this->error('Swagger UI not initialized. Run: php artisan swagger:init');
            return 1;
        }

        $url = "http://{$host}:{$port}";

        $this->info('');
        $this->info('✨ Swagger UI is running at: ' . $this->formatOutput($url, 'fg=cyan'));
        $this->info('📊 API Documentation: ' . $this->formatOutput("{$url}/docs", 'fg=green'));
        $this->info('');

        // Show current configuration
        $theme = config('module-generator.swagger.theme', 'vanilla');
        $this->line("🎨 Theme: <fg=cyan>{$theme}</>");
        $this->line("📁 Path: <fg=cyan>{$uiPath}</>");

        $this->info('');
        $this->info('Press Ctrl+C to stop the server');
        $this->info('');

        // Use PHP's built-in server
        $command = "php -S {$host}:{$port} -t {$uiPath}";

        passthru($command);

        return 0;
    }

    protected function formatOutput(string $text, string $style): string
    {
        return "<{$style}>{$text}</>";
    }
}
