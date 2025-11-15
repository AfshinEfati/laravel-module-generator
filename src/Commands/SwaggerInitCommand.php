<?php

namespace Efati\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SwaggerInitCommand extends Command
{
    protected $signature = 'swagger:init
                            {--force : Overwrite existing files}';

    protected $description = 'Initialize Swagger UI files and assets (no external dependencies)';

    public function handle(): int
    {
        $this->info('📦 Initializing Swagger UI...');

        $storagePath = storage_path('swagger-ui');
        $stubsPath = __DIR__ . '/../../Stubs/SwaggerUI';

        // Create storage directory
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info('✓ Created storage/swagger-ui directory');
        }

        // Copy HTML files
        if (File::exists($stubsPath)) {
            File::copyDirectory($stubsPath, $storagePath, $this->option('force'));
            $this->info('✓ Copied Swagger UI files');
        }

        // Create .htaccess for routing
        $htaccessPath = $storagePath . '/.htaccess';
        if (!File::exists($htaccessPath) || $this->option('force')) {
            File::put($htaccessPath, $this->getHtaccessContent());
            $this->info('✓ Created .htaccess for routing');
        }

        $this->info('');
        $this->info('✨ Swagger UI initialized successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->line('  1. Generate Swagger docs: <fg=cyan>php artisan swagger:generate</>');
        $this->line('  2. Start the server:      <fg=cyan>php artisan swagger:ui</>');
        $this->line('  3. Open in browser:       <fg=cyan>http://localhost:8000/docs</>');
        $this->info('');

        return 0;
    }

    protected function getHtaccessContent(): string
    {
        return <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Serve actual files
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]

    # Serve directories
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Rewrite everything else to index.html
    RewriteRule ^ /index.html [QSA,L]
</IfModule>
HTACCESS;
    }
}
