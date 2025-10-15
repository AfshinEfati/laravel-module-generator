<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class SwaggerDocGenerator
{
    /**
     * @param  array<string, mixed>  $swaggerData
     * @param  array<string, mixed>  $fields
     */
    public static function generate(string $name, string $baseNamespace, array $swaggerData, array $fields = [], bool $force = false): void
    {
        $tag        = (string) ($swaggerData['tag'] ?? $name);
        $operations = $swaggerData['operations'] ?? [];
        $paramName  = $swaggerData['param_name'] ?? null;
        $security   = $swaggerData['security'] ?? [];

        if (empty($operations)) {
            return;
        }

        $paths   = config('module-generator.paths', []);
        $docsRel = $paths['docs'] ?? 'Docs';
        $docsPath = app_path($docsRel);
        File::ensureDirectoryExists($docsPath);

        $namespace = $baseNamespace . '\\Docs';

        self::ensureBaseDocExists($docsPath, $namespace);

        $className = "{$name}Doc";
        $filePath  = $docsPath . DIRECTORY_SEPARATOR . $className . '.php';

        if (!$force && File::exists($filePath)) {
            return;
        }

        $operationsBlock = self::buildOperations((string) $tag, $operations, $paramName);

        $schemesConfig  = is_array($security['schemes'] ?? null) ? $security['schemes'] : [];
        $filteredSchemes = self::filterUndefinedSchemes($schemesConfig, $docsPath);
        $securityBlock   = self::buildSecuritySchemes($filteredSchemes, (bool) ($security['enabled'] ?? false));

        $content = Stub::render('Doc/swagger', [
            'namespace'        => $namespace,
            'class'            => $className,
            'tag'              => $tag,
            'security_schemes' => $securityBlock,
            'operations'       => $operationsBlock,
        ]);

        File::put($filePath, $content);
    }

    private static function ensureBaseDocExists(string $docsPath, string $namespace): void
    {
        $baseDocPath = $docsPath . DIRECTORY_SEPARATOR . 'BaseDoc.php';

        if (File::exists($baseDocPath)) {
            return;
        }

        if (self::projectHasInfoAnnotation([
            $docsPath,
            app_path(),
        ])) {
            return;
        }

        $stubPath = __DIR__ . '/../Stubs/BaseDoc.stub';

        if (!File::exists($stubPath)) {
            return;
        }

        $replacements = [
            '{{ namespace }}'            => $namespace,
            '{{ title }}'                => config('app.name', 'Sample Project API'),
            '{{ version }}'              => '1.0.0',
            '{{ description }}'          => 'This is a sample auto-generated API doc. Customize it as needed.',
            '{{ terms_of_service_url }}' => 'https://example.com/terms',
            '{{ contact_name }}'         => 'API Support',
            '{{ contact_email }}'        => 'support@example.com',
            '{{ contact_url }}'          => 'https://example.com/support',
            '{{ license_name }}'         => 'MIT',
            '{{ license_url }}'          => 'https://opensource.org/licenses/MIT',
            '{{ server_1_url }}'         => config('app.url', 'http://localhost:8000'),
            '{{ server_1_description }}' => 'Local Development Server',
            '{{ server_2_url }}'         => 'https://staging-api.example.com',
            '{{ server_2_description }}' => 'Staging Server',
            '{{ server_3_url }}'         => 'https://api.example.com',
            '{{ server_3_description }}' => 'Production Server',
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            File::get($stubPath)
        );

        File::put($baseDocPath, $content);
    }

    /**
     * @param  array<int, string>  $paths
     */
    private static function projectHasInfoAnnotation(array $paths): bool
    {
        foreach ($paths as $path) {
            if (!is_string($path) || $path === '' || !File::exists($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $contents = File::get($file->getPathname());
                if (strpos($contents, '@OA\\Info(') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $operations
     * @param  array<string, mixed>  $fields
     */
    private static function buildOperations(string $tag, array $operations, ?string $paramName, array $fields): string
    {
        $blocks = [];

        foreach ($operations as $operation) {
            $blocks[] = self::buildOperationBlock($tag, $operation, $paramName, $fields);
        }

        $filtered = array_filter($blocks);

        if (empty($filtered)) {
            return '';
        }

        return implode("\n\n", $filtered) . "\n";
    }

    /**
     * @param  array<string, mixed>  $operation
     * @param  array<string, mixed>  $fields
     */
    private static function buildOperationBlock(string $tag, array $operation, ?string $defaultParam, array $fields): string
    {
        $method     = ucfirst(strtolower((string) ($operation['httpMethod'] ?? 'get')));
        $path       = (string) ($operation['path'] ?? '/api/resource');
        $summary    = (string) ($operation['summary'] ?? 'Endpoint');
        $name       = (string) ($operation['name'] ?? strtolower($method));
        $responses  = $operation['responses'] ?? [];
        $hasBody    = (bool) ($operation['requestBody'] ?? false);
        $hasParam   = (bool) ($operation['pathParam'] ?? false);
        $security   = $operation['security'] ?? [];
        $paramName  = $defaultParam ?? 'id';

        $entries = [];
        $entries[] = "path=\"{$path}\"";
        $entries[] = "summary=\"{$summary}\"";
        $entries[] = "tags={\"{$tag}\"}";

        if ($hasParam) {
            $entries[] = "@OA\\Parameter(\n        name=\"{$paramName}\",\n        in=\"path\",\n        required=true,\n        @OA\\Schema(type=\"integer\")\n    )";
        }

        if ($hasBody) {
            $jsonContent = self::buildJsonContent($fields);
            $entries[] = "@OA\\RequestBody(required=true, {$jsonContent})";
        }

        foreach ($responses as $response) {
            $code = $response['code'] ?? 200;
            $desc = $response['description'] ?? 'Response';
            
            // Add JSON content type for successful responses (2xx)
            if ($code >= 200 && $code < 300 && $code !== 204) {
                $entries[] = "@OA\\Response(\n        response={$code},\n        description=\"{$desc}\",\n        @OA\\JsonContent()\n    )";
            } elseif ($code === 401) {
                // Unauthenticated response with JSON
                $entries[] = "@OA\\Response(\n        response={$code},\n        description=\"{$desc}\",\n        @OA\\JsonContent(\n            @OA\\Property(property=\"message\", type=\"string\", example=\"Unauthenticated.\")\n        )\n    )";
            } elseif ($code === 404) {
                // Not found response with JSON
                $entries[] = "@OA\\Response(\n        response={$code},\n        description=\"{$desc}\",\n        @OA\\JsonContent(\n            @OA\\Property(property=\"message\", type=\"string\", example=\"Resource not found.\")\n        )\n    )";
            } elseif ($code === 422) {
                // Validation error response with JSON
                $entries[] = "@OA\\Response(\n        response={$code},\n        description=\"{$desc}\",\n        @OA\\JsonContent(\n            @OA\\Property(property=\"message\", type=\"string\", example=\"The given data was invalid.\")\n        )\n    )";
            } else {
                $entries[] = "@OA\\Response(response={$code}, description=\"{$desc}\")";
            }
        }

        if (!empty($security) && is_array($security)) {
            $securityEntries = [];
            foreach ($security as $scheme) {
                if (!is_string($scheme) || $scheme === '') {
                    continue;
                }
                $securityEntries[] = '"' . addslashes($scheme) . '":{}';
            }

            if (!empty($securityEntries)) {
                $entries[] = 'security={{' . implode(', ', $securityEntries) . '}}';
            }
        }

        $annotation = self::buildAnnotation($method, $entries);

        $lines   = [];
        $lines[] = '    /**';
        foreach ($annotation as $line) {
            $lines[] = '     * ' . $line;
        }
        $lines[] = '     */';
        $lines[] = "    public function {$name}(): void";
        $lines[] = '    {';
        $lines[] = '    }';

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, string>  $entries
     * @return array<int, string>
     */
    private static function buildAnnotation(string $method, array $entries): array
    {
        $method = ucfirst(strtolower($method));
        $lines = [];
        $lines[] = "@OA\\{$method}(";

        $count = count($entries);
        foreach ($entries as $index => $entry) {
            $suffix = $index === $count - 1 ? '' : ',';
            $entryLines = explode("\n", $entry);
            $entryLineCount = count($entryLines);

            foreach ($entryLines as $lineIndex => $line) {
                $lineSuffix = ($lineIndex === $entryLineCount - 1) ? $suffix : '';
                $lines[] = '     ' . $line . $lineSuffix;
            }
        }

        $lines[] = ')';

        return $lines;
    }

    private static function buildSecuritySchemes(array $schemes, bool $enabled): string
    {
        if (!$enabled || empty($schemes)) {
            return '';
        }

        $blocks = [];

        foreach ($schemes as $name => $definition) {
            if (!is_string($name) || $name === '' || !is_array($definition)) {
                continue;
            }

            $attributes = [
                'securityScheme="' . addslashes($name) . '"',
            ];

            $type = Arr::get($definition, 'type');
            if ($type) {
                $attributes[] = 'type="' . addslashes((string) $type) . '"';
            }

            $scheme = Arr::get($definition, 'scheme');
            if ($scheme) {
                $attributes[] = 'scheme="' . addslashes((string) $scheme) . '"';
            }

            $bearer = Arr::get($definition, 'bearer_format');
            if ($bearer) {
                $attributes[] = 'bearerFormat="' . addslashes((string) $bearer) . '"';
            }

            $description = Arr::get($definition, 'description');
            if ($description) {
                $attributes[] = 'description="' . addslashes((string) $description) . '"';
            }

            $lines = [];
            $lines[] = '    /**';
            $lines[] = '     * @OA\SecurityScheme(';

            $attributeCount = count($attributes);
            foreach ($attributes as $index => $attribute) {
                $suffix = $index === $attributeCount - 1 ? '' : ',';
                $lines[] = '     *     ' . $attribute . $suffix;
            }

            $lines[] = '     * )';
            $lines[] = '     */';

            $methodName = preg_replace('/[^A-Za-z0-9_]/', '', ucfirst($name)) . 'Security';
            if ($methodName === 'Security') {
                $methodName = 'SecurityScheme';
            }

            $lines[] = '    public function ' . $methodName . '(): void';
            $lines[] = '    {';
            $lines[] = '    }';

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks) . "\n";
    }

    /**
     * @param  array<string, array<string, mixed>>  $schemes
     * @return array<string, array<string, mixed>>
     */
    private static function filterUndefinedSchemes(array $schemes, string $docsPath): array
    {
        if (empty($schemes) || !is_dir($docsPath)) {
            return $schemes;
        }

        $remaining = $schemes;

        foreach (File::files($docsPath) as $file) {
            $contents = File::get($file->getPathname());

            foreach (array_keys($remaining) as $name) {
                if (str_contains($contents, 'securityScheme="' . addslashes($name) . '"')) {
                    unset($remaining[$name]);
                }
            }

            if (empty($remaining)) {
                break;
            }
        }

        return $remaining;
    }

    private static function buildJsonContent(array $fields): string
    {
        if (empty($fields)) {
            return '@OA\JsonContent()';
        }

        $properties = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = self::mapTypeToSwagger($field['type']);
            $properties[] = "@OA\\Property(property=\"{$name}\", type=\"{$type}\")";
        }

        $propertiesString = implode(",\n            ", $properties);

        return "@OA\\JsonContent(\n            {$propertiesString}\n        )";
    }

    private static function mapTypeToSwagger(string $type): string
    {
        return match ($type) {
            'integer' => 'integer',
            'decimal', 'float' => 'number',
            'boolean' => 'boolean',
            'date', 'datetime', 'time' => 'string',
            default => 'string',
        };
    }
}
