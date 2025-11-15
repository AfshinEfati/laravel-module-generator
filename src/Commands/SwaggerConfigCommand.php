<?php

namespace Efati\ModuleGenerator\Commands;

use Efati\ModuleGenerator\Support\SwaggerConfigManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SwaggerConfigCommand extends Command
{
    protected $signature = 'swagger:config
                            {--show : Show current configuration}
                            {--export-env : Export configuration as .env format}
                            {--theme= : Set theme (vanilla, tailwind, dark)}
                            {--primary-color= : Set primary color (hex)}
                            {--secondary-color= : Set secondary color (hex)}
                            {--title= : Set API title}
                            {--reset : Reset to defaults}';

    protected $description = 'Configure Swagger UI settings without editing files';

    public function handle(): int
    {
        if ($this->option('show')) {
            return $this->showConfiguration();
        }

        if ($this->option('export-env')) {
            return $this->exportConfiguration();
        }

        if ($this->option('reset')) {
            return $this->resetConfiguration();
        }

        // Interactive mode if no options provided
        if (!$this->hasOptions()) {
            return $this->interactiveMode();
        }

        return $this->updateConfiguration();
    }

    protected function hasOptions(): bool
    {
        return $this->option('theme')
            || $this->option('primary-color')
            || $this->option('secondary-color')
            || $this->option('title');
    }

    protected function showConfiguration(): int
    {
        $this->info('📋 Current Swagger Configuration:');
        $this->info('');

        $config = SwaggerConfigManager::getConfig();

        $this->line('<fg=cyan>Theme:</> ' . ($config['theme'] ?? 'vanilla'));
        $this->line('');

        $this->line('<fg=cyan>Colors:</>');
        $colors = $config['colors'] ?? [];
        foreach ($colors as $name => $value) {
            $this->line("  {$name}: <fg=cyan>{$value}</>");
        }

        $this->line('');
        $this->line('<fg=cyan>Fonts:</>');
        $fonts = $config['fonts'] ?? [];
        foreach ($fonts as $name => $value) {
            $this->line("  {$name}: <fg=cyan>{$value}</>");
        }

        $this->line('');
        $this->line('<fg=cyan>Dark Mode:</>');
        $darkMode = $config['dark_mode'] ?? [];
        $this->line("  enabled: " . ($darkMode['enabled'] ? '<fg=green>yes</>' : '<fg=red>no</>'));
        $this->line("  default: <fg=cyan>" . ($darkMode['default'] ?? 'auto') . '</>', 2);
        $this->line("  persist: " . ($darkMode['persist'] ? '<fg=green>yes</>' : '<fg=red>no</>'));

        $this->line('');
        $this->line('<fg=cyan>Display:</>');
        $display = $config['display'] ?? [];
        $this->line("  title: <fg=cyan>" . ($display['title'] ?? 'API Documentation') . '</>', 2);
        $this->line("  show_models: " . ($display['show_models'] ? '<fg=green>yes</>' : '<fg=red>no</>'));

        $this->line('');
        $this->info('💡 Edit .env file to change these settings:');
        $this->line('   SWAGGER_THEME=tailwind');
        $this->line('   SWAGGER_COLOR_PRIMARY=#8b5cf6');
        $this->line('   SWAGGER_DARK_MODE_DEFAULT=dark');

        return 0;
    }

    protected function exportConfiguration(): int
    {
        $envContent = SwaggerConfigManager::exportAsEnv();
        $envPath = base_path('.env.swagger');

        File::put($envPath, $envContent);

        $this->info('✓ Configuration exported');
        $this->info('');
        $this->line("📁 Location: <fg=cyan>{$envPath}</>");
        $this->line('');
        $this->info('To use this configuration:');
        $this->line('1. Review the file: <fg=cyan>cat .env.swagger</>');
        $this->line('2. Copy settings to .env: <fg=cyan>cat .env.swagger >> .env</>');
        $this->line('3. Customize as needed');

        return 0;
    }

    protected function resetConfiguration(): int
    {
        if (!$this->confirm('Are you sure you want to reset to default configuration?')) {
            return 0;
        }

        $defaults = [
            'theme' => 'vanilla',
            'colors' => [
                'primary' => '#3b82f6',
                'primary_dark' => '#1e40af',
                'primary_light' => '#eff6ff',
                'secondary' => '#06b6d4',
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'dark' => '#1f2937',
                'light' => '#f9fafb',
                'border' => '#e5e7eb',
                'text' => '#374151',
                'text_light' => '#6b7280',
            ],
            'fonts' => [
                'family' => 'system-ui, -apple-system, sans-serif',
                'mono' => '"Fira Code", "Courier New", monospace',
            ],
            'dark_mode' => [
                'enabled' => true,
                'default' => 'auto',
                'persist' => true,
            ],
        ];

        $this->info('✓ Configuration reset to defaults');
        $this->info('');
        $this->line('Clear these from .env to use defaults:');
        foreach ($defaults['colors'] as $name => $value) {
            $this->line("  SWAGGER_COLOR_" . strtoupper($name));
        }

        return 0;
    }

    protected function interactiveMode(): int
    {
        $this->info('⚙️  Swagger UI Configuration');
        $this->info('');

        // Theme selection
        $theme = $this->choice(
            'Select theme:',
            ['vanilla', 'tailwind', 'dark'],
            0
        );

        // Color presets
        $usePreset = $this->confirm('Use color preset?', false);
        if ($usePreset) {
            $preset = $this->choice('Select color preset:', [
                'blue' => 'Professional Blue',
                'purple' => 'Modern Purple',
                'green' => 'Green & Teal',
                'gray' => 'Corporate Gray',
                'orange' => 'Energetic Orange',
            ]);

            $presets = $this->getColorPresets();
            $colors = $presets[$preset] ?? $presets['blue'];

            $this->updateEnvFile('SWAGGER_THEME', $theme);
            foreach ($colors as $name => $value) {
                $this->updateEnvFile('SWAGGER_COLOR_' . strtoupper($name), $value);
            }

            $this->info('✓ Configuration updated');
        } else {
            $this->updateEnvFile('SWAGGER_THEME', $theme);
            $this->info('✓ Theme updated');
        }

        // Reinitialize with new theme
        if ($this->confirm('Reinitialize Swagger UI with new settings?', true)) {
            $this->call('swagger:init', ['--force' => true]);
        }

        return 0;
    }

    protected function updateConfiguration(): int
    {
        if ($theme = $this->option('theme')) {
            $this->updateEnvFile('SWAGGER_THEME', $theme);
            $this->info("✓ Theme set to: <fg=cyan>{$theme}</>");
        }

        if ($primaryColor = $this->option('primary-color')) {
            $this->updateEnvFile('SWAGGER_COLOR_PRIMARY', $primaryColor);
            $this->info("✓ Primary color set to: <fg=cyan>{$primaryColor}</>");
        }

        if ($secondaryColor = $this->option('secondary-color')) {
            $this->updateEnvFile('SWAGGER_COLOR_SECONDARY', $secondaryColor);
            $this->info("✓ Secondary color set to: <fg=cyan>{$secondaryColor}</>");
        }

        if ($title = $this->option('title')) {
            $this->updateEnvFile('SWAGGER_UI_TITLE', $title);
            $this->info("✓ Title set to: <fg=cyan>{$title}</>");
        }

        $this->info('');
        $this->info('💡 Run <fg=cyan>php artisan swagger:init --force</> to apply changes');

        return 0;
    }

    protected function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Check if key exists
        if (strpos($envContent, $key . '=') !== false) {
            // Update existing
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        } else {
            // Add new
            $envContent .= "\n{$key}={$value}";
        }

        File::put($envPath, $envContent);
    }

    protected function getColorPresets(): array
    {
        return [
            'blue' => [
                'primary' => '#3b82f6',
                'primary_dark' => '#1e40af',
                'primary_light' => '#eff6ff',
                'secondary' => '#06b6d4',
            ],
            'purple' => [
                'primary' => '#8b5cf6',
                'primary_dark' => '#7c3aed',
                'primary_light' => '#f5f3ff',
                'secondary' => '#d946ef',
            ],
            'green' => [
                'primary' => '#059669',
                'primary_dark' => '#047857',
                'primary_light' => '#ecfdf5',
                'secondary' => '#14b8a6',
            ],
            'gray' => [
                'primary' => '#6b7280',
                'primary_dark' => '#4b5563',
                'primary_light' => '#f3f4f6',
                'secondary' => '#9ca3af',
            ],
            'orange' => [
                'primary' => '#f97316',
                'primary_dark' => '#ea580c',
                'primary_light' => '#fff7ed',
                'secondary' => '#fb923c',
            ],
        ];
    }
}
