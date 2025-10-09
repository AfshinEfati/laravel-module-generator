# Swagger Documentation Usage Guide

## Overview

This guide explains how to use the `--swagger` flag to generate OpenAPI/Swagger documentation for your Laravel modules.

## Basic Usage

### Generate Only Swagger Documentation

When you use `--swagger` flag alone, the package will generate **only** the Swagger documentation file without creating any other module files:

```bash
php artisan make:module City --swagger
```

**Output:**
- ✅ Creates: `app/Docs/CityDoc.php` with OpenAPI annotations
- ❌ Skips: Repository, Service, DTO, Controller, Provider, Tests, etc.

### Generate Full Module with Swagger

To generate a complete module with Swagger documentation, combine `--swagger` with other flags:

```bash
# Generate API module with Swagger documentation
php artisan make:module City --api --swagger

# Generate full module with all components and Swagger
php artisan make:module City --api --swagger --requests --tests
```

## Generated Swagger Features

The generated Swagger documentation includes:

### 1. Proper JSON Content Types

All responses include `@OA\JsonContent()` annotations with proper content-type headers:

```php
/**
 * @OA\Get(
 *     path="/api/cities",
 *     summary="List City",
 *     tags={"City"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent()
 *     )
 * )
 */
```

### 2. Authentication Error Handling (401)

When authentication middleware is configured, all endpoints include 401 responses:

```php
/**
 * @OA\Response(
 *     response=401,
 *     description="Unauthenticated",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Unauthenticated.")
 *     )
 * )
 */
```

### 3. Structured Error Responses

- **404 Not Found:**
```php
@OA\Response(
    response=404,
    description="Not found",
    @OA\JsonContent(
        @OA\Property(property="message", type="string", example="Resource not found.")
    )
)
```

- **422 Validation Error:**
```php
@OA\Response(
    response=422,
    description="Validation error",
    @OA\JsonContent(
        @OA\Property(property="message", type="string", example="The given data was invalid.")
    )
)
```

### 4. Security Schemes

Configure authentication schemes in `config/module-generator.php`:

```php
'swagger' => [
    'security' => [
        'auth_middleware' => ['auth', 'auth:api', 'auth:sanctum'],
        'default' => 'bearerAuth',
        'schemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearer_format' => 'JWT',
                'description' => 'Pass a valid bearer token retrieved from the authentication endpoint.'
            ],
        ],
    ],
],
```

## Ensuring Proper JSON Responses in Laravel

The Swagger documentation describes JSON responses, but you need to configure Laravel to actually return JSON for errors:

### 1. Use API Middleware

Ensure your API routes use the `api` middleware group:

```php
// routes/api.php
Route::middleware(['api'])->group(function () {
    Route::apiResource('cities', CityController::class);
});
```

### 2. Configure Authentication Middleware

Update `app/Http/Middleware/Authenticate.php` to return JSON for API requests:

```php
protected function redirectTo(Request $request): ?string
{
    // Return null for JSON requests (triggers 401 JSON response)
    // Return login route for web requests
    return $request->expectsJson() ? null : route('login');
}
```

### 3. Exception Handler (Optional)

For Laravel 11+, ensure your exception handler returns JSON for API requests:

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Resource not found.'
            ], 404);
        }
    });
})
```

## Configuration

### Controller Middleware

To automatically add authentication to generated Swagger docs, configure controller middleware:

```php
// config/module-generator.php
'defaults' => [
    'controller_middleware' => ['auth:sanctum'],
],
```

This will:
- Add `auth:sanctum` middleware to generated controllers
- Automatically include 401 responses in Swagger documentation
- Add security requirements to all endpoints

### Custom Swagger Path

Change where Swagger documentation files are generated:

```php
// config/module-generator.php
'paths' => [
    'docs' => 'Docs', // Default: app/Docs
    // Or use a custom path:
    // 'docs' => 'Http/Documentation',
],
```

## Example Output

Running `php artisan make:module City --swagger` generates:

```php
<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="City")
 */
class CityDoc
{
    /**
     * @OA\Get(
     *     path="/api/cities",
     *     summary="List City",
     *     tags={"City"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(): void
    {
    }

    // ... other CRUD methods (store, show, update, destroy)
}
```

## Troubleshooting

### Issue: HTML 404 Instead of JSON

**Problem:** API returns HTML 404 page instead of JSON error.

**Solution:** 
1. Ensure routes use `api` middleware
2. Check `Authenticate` middleware returns `null` for JSON requests
3. Verify `Accept: application/json` header is sent in requests

### Issue: Missing Swagger Package Warning

**Problem:** Warning about missing swagger-php package.

**Solution:** Install one of these packages:
```bash
composer require darkaonline/l5-swagger
# OR
composer require zircote/swagger-php
```

### Issue: 401 Responses Not Documented

**Problem:** Swagger docs don't show 401 responses.

**Solution:** Configure controller middleware in config:
```php
'defaults' => [
    'controller_middleware' => ['auth:sanctum'],
],
```

## Integration with L5-Swagger

After generating Swagger documentation, regenerate your API documentation:

```bash
php artisan l5-swagger:generate
```

Then access your API documentation at:
```
http://your-app.test/api/documentation
```

## Best Practices

1. **Generate Swagger First:** Create swagger docs before implementing controllers to establish API contract
2. **Use Middleware Config:** Configure authentication middleware in config file for consistency
3. **Version Your APIs:** Use controller subfolders for API versioning: `--controller=V1`
4. **Keep Docs Updated:** Regenerate swagger when adding new fields or changing endpoints
5. **Test JSON Responses:** Always test that your API returns proper JSON errors, not HTML

## Summary

The `--swagger` flag now provides two modes:

- **Standalone Mode:** `--swagger` alone = Only documentation
- **Full Module Mode:** `--swagger` + other flags = Complete module with documentation

Both modes generate proper JSON response annotations with authentication handling and structured error responses.
