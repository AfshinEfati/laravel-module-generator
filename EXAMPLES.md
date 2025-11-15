# Laravel Module Generator - Usage Examples

This document provides practical examples of how to use the Laravel Module Generator in real-world scenarios.

## Basic Examples

### Example 1: Simple Product Module

```bash
# Generate basic product module
php artisan make:module Product -a

# This generates:
# - ProductRepository and ProductRepositoryInterface
# - ProductService and ProductServiceInterface
# - ProductDTO with fromRequest() helper
# - ProductController (API)
# - ProductResource for JSON responses
# - Store/Update form requests with validation
# - Feature tests for CRUD operations
# - ProductServiceProvider (auto-registered)
```

### Example 2: Blog Module with Tests and Swagger

```bash
# Complete blog module with all features
php artisan make:module Post -a --requests --tests --swagger

# Now register the routes
Route::apiResource('posts', PostController::class);

# Access Swagger documentation
# http://yourapp.test/api/documentation
```

### Example 3: Admin Controller in Subfolder

```bash
# Place controller in an admin subfolder
php artisan make:module Invoice -a --controller=Admin

# Generates controller at:
# App/Http/Controllers/Api/V1/Admin/InvoiceController.php
```

## Advanced Examples

### Example 4: Multi-Level Module Structure

```bash
# Generate modules for a complete feature set
php artisan make:module Order -a --requests --tests
php artisan make:module OrderItem -a --requests --tests
php artisan make:module OrderStatus -a --requests --tests

# Register routes
Route::apiResource('orders', OrderController::class);
Route::apiResource('orders.items', OrderItemController::class);
Route::apiResource('order-statuses', OrderStatusController::class);
```

### Example 5: Using Schema from Migration

```bash
# Create migration first
php artisan make:migration create_products_table --create=products

# Add columns to migration
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->decimal('price', 10, 2);
    $table->text('description')->nullable();
    $table->unsignedBigInteger('category_id');
    $table->foreign('category_id')->references('id')->on('categories');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

# Run migration
php artisan migrate

# Generate module with schema inference
php artisan make:module Product -a --from-migration=create_products_table --tests
```

### Example 6: Custom Field Definitions

```bash
php artisan make:module User \
  --fields="email:email:unique, \
            password:string, \
            first_name:string, \
            last_name:string, \
            phone:string:nullable, \
            date_of_birth:date:nullable, \
            is_admin:boolean:default=false, \
            role_id:integer:fk=roles.id, \
            is_active:boolean:default=true" \
  -a --requests --tests
```

This generates:

- DTO with email, password, first_name, last_name, phone, date_of_birth, is_admin, role_id, is_active
- Form requests with proper validation rules
- Feature tests with relevant test data
- Controller with dependency injection

### Example 7: Web Module (Not API)

```bash
# Generate web module instead of API
php artisan make:module Article --controller=Blog

# This generates:
# - Web controller at Http/Controllers/Blog/ArticleController.php
# - Views will need to be created manually
# - No API Resource generation
# - Web-style form requests
```

## Real-World Scenarios

### Scenario 1: E-Commerce Product Catalog

```bash
# Products
php artisan make:module Product -a --swagger \
  --fields="sku:string:unique, \
            name:string, \
            description:text:nullable, \
            price:decimal(10,2), \
            cost:decimal(10,2):nullable, \
            stock:integer, \
            category_id:integer:fk=categories.id, \
            is_featured:boolean, \
            is_active:boolean" \
  --tests

# Categories
php artisan make:module Category -a --swagger

# Orders
php artisan make:module Order -a --swagger \
  --fields="order_number:string:unique, \
            customer_id:integer:fk=customers.id, \
            total_amount:decimal(10,2), \
            status:string, \
            shipped_at:datetime:nullable" \
  --tests

# Register routes
Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('orders', OrderController::class);
```

### Scenario 2: CMS with Content Management

```bash
# Pages module
php artisan make:module Page -a \
  --fields="title:string:unique, \
            slug:string:unique, \
            content:text, \
            excerpt:string:nullable, \
            published_at:datetime:nullable, \
            is_published:boolean:default=false" \
  --tests --swagger

# Comments module
php artisan make:module Comment -a \
  --fields="content:text, \
            page_id:integer:fk=pages.id, \
            author_id:integer:fk=users.id, \
            is_approved:boolean:default=false" \
  --tests --swagger

# Tags module
php artisan make:module Tag -a \
  --fields="name:string:unique, \
            slug:string:unique, \
            description:text:nullable" \
  --tests --swagger
```

### Scenario 3: Multi-Tenant SaaS Application

```bash
# Tenant module
php artisan make:module Tenant -a \
  --fields="name:string:unique, \
            slug:string:unique, \
            domain:string:unique:nullable, \
            plan_id:integer:fk=plans.id, \
            is_active:boolean:default=true, \
            created_at:datetime" \
  --tests --swagger

# Subscription module
php artisan make:module Subscription -a \
  --fields="tenant_id:integer:fk=tenants.id, \
            plan_id:integer:fk=plans.id, \
            started_at:datetime, \
            expires_at:datetime:nullable, \
            is_active:boolean:default=true" \
  --tests --swagger

# API Usage module
php artisan make:module ApiKey -a \
  --fields="tenant_id:integer:fk=tenants.id, \
            name:string, \
            key:string:unique, \
            is_active:boolean:default=true, \
            last_used_at:datetime:nullable" \
  --tests --swagger
```

## Using Generated Code

### Using the Service in a Controller

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service
    ) {}

    public function index()
    {
        // List with dynamic query building
        $products = $this->service->findDynamic(
            where: ['is_active' => true],
            with: ['category', 'reviews'],
            orderBy: 'name',
            limit: 15
        );

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->service->store($request->validated());
        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->service->update($product->id, $request->validated());
        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $this->service->destroy($product->id);
        return response()->noContent();
    }
}
```

### Using Actions for Complex Operations

```php
// In your controller
public function index(ListProductAction $action)
{
    $products = $action(); // Calls ListProductAction::handle()
    return ProductResource::collection($products);
}

public function store(
    CreateProductAction $action,
    StoreProductRequest $request
)
{
    $product = $action(ProductDTO::fromRequest($request));
    return new ProductResource($product);
}
```

### Using DTOs for Type Safety

```php
// In your service
public function store(mixed $payload): Product
{
    // Convert to DTO if array
    if (is_array($payload)) {
        $payload = ProductDTO::fromArray($payload);
    }

    // Now safely access properties
    $data = [
        'name' => $payload->name,
        'price' => $payload->price,
        'description' => $payload->description,
    ];

    return $this->repository->store($data);
}
```

### Advanced Query Building

```php
// In your service or repository
$products = $this->repository->findDynamic(
    // Simple WHERE conditions
    where: [
        'is_active' => true,
        'is_featured' => true,
    ],

    // Eager load relationships
    with: ['category', 'reviews', 'images'],

    // IN queries
    whereIn: [
        'category_id' => [1, 2, 3],
        'status' => ['active', 'pending'],
    ],

    // NOT equal
    whereNot: [
        'deleted' => true,
    ],

    // NOT IN
    whereNotIn: [
        'category_id' => [99],
    ],

    // BETWEEN queries
    whereBetween: [
        'price' => [100, 1000],
        'rating' => [4, 5],
    ],

    // IS NULL
    whereNull: ['approved_at'],

    // IS NOT NULL
    whereNotNull: ['published_at'],

    // OR conditions
    orWhere: [
        'name' => 'Special Item',
        'is_featured' => true,
    ],

    // Sorting
    orderBy: 'created_at',

    // Pagination
    limit: 20,
    offset: 0,
);
```

## Testing Examples

### Feature Test Structure

Generated tests include:

```php
class ProductCrudTest extends TestCase
{
    // ✓ Can list products
    public function test_index_returns_products() {}

    // ✓ Can create product
    public function test_store_creates_product() {}

    // ✓ Store validates required fields
    public function test_store_validates_required_fields() {}

    // ✓ Can show single product
    public function test_show_returns_product() {}

    // ✓ Can update product
    public function test_update_modifies_product() {}

    // ✓ Update validates data
    public function test_update_validates_data() {}

    // ✓ Can delete product
    public function test_destroy_deletes_product() {}
}
```

## Tips and Tricks

### Tip 1: Regenerate with Force Flag

```bash
# Overwrite existing files
php artisan make:module Product -a --force
```

### Tip 2: Generate Only Swagger Docs

```bash
# Update docs without regenerating all files
php artisan make:module Product --swagger
```

### Tip 3: Combine Multiple Options

```bash
# Web + admin controller with custom paths
php artisan make:module Report --controller=Admin/Reports --actions
```

### Tip 4: Customize Configuration

Edit `config/module-generator.php` to change defaults for your project:

```php
'defaults' => [
    'controller_type' => 'api',
    'with_form_requests' => true,
    'with_actions' => true,
    'with_tests' => true,
    'controller_middleware' => ['auth:sanctum', 'throttle:60,1'],
],
```

### Tip 5: Use with Domain-Driven Design

```bash
# Generate modules for each domain
# Domain: Orders
php artisan make:module Order -a --controller=Domains/Orders
php artisan make:module OrderItem -a --controller=Domains/Orders

# Domain: Payments
php artisan make:module Payment -a --controller=Domains/Payments
php artisan make:module Invoice -a --controller=Domains/Payments

# Domain: Shipping
php artisan make:module Shipment -a --controller=Domains/Shipping
```

---

For more information, refer to the [main documentation](README.md) and [features guide](FEATURES.md).
