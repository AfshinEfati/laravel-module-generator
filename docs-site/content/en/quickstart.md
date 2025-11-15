# Quickstart

Get up and running with your first generated module in under 5 minutes.

## Step 1: Install the Package

```bash
composer require efati/laravel-module-generator
```

The service provider auto-registers during console initialization. No extra publish commands required!

## Step 2: Define Your Schema (Choose One)

### Option A: Inline Fields

```bash
php artisan make:module Product \
  --fields="name:string:unique, price:decimal(10,2), stock:integer, is_active:boolean"
```

### Option B: From Migration

```bash
# If migration exists:
php artisan make:module Product --from-migration

# Or specify migration path:
php artisan make:module Product --from-migration=database/migrations/2024_01_15_create_products_table.php
```

## Step 3: Generate the Module

For a **complete API module**:

```bash
php artisan make:module Product \
  --api \
  --requests \
  --tests \
  --swagger \
  --fields="name:string:unique, price:decimal(10,2), stock:integer, is_active:boolean"
```

This generates:

✅ Repository + Interface
✅ Service + Interface
✅ DTO with validation
✅ API Controller
✅ Form Requests
✅ API Resource
✅ Action Layer (7 actions)
✅ Feature Tests
✅ Service Provider (auto-registered)
✅ OpenAPI Documentation

## Step 4: Register Routes

```php
// routes/api.php
Route::apiResource('products', ProductController::class);

// Or with custom prefix:
Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
});
```

## Step 5: Test It

```bash
# Run feature tests
php artisan test tests/Feature/ProductCrudTest.php

# View Swagger docs (if installed l5-swagger)
php artisan l5-swagger:generate
# Visit: http://yourapp.test/api/documentation
```

## Customize Generated Files (Optional)

Publish stubs for custom templates:

```bash
php artisan vendor:publish \
  --provider="Efati\ModuleGenerator\ModuleGeneratorServiceProvider" \
  --tag=module-generator-stubs
```

Edit files in `resources/stubs/module-generator/` then regenerate with `--force`:

```bash
php artisan make:module Product --api --force
```

## What's Generated?

```
App/
├── Models/Product.php
├── Services/ProductService.php
├── Services/ProductServiceInterface.php
├── Repositories/Eloquent/ProductRepository.php
├── Repositories/Contracts/ProductRepositoryInterface.php
├── DTOs/ProductDTO.php
├── Http/Controllers/Api/V1/ProductController.php
├── Http/Requests/Product/StoreProductRequest.php
├── Http/Requests/Product/UpdateProductRequest.php
├── Http/Resources/ProductResource.php
├── Actions/Product/
│   ├── CreateAction.php
│   ├── UpdateAction.php
│   └── ... (5 more actions)
├── Providers/ProductServiceProvider.php
└── Docs/ProductDoc.php

tests/
└── Feature/ProductCrudTest.php
```

## Next Steps

- Explore [generating modules](./features/generating-modules.md) for all available options
- Learn about [schema-aware features](./features/schema-aware-generation.md)
- Review [action layer patterns](./features/action-layer.md)
- Check the [API reference](./reference.md) for complete CLI options
