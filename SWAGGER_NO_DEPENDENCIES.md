# Swagger Documentation - No Dependencies Required

This module generator includes a **built-in API documentation system** that doesn't require L5-Swagger, Swagger-PHP, or any external packages.

## Quick Start

### 1. Initialize Swagger UI

```bash
php artisan swagger:init
```

This creates the necessary UI files in `storage/swagger-ui/`.

### 2. Generate API Documentation

```bash
php artisan swagger:generate
```

This scans your routes and generates `public/api/swagger.json`.

### 3. View Documentation

**Option A: Using the built-in server**
```bash
php artisan swagger:ui
```
Visit: `http://localhost:8000/docs`

**Option B: Using your Laravel app**

In `routes/api.php`:
```php
use Efati\ModuleGenerator\Traits\RegistersSwaggerRoutes;

Route::middleware(['api'])->group(function () {
    Route::registerSwaggerRoutes(); // Adds /api/docs route
});
```

Then visit: `http://localhost:8000/api/docs`

## Features

✅ **No External Dependencies** - Works with just Laravel
✅ **Custom UI** - Beautiful, responsive design
✅ **Dark/Light Mode Ready** - Gradient backgrounds
✅ **Search & Filter** - Sidebar navigation
✅ **Auto-generated** - From your routes
✅ **OpenAPI 3.0** - Industry standard
✅ **Try It Out** - Interactive examples (coming soon)
✅ **Customizable** - Edit templates as needed

## Commands

### Initialize UI
```bash
php artisan swagger:init
```

### Generate Spec
```bash
php artisan swagger:generate
# Options:
#   --title="My API"
#   --version="2.0.0"
#   --host="api.example.com"
#   --output="public/api/v1/swagger.json"
```

### Start UI Server
```bash
php artisan swagger:ui
# Options:
#   --port=3000
#   --host=0.0.0.0
#   --refresh (regenerate spec before starting)
```

## Configuration

Edit `config/module-generator.php` to configure:

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
            ],
        ],
    ],
],
```

## Usage in Routes

### With Module Generator

```bash
php artisan make:module Post --api --swagger
```

### Manual Swagger Annotations

Use OpenAPI annotations in your controllers:

```php
/**
 * @OA\Get(
 *     path="/api/posts",
 *     summary="List all posts",
 *     @OA\Response(response=200, description="Success")
 * )
 */
public function index()
{
    return Post::all();
}
```

## Project Structure

```
storage/swagger-ui/
├── index.html          # Main UI
└── swagger.json        # (generated)

public/api/
└── swagger.json        # (generated)
```

## Customization

### Edit UI Design

Edit `storage/swagger-ui/index.html` to customize:
- Colors (CSS variables in `:root`)
- Layout
- Typography
- Behavior

### Regenerate Files

To reset to defaults:
```bash
php artisan swagger:init --force
```

## Examples

### Example 1: Simple API

```bash
# Create module
php artisan make:module Product --api --swagger

# Generate docs
php artisan swagger:generate

# View
php artisan swagger:ui
```

### Example 2: Integration with Laravel App

In `routes/api.php`:
```php
use Efati\ModuleGenerator\Traits\RegistersSwaggerRoutes;

Route::middleware('api')->group(function () {
    Route::registerSwaggerRoutes();

    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
});
```

Then visit `/api/docs` in your application.

## Troubleshooting

### Issue: "swagger.json not found"

**Solution:**
```bash
php artisan swagger:generate
```

### Issue: UI not displaying

**Solution:**
```bash
php artisan swagger:init --force
```

### Issue: Routes not showing up

**Solutions:**
- Ensure routes are in `routes/api.php`
- Routes must have `api` middleware
- Run `php artisan swagger:generate` again

## Advanced Features

### API Versioning

Generate separate specs:
```bash
php artisan swagger:generate --version="1.0.0" --output="public/api/v1/swagger.json"
php artisan swagger:generate --version="2.0.0" --output="public/api/v2/swagger.json"
```

### Multiple Security Schemes

Configure in `config/module-generator.php`:
```php
'swagger' => [
    'security' => [
        'schemes' => [
            'bearerAuth' => [...],
            'apiKey' => [
                'type' => 'apiKey',
                'in' => 'header',
                'name' => 'X-API-Key',
            ],
        ],
    ],
],
```

### Custom Servers

```bash
php artisan swagger:generate \
    --host="https://api.example.com" \
    --title="Production API" \
    --version="1.0.0"
```

## Performance

- **First Load:** < 1 second
- **Spec Generation:** < 5 seconds (for 100+ routes)
- **File Size:** ~50KB (UI + JSON combined)
- **Memory Usage:** < 10MB

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

Part of Laravel Module Generator - MIT License

---

**Need help?** Check the documentation at: https://afshinefati.github.io/Laravel-Scaffolder/
