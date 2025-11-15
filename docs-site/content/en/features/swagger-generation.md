# OpenAPI/Swagger Generation

Generate interactive API documentation automatically with inline annotations and route scanning.

## Getting Started

First, install a Swagger package (optional but recommended):

```bash
composer require darkaonline/l5-swagger
# or
composer require zircote/swagger-php
```

Then generate your module with Swagger support:

```bash
php artisan make:module Product --api --swagger
```

This creates `App/Docs/ProductDoc.php` with OpenAPI 3.0 annotations.

## Model-Based Generation

The `--swagger` flag generates comprehensive OpenAPI documentation for your module:

```bash
php artisan make:module Product \
  --api --swagger \
  --fields="name:string, price:decimal(10,2), is_active:boolean"
```

Generated annotations include:

- **Paths** – All CRUD endpoints (GET, POST, PUT, DELETE)
- **Schemas** – Request/response models with properties
- **Parameters** – Query filters, pagination, includes
- **Security** – Bearer token authentication
- **Examples** – Sample requests and responses

## Route-Based Generation

Scan existing routes to auto-generate docs:

```bash
php artisan make:swagger
```

Generates separate doc files for each controller with:

- Method signatures from code analysis
- Parameter detection from form requests
- Response type hints from resources
- HTTP status codes

### Filtering Routes

```bash
# Only document api/v1/* routes
php artisan make:swagger --path=api/v1

# Only document specific controller namespace
php artisan make:swagger --controller=Api

# Force regeneration (overwrite existing docs)
php artisan make:swagger --force
```

## Viewing Documentation

If using `l5-swagger`:

```bash
php artisan l5-swagger:generate
```

Then visit: `http://yourapp.test/api/documentation`

## Custom Annotations

Extend generated docs in your action classes:

```php
/**
 * @OA\Post(
 *     path="/api/v1/products",
 *     summary="Create a new product",
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProductDTO")),
 *     @OA\Response(response=201, description="Product created")
 * )
 */
class CreateAction extends BaseAction
{
    // ...
}
```

## OpenAPI Spec Locations

- **Generated Docs:** `App/Docs/{Module}Doc.php`
- **Generated Config:** `config/swagger.php` (if published)
- **API Endpoint:** `/api/v1/*` (routes with OpenAPI annotations)
