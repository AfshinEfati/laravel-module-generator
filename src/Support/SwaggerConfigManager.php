<?php

namespace Efati\ModuleGenerator\Support;

use Illuminate\Support\Facades\File;

class SwaggerConfigManager
{
    /**
     * Get Swagger configuration
     */
    public static function getConfig(): array
    {
        return config('module-generator.swagger', []);
    }

    /**
     * Get theme name
     */
    public static function getTheme(): string
    {
        return config('module-generator.swagger.theme', 'vanilla');
    }

    /**
     * Get color configuration
     */
    public static function getColors(): array
    {
        return config('module-generator.swagger.colors', [
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
        ]);
    }

    /**
     * Get fonts configuration
     */
    public static function getFonts(): array
    {
        return config('module-generator.swagger.fonts', [
            'family' => 'system-ui, -apple-system, sans-serif',
            'mono' => '"Fira Code", "Courier New", monospace',
        ]);
    }

    /**
     * Get dark mode configuration
     */
    public static function getDarkModeConfig(): array
    {
        return config('module-generator.swagger.dark_mode', [
            'enabled' => true,
            'default' => 'auto',
            'persist' => true,
        ]);
    }

    /**
     * Generate CSS variables from colors config
     */
    public static function generateCSSVariables(): string
    {
        $colors = self::getColors();
        $css = ":root {\n";

        foreach ($colors as $name => $value) {
            $varName = str_replace('_', '-', $name);
            $css .= "    --{$varName}: {$value};\n";
        }

        // Add fonts
        $fonts = self::getFonts();
        $css .= "    --font-family: {$fonts['family']};\n";
        $css .= "    --font-mono: {$fonts['mono']};\n";

        $css .= "}\n";

        return $css;
    }

    /**
     * Generate Tailwind color config
     */
    public static function generateTailwindColors(): array
    {
        $colors = self::getColors();
        $primary = $colors['primary'] ?? '#3b82f6';

        // Create primary color palette
        $primaryPalette = self::generateColorPalette($primary);

        return [
            'primary' => $primaryPalette,
        ];
    }

    /**
     * Generate color palette from a hex color
     */
    public static function generateColorPalette(string $hex): array
    {
        // This is a simplified version - in production, use a proper library
        $baseColors = [
            50 => 'f0f9ff',
            100 => 'e0f2fe',
            200 => 'bae6fd',
            300 => '7dd3fc',
            400 => '38bdf8',
            500 => '0ea5e9',
            600 => '0284c7',
            700 => '0369a1',
            800 => '075985',
            900 => '0c3d66',
        ];

        // For now, return the base colors
        // In a real implementation, you'd generate tints and shades
        return array_map(fn($hex) => "#{$hex}", $baseColors);
    }

    /**
     * Inject colors into HTML file
     */
    public static function injectColorsIntoHtml(string $htmlContent): string
    {
        $theme = self::getTheme();

        if ($theme === 'vanilla' || $theme === 'tailwind') {
            $colors = self::getColors();
            $cssVars = self::generateCSSVariables();

            // Find the style tag and inject CSS variables
            if (preg_match('/<style[^>]*>/', $htmlContent, $matches)) {
                $styleTag = $matches[0];
                $injection = $styleTag . "\n" . $cssVars;
                $htmlContent = str_replace($styleTag, $injection, $htmlContent);
            }
        }

        if ($theme === 'dark') {
            $darkConfig = self::getDarkModeConfig();
            $defaultMode = $darkConfig['default'] ?? 'auto';
            $persist = $darkConfig['persist'] ?? true;

            // Inject dark mode settings
            $htmlContent = str_replace(
                "darkMode: 'auto',",
                "darkMode: '{$defaultMode}',\npersist: " . ($persist ? 'true' : 'false') . ",",
                $htmlContent
            );
        }

        return $htmlContent;
    }

    /**
     * Get UI display configuration
     */
    public static function getDisplayConfig(): array
    {
        return config('module-generator.swagger.display', [
            'title' => 'API Documentation',
            'description' => 'REST API Documentation',
            'show_models' => true,
            'show_examples' => true,
            'persist_auth' => true,
        ]);
    }

    /**
     * Get server configuration
     */
    public static function getServerConfig(): array
    {
        return config('module-generator.swagger.server', [
            'port' => 8000,
            'host' => 'localhost',
        ]);
    }

    /**
     * Get spec configuration
     */
    public static function getSpecConfig(): array
    {
        return config('module-generator.swagger.spec', [
            'path' => 'storage/swagger-ui',
            'filename' => 'swagger.json',
        ]);
    }

    /**
     * Apply colors to UI file
     */
    public static function applyColorsToUI(): bool
    {
        $storagePath = storage_path('swagger-ui');
        $indexPath = $storagePath . '/index.html';

        if (!File::exists($indexPath)) {
            return false;
        }

        $htmlContent = File::get($indexPath);
        $updatedContent = self::injectColorsIntoHtml($htmlContent);

        if ($updatedContent !== $htmlContent) {
            File::put($indexPath, $updatedContent);
            return true;
        }

        return false;
    }

    /**
     * Export configuration as environment variables
     */
    public static function exportAsEnv(): string
    {
        $colors = self::getColors();
        $fonts = self::getFonts();
        $darkMode = self::getDarkModeConfig();
        $display = self::getDisplayConfig();
        $server = self::getServerConfig();
        $spec = self::getSpecConfig();

        $env = "# Swagger UI Configuration\n";
        $env .= "SWAGGER_THEME=" . self::getTheme() . "\n";
        $env .= "\n# Colors\n";

        foreach ($colors as $name => $value) {
            $env .= "SWAGGER_COLOR_" . strtoupper($name) . "={$value}\n";
        }

        $env .= "\n# Fonts\n";
        $env .= "SWAGGER_FONT_FAMILY=" . $fonts['family'] . "\n";
        $env .= "SWAGGER_FONT_MONO=" . $fonts['mono'] . "\n";

        $env .= "\n# Dark Mode\n";
        $env .= "SWAGGER_DARK_MODE_ENABLED=" . ($darkMode['enabled'] ? 'true' : 'false') . "\n";
        $env .= "SWAGGER_DARK_MODE_DEFAULT=" . $darkMode['default'] . "\n";
        $env .= "SWAGGER_DARK_MODE_PERSIST=" . ($darkMode['persist'] ? 'true' : 'false') . "\n";

        $env .= "\n# Display\n";
        $env .= "SWAGGER_UI_TITLE=" . $display['title'] . "\n";
        $env .= "SWAGGER_UI_DESCRIPTION=" . $display['description'] . "\n";
        $env .= "SWAGGER_PERSIST_AUTH=" . ($display['persist_auth'] ? 'true' : 'false') . "\n";

        $env .= "\n# Server\n";
        $env .= "SWAGGER_SERVER_PORT=" . $server['port'] . "\n";
        $env .= "SWAGGER_SERVER_HOST=" . $server['host'] . "\n";

        $env .= "\n# Spec Output\n";
        $env .= "SWAGGER_SPEC_PATH=" . $spec['path'] . "\n";
        $env .= "SWAGGER_SPEC_FILENAME=" . $spec['filename'] . "\n";

        return $env;
    }
}
