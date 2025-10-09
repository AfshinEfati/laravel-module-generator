<?php

namespace Efati\ModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class GenerateSwaggerCommand extends Command
{
    protected $signature = 'make:swagger
                            {--path= : Filter routes by path prefix (e.g., api, api/v1)}
                            {--controller= : Filter routes by controller namespace}
                            {--f|force : Overwrite existing swagger files}
                            {--output= : Output directory for swagger files (default: app/Docs)}';

    protected $description = 'Generate Swagger documentation by scanning Laravel routes';

    public function handle(): int
    {
        $pathFilter = $this->option('path');
        $controllerFilter = $this->option('controller');
        $force = $this->option('force');
        $outputDir = $this->option('output') ?: app_path('Docs');

        File::ensureDirectoryExists($outputDir);

        $this->info('🔍 Scanning Laravel routes...');

        $routes = $this->getFilteredRoutes($pathFilter, $controllerFilter);

        if (empty($routes)) {
            $this->warn('⚠️  No routes found matching the filters.');
            return self::FAILURE;
        }

        $this->info(sprintf('📋 Found %d routes to document.', count($routes)));

        $groupedRoutes = $this->groupRoutesByController($routes);

        $generatedFiles = 0;
        foreach ($groupedRoutes as $controllerName => $controllerRoutes) {
            $docClass = $this->generateSwaggerDoc($controllerName, $controllerRoutes, $outputDir, $force);
            if ($docClass) {
                $this->line(sprintf('  ✓ Generated: %s', $docClass));
                $generatedFiles++;
            }
        }

        $this->info(sprintf('✅ Successfully generated %d swagger documentation file(s).', $generatedFiles));

        return self::SUCCESS;
    }

    /**
     * Get filtered routes based on options
     */
    private function getFilteredRoutes(?string $pathFilter, ?string $controllerFilter): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            $action = $route->getActionName();

            // Skip closure routes
            if ($action === 'Closure' || Str::contains($action, 'Closure')) {
                continue;
            }

            // Apply path filter
            if ($pathFilter && !Str::startsWith($uri, trim($pathFilter, '/'))) {
                continue;
            }

            // Apply controller filter
            if ($controllerFilter && !Str::contains($action, $controllerFilter)) {
                continue;
            }

            $routes[] = [
                'uri' => $uri,
                'methods' => $route->methods(),
                'action' => $action,
                'name' => $route->getName(),
                'middleware' => $route->middleware(),
            ];
        }

        return $routes;
    }

    /**
     * Group routes by controller
     */
    private function groupRoutesByController(array $routes): array
    {
        $grouped = [];

        foreach ($routes as $route) {
            $action = $route['action'];
            
            // Extract controller class and method
            if (Str::contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
            } else {
                // Handle invokable controllers
                $controller = $action;
                $method = '__invoke';
            }

            $controllerName = class_basename($controller);
            
            if (!isset($grouped[$controllerName])) {
                $grouped[$controllerName] = [
                    'controller_class' => $controller,
                    'routes' => [],
                ];
            }

            $grouped[$controllerName]['routes'][] = array_merge($route, [
                'method' => $method,
            ]);
        }

        return $grouped;
    }

    /**
     * Generate swagger documentation for a controller
     */
    private function generateSwaggerDoc(string $controllerName, array $data, string $outputDir, bool $force): ?string
    {
        $controllerClass = $data['controller_class'];
        $routes = $data['routes'];

        // Create doc class name
        $docClassName = str_replace('Controller', 'Doc', $controllerName);
        if ($docClassName === $controllerName) {
            $docClassName .= 'Doc';
        }

        $filePath = $outputDir . DIRECTORY_SEPARATOR . $docClassName . '.php';

        if (!$force && File::exists($filePath)) {
            $this->line(sprintf('  - Skipped existing file: %s (use --force to overwrite)', $docClassName));
            return null;
        }

        // Extract tag from controller name
        $tag = str_replace(['Controller', 'Doc'], '', $controllerName);

        // Generate operations
        $operations = $this->generateOperations($routes, $tag);

        // Get security schemes
        $securitySchemes = $this->extractSecuritySchemes($routes);

        // Generate file content
        $content = $this->buildSwaggerFileContent($docClassName, $tag, $operations, $securitySchemes);

        File::put($filePath, $content);

        return $docClassName . '.php';
    }

    /**
     * Generate operation methods for routes
     */
    private function generateOperations(array $routes, string $tag): string
    {
        $operations = [];

        foreach ($routes as $route) {
            $operations[] = $this->buildOperationMethod($route, $tag);
        }

        return implode("\n\n", array_filter($operations));
    }

    /**
     * Build a single operation method
     */
    private function buildOperationMethod(array $route, string $tag): string
    {
        $uri = '/' . ltrim($route['uri'], '/');
        $methods = array_diff($route['methods'], ['HEAD']);
        $methodName = $route['method'] ?? 'index';
        $routeName = $route['name'] ?? Str::slug($uri);

        if (empty($methods)) {
            return '';
        }

        $httpMethod = strtolower($methods[0]);
        $httpMethodUpper = ucfirst($httpMethod);

        // Extract path parameters
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        $pathParams = $matches[1] ?? [];

        // Determine if request body is needed
        $hasBody = in_array(strtoupper($httpMethod), ['POST', 'PUT', 'PATCH']);

        // Build summary
        $summary = $this->generateSummary($methodName, $tag);

        // Build responses
        $responses = $this->generateResponses($httpMethod, $route['middleware']);

        // Check for authentication
        $requiresAuth = $this->requiresAuthentication($route['middleware']);

        $lines = [];
        $lines[] = '    /**';
        $lines[] = sprintf('     * @OA\%s(', $httpMethodUpper);
        $lines[] = sprintf('     *     path="%s",', $uri);
        $lines[] = sprintf('     *     summary="%s",', $summary);
        $lines[] = sprintf('     *     tags={"%s"},', $tag);

        // Add path parameters
        foreach ($pathParams as $param) {
            $paramType = $this->inferParameterType($param);
            $lines[] = '     *     @OA\Parameter(';
            $lines[] = sprintf('     *         name="%s",', $param);
            $lines[] = '     *         in="path",';
            $lines[] = '     *         required=true,';
            $lines[] = sprintf('     *         @OA\Schema(type="%s")', $paramType);
            $lines[] = '     *     ),';
        }

        // Add request body
        if ($hasBody) {
            $lines[] = '     *     @OA\RequestBody(';
            $lines[] = '     *         required=true,';
            $lines[] = '     *         @OA\JsonContent()';
            $lines[] = '     *     ),';
        }

        // Add responses
        foreach ($responses as $response) {
            $lines[] = '     *     @OA\Response(';
            $lines[] = sprintf('     *         response=%d,', $response['code']);
            $lines[] = sprintf('     *         description="%s",', $response['description']);
            if (isset($response['content'])) {
                $lines[] = '     *         @OA\JsonContent(';
                if (isset($response['example'])) {
                    $lines[] = sprintf('     *             @OA\Property(property="message", type="string", example="%s")', $response['example']);
                }
                $lines[] = '     *         )';
            } else {
                $lines[] = '     *         @OA\JsonContent()';
            }
            $lines[] = '     *     ),';
        }

        // Add security
        if ($requiresAuth) {
            $lines[] = '     *     security={{"bearerAuth":{}}}';
        }

        $lines[] = '     * )';
        $lines[] = '     */';
        $methodNameSafe = str_replace(['.', '-', '/'], '_', $methodName . '_' . $routeName);
        $lines[] = sprintf('    public function %s(): void', Str::camel($methodNameSafe));
        $lines[] = '    {';
        $lines[] = '    }';

        return implode("\n", $lines);
    }

    /**
     * Generate summary from method name
     */
    private function generateSummary(string $methodName, string $tag): string
    {
        $summaries = [
            'index' => "List {$tag}",
            'store' => "Create {$tag}",
            'show' => "Show {$tag}",
            'update' => "Update {$tag}",
            'destroy' => "Delete {$tag}",
            'edit' => "Edit {$tag}",
            'create' => "Create {$tag} form",
        ];

        return $summaries[$methodName] ?? Str::title(str_replace('_', ' ', $methodName));
    }

    /**
     * Generate responses based on HTTP method
     */
    private function generateResponses(string $httpMethod, array $middleware): array
    {
        $requiresAuth = $this->requiresAuthentication($middleware);

        $responses = [];

        switch (strtolower($httpMethod)) {
            case 'get':
                $responses[] = ['code' => 200, 'description' => 'Successful response'];
                if ($requiresAuth) {
                    $responses[] = ['code' => 401, 'description' => 'Unauthenticated', 'content' => true, 'example' => 'Unauthenticated.'];
                }
                $responses[] = ['code' => 404, 'description' => 'Not found', 'content' => true, 'example' => 'Resource not found.'];
                break;

            case 'post':
                $responses[] = ['code' => 201, 'description' => 'Created'];
                if ($requiresAuth) {
                    $responses[] = ['code' => 401, 'description' => 'Unauthenticated', 'content' => true, 'example' => 'Unauthenticated.'];
                }
                $responses[] = ['code' => 422, 'description' => 'Validation error', 'content' => true, 'example' => 'The given data was invalid.'];
                break;

            case 'put':
            case 'patch':
                $responses[] = ['code' => 200, 'description' => 'Updated'];
                if ($requiresAuth) {
                    $responses[] = ['code' => 401, 'description' => 'Unauthenticated', 'content' => true, 'example' => 'Unauthenticated.'];
                }
                $responses[] = ['code' => 404, 'description' => 'Not found', 'content' => true, 'example' => 'Resource not found.'];
                $responses[] = ['code' => 422, 'description' => 'Validation error', 'content' => true, 'example' => 'The given data was invalid.'];
                break;

            case 'delete':
                $responses[] = ['code' => 204, 'description' => 'Deleted'];
                if ($requiresAuth) {
                    $responses[] = ['code' => 401, 'description' => 'Unauthenticated', 'content' => true, 'example' => 'Unauthenticated.'];
                }
                $responses[] = ['code' => 404, 'description' => 'Not found', 'content' => true, 'example' => 'Resource not found.'];
                break;

            default:
                $responses[] = ['code' => 200, 'description' => 'Successful response'];
                if ($requiresAuth) {
                    $responses[] = ['code' => 401, 'description' => 'Unauthenticated', 'content' => true, 'example' => 'Unauthenticated.'];
                }
        }

        return $responses;
    }

    /**
     * Check if route requires authentication
     */
    private function requiresAuthentication(array $middleware): bool
    {
        $authMiddleware = ['auth', 'auth:api', 'auth:sanctum', 'auth:web'];

        foreach ($middleware as $mw) {
            if (in_array($mw, $authMiddleware)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Infer parameter type from name
     */
    private function inferParameterType(string $param): string
    {
        if (Str::endsWith($param, '_id') || $param === 'id') {
            return 'integer';
        }

        if (Str::contains($param, ['uuid', 'token'])) {
            return 'string';
        }

        return 'string';
    }

    /**
     * Extract security schemes from routes
     */
    private function extractSecuritySchemes(array $routes): array
    {
        $requiresAuth = false;

        foreach ($routes as $route) {
            if ($this->requiresAuthentication($route['middleware'])) {
                $requiresAuth = true;
                break;
            }
        }

        if (!$requiresAuth) {
            return [];
        }

        $securityConfig = (array) config('module-generator.swagger.security', []);
        $configuredSchemes = (array) ($securityConfig['schemes'] ?? []);
        $defaultScheme = (string) ($securityConfig['default'] ?? 'bearerAuth');

        if (!isset($configuredSchemes[$defaultScheme])) {
            return [];
        }

        return [$defaultScheme => $configuredSchemes[$defaultScheme]];
    }

    /**
     * Build swagger file content
     */
    private function buildSwaggerFileContent(string $className, string $tag, string $operations, array $securitySchemes): string
    {
        $baseNamespace = config('module-generator.base_namespace', 'App');
        $namespace = $baseNamespace . '\\Docs';

        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = "namespace {$namespace};";
        $lines[] = '';
        $lines[] = 'use OpenApi\Annotations as OA;';
        $lines[] = '';
        $lines[] = '/**';
        $lines[] = sprintf(' * @OA\Tag(name="%s")', $tag);
        $lines[] = ' */';
        $lines[] = "class {$className}";
        $lines[] = '{';

        // Add security schemes
        if (!empty($securitySchemes)) {
            foreach ($securitySchemes as $name => $scheme) {
                $lines[] = '    /**';
                $lines[] = '     * @OA\SecurityScheme(';
                $lines[] = sprintf('     *     securityScheme="%s",', $name);
                $lines[] = sprintf('     *     type="%s",', $scheme['type'] ?? 'http');
                if (isset($scheme['scheme'])) {
                    $lines[] = sprintf('     *     scheme="%s",', $scheme['scheme']);
                }
                if (isset($scheme['bearer_format'])) {
                    $lines[] = sprintf('     *     bearerFormat="%s",', $scheme['bearer_format']);
                }
                if (isset($scheme['description'])) {
                    $lines[] = sprintf('     *     description="%s"', $scheme['description']);
                }
                $lines[] = '     * )';
                $lines[] = '     */';
                $lines[] = '    public function ' . Str::camel($name) . 'Security(): void';
                $lines[] = '    {';
                $lines[] = '    }';
                $lines[] = '';
            }
        }

        // Add operations
        $lines[] = $operations;

        $lines[] = '}';
        $lines[] = '';

        return implode("\n", $lines);
    }
}
