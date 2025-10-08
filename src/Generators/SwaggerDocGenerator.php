<?php

namespace Efati\ModuleGenerator\Generators;

use Efati\ModuleGenerator\Support\Stub;
use Illuminate\Support\Facades\File;

class SwaggerDocGenerator
{
    /**
     * @param  array<string, mixed>  $swaggerData
     */
    public static function generate(string $name, string $baseNamespace, array $swaggerData, bool $force = false): void
    {
        $tag        = (string) ($swaggerData['tag'] ?? $name);
        $operations = $swaggerData['operations'] ?? [];
        $paramName  = $swaggerData['param_name'] ?? null;

        if (empty($operations)) {
            return;
        }

        $paths = config('module-generator.paths', []);
        $docsRel = $paths['docs'] ?? 'Docs';
        $docsPath = app_path($docsRel);
        File::ensureDirectoryExists($docsPath);

        $className = "{$name}Doc";
        $filePath  = $docsPath . DIRECTORY_SEPARATOR . $className . '.php';

        if (!$force && File::exists($filePath)) {
            return;
        }

        $namespace = $baseNamespace . '\\Docs';
        $operationsBlock = self::buildOperations((string) $tag, $operations, $paramName);

        $content = Stub::render('Doc/swagger', [
            'namespace' => $namespace,
            'class'     => $className,
            'tag'       => $tag,
            'operations'=> $operationsBlock,
        ]);

        File::put($filePath, $content);
    }

    /**
     * @param  array<int, array<string, mixed>>  $operations
     */
    private static function buildOperations(string $tag, array $operations, ?string $paramName): string
    {
        $blocks = [];

        foreach ($operations as $operation) {
            $blocks[] = self::buildOperationBlock($tag, $operation, $paramName);
        }

        $filtered = array_filter($blocks);

        if (empty($filtered)) {
            return '';
        }

        return implode("\n\n", $filtered) . "\n";
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private static function buildOperationBlock(string $tag, array $operation, ?string $defaultParam): string
    {
        $method     = ucfirst(strtolower((string) ($operation['httpMethod'] ?? 'get')));
        $path       = (string) ($operation['path'] ?? '/api/resource');
        $summary    = (string) ($operation['summary'] ?? 'Endpoint');
        $name       = (string) ($operation['name'] ?? strtolower($method));
        $responses  = $operation['responses'] ?? [];
        $hasBody    = (bool) ($operation['requestBody'] ?? false);
        $hasParam   = (bool) ($operation['pathParam'] ?? false);
        $paramName  = $defaultParam ?? 'id';

        $entries = [];
        $entries[] = "path=\"{$path}\"";
        $entries[] = "summary=\"{$summary}\"";
        $entries[] = "tags={\"{$tag}\"}";

        if ($hasParam) {
            $entries[] = "@OA\\Parameter(\n        name=\"{$paramName}\",\n        in=\"path\",\n        required=true,\n        @OA\\Schema(type=\"integer\")\n    )";
        }

        if ($hasBody) {
            $entries[] = '@OA\RequestBody(required=true, @OA\JsonContent())';
        }

        foreach ($responses as $response) {
            $code = $response['code'] ?? 200;
            $desc = $response['description'] ?? 'Response';
            $entries[] = "@OA\\Response(response={$code}, description=\"{$desc}\")";
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
}
