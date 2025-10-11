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

    private static $securitySchemeGenerated = false;

    public function handle(): int
    {
        $pathFilter = $this->option('path');
        $controllerFilter = $this->option('controller');
        $force = $this->option('force');
        $outputDir = $this->option('output') ?: app_path('Docs');

        File::ensureDirectoryExists($outputDir);

        // Reset security scheme flag
        self::$securitySchemeGenerated = false;

        $this->info('🔍 Scanning Laravel routes...');

        $routes = $this->getFilteredRoutes($pathFilter, $controllerFilter);

        if (empty($routes)) {
            $this->warn('⚠️  No routes found matching the filters.');
            return self::FAILURE;
        }

        $this->info(sprintf('📋 Found %d routes to document.', count($routes)));

        // Generate main Info file first
        $this->generateMainInfoFile($outputDir, $force);

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
        $baseNamespace = config('module-generator.base_namespace', 'App');

        // Get allowed route files
        $allowedFiles = [
            base_path('routes/api.php'),
            base_path('routes/web.php'),
        ];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            $action = $route->getActionName();

            // Skip closure routes
            if ($action === 'Closure' || Str::contains($action, 'Closure')) {
                continue;
            }

            // Get route file path
            $routeAction = $route->getAction();
            $routeFile = $routeAction['file'] ?? null;

            // Only include routes from api.php and web.php
            if ($routeFile && !in_array($routeFile, $allowedFiles)) {
                continue;
            }

            // Skip vendor/package routes (Laravel, Sanctum, L5-Swagger, etc.)
            if (Str::contains($action, ['Laravel\\', 'Illuminate\\', 'Laravel\\Sanctum\\', 'L5Swagger\\', 'Darkaonline\\'])) {
                continue;
            }

            // Skip documentation routes
            if (Str::startsWith($uri, ['api/documentation', 'sanctum/', '_ignition'])) {
                continue;
            }

            // Only include routes from app controllers
            if (!Str::startsWith($action, $baseNamespace . '\\')) {
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
     * Generate main OpenAPI Info file
     */
    private function generateMainInfoFile(string $outputDir, bool $force): void
    {
        $filePath = $outputDir . DIRECTORY_SEPARATOR . 'OpenApiInfo.php';

        if (!$force && File::exists($filePath)) {
            return;
        }

        $baseNamespace = config('module-generator.base_namespace', 'App');
        $namespace = $baseNamespace . '\\Docs';
        $appName = config('app.name', 'Laravel API');
        $appVersion = '1.0.0';

        $content = <<<PHP
<?php

namespace {$namespace};

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="{$appName}",
 *     version="{$appVersion}",
 *     description="API Documentation",
 *     @OA\Contact(
 *         email="api@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="/",
 *     description="API Server"
 * )
 */
class OpenApiInfo
{
}

PHP;

        File::put($filePath, $content);
        $this->line('  ✓ Generated: OpenApiInfo.php');
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
            $requestProperties = $this->extractRequestProperties($route);
            $lines[] = '     *     @OA\RequestBody(';
            $lines[] = '     *         required=true,';
            if (!empty($requestProperties)) {
                $lines[] = '     *         @OA\JsonContent(';
                $lines[] = '     *             required={' . implode(',', array_map(fn($p) => '"' . $p . '"', array_keys($requestProperties))) . '},';
                $propCount = count($requestProperties);
                $index = 0;
                foreach ($requestProperties as $propName => $propType) {
                    $index++;
                    $comma = $index < $propCount ? ',' : '';
                    $lines[] = sprintf('     *             @OA\Property(property="%s", type="%s")%s', $propName, $propType, $comma);
                }
                $lines[] = '     *         )';
            } else {
                $lines[] = '     *         @OA\JsonContent(';
                $lines[] = '     *             @OA\Property(property="example", type="string", description="Add your request fields here")';
                $lines[] = '     *         )';
            }
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
        // Create unique method name from HTTP method + URI
        $uriForMethod = str_replace(['/', '{', '}', '-', '.'], '_', $uri);
        $methodNameSafe = $httpMethod . '_' . $uriForMethod;
        $lines[] = sprintf('    public function %s(){}', Str::camel($methodNameSafe));

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
     * Extract request properties from controller action
     */
    private function extractRequestProperties(array $route): array
    {
        $action = $route['action'];
        
        // Try to find FormRequest class
        if (!Str::contains($action, '@')) {
            return [];
        }

        [$controllerClass, $method] = explode('@', $action);

        if (!class_exists($controllerClass)) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($controllerClass);
            if (!$reflection->hasMethod($method)) {
                return [];
            }

            $methodReflection = $reflection->getMethod($method);
            $parameters = $methodReflection->getParameters();

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                if (!$type || $type->isBuiltin()) {
                    continue;
                }

                $typeName = $type->getName();
                
                // Check if it's a FormRequest
                if (class_exists($typeName) && is_subclass_of($typeName, 'Illuminate\\Foundation\\Http\\FormRequest')) {
                    return $this->extractPropertiesFromFormRequest($typeName);
                }
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return [];
    }

    /**
     * Extract properties from FormRequest rules
     */
    private function extractPropertiesFromFormRequest(string $formRequestClass): array
    {
        try {
            $formRequest = new $formRequestClass();
            
            if (!method_exists($formRequest, 'rules')) {
                return [];
            }

            $rules = $formRequest->rules();
            $properties = [];

            foreach ($rules as $field => $rule) {
                $type = $this->inferTypeFromRule($rule);
                $properties[$field] = $type;
            }

            return $properties;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Infer property type from validation rule
     */
    private function inferTypeFromRule($rule): string
    {
        if (is_array($rule)) {
            $rule = implode('|', $rule);
        }

        $rule = strtolower((string) $rule);

        if (str_contains($rule, 'integer') || str_contains($rule, 'numeric')) {
            return 'integer';
        }

        if (str_contains($rule, 'boolean') || str_contains($rule, 'bool')) {
            return 'boolean';
        }

        if (str_contains($rule, 'array')) {
            return 'array';
        }

        if (str_contains($rule, 'email')) {
            return 'string';
        }

        if (str_contains($rule, 'date')) {
            return 'string';
        }

        return 'string';
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

        // Add security schemes (only once across all files)
        if (!empty($securitySchemes) && !self::$securitySchemeGenerated) {
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
                $lines[] = '    public function ' . Str::camel($name) . 'Security(){}';
                $lines[] = '';
            }
            self::$securitySchemeGenerated = true;
        }

        // Add operations
        $lines[] = $operations;

        $lines[] = '}';
        $lines[] = '';

        return implode("\n", $lines);
    }
}
