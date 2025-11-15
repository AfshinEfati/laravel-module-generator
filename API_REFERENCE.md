# API Reference - Laravel Module Generator

This document provides comprehensive API reference for the Laravel Module Generator package.

## Command Signature

```bash
php artisan make:module {name} [options]
```

## Parameters

### name (Required)

The model/module name in StudlyCase format.

```bash
php artisan make:module Product          # Generates Product module
php artisan make:module OrderItem        # Generates OrderItem module
php artisan make:module BlogPost         # Generates BlogPost module
```

The name is used for:

- Model class: `App\Models\{name}`
- Service class: `App\Services\{name}Service`
- Repository class: `App\Repositories\Eloquent\{name}Repository`
- DTO class: `App\DTOs\{name}DTO`
- Resource class: `App\Http\Resources\{name}Resource`
- Controller class: `App\Http\Controllers\...{name}Controller`

## Options

### Stack Generation Options

#### `-a, --api`

Generate API-style controller with form requests and actions.

```bash
php artisan make:module Product -a
```

Enables:

- API controller (not web)
- Form request generation
- Action classes
- API resource

#### `--actions`

Generate invokable action classes for CRUD operations.

```bash
php artisan make:module Product --actions
```

Generates actions:

- `App\Actions\Product\ListProductAction`
- `App\Actions\Product\ShowProductAction`
- `App\Actions\Product\CreateProductAction`
- `App\Actions\Product\UpdateProductAction`
- `App\Actions\Product\DeleteProductAction`
- `App\Actions\Product\ListWithRelationsProductAction`

#### `--no-actions`

Skip action layer generation.

```bash
php artisan make:module Product --no-actions
```

#### `-r, --requests`

Generate Store and Update form request classes.

```bash
php artisan make:module Product --requests
```

Generates:

- `App\Http\Requests\Product\StoreProductRequest`
- `App\Http\Requests\Product\UpdateProductRequest`

#### `-t, --tests`

Generate CRUD feature tests.

```bash
php artisan make:module Product --tests
```

Generates: `tests/Feature/ProductCrudTest.php`

#### `-nr, --no-resource`

Skip API resource generation.

```bash
php artisan make:module Product --no-resource
```

#### `-nd, --no-dto`

Skip DTO generation.

```bash
php artisan make:module Product --no-dto
```

#### `-nc, --no-controller`

Skip controller generation.

```bash
php artisan make:module Product --no-controller
```

#### `-np, --no-provider`

Skip service provider generation and registration.

```bash
php artisan make:module Product --no-provider
```

Note: You'll need to manually register the service provider if skipped.

#### `-nt, --no-test`

Skip feature test generation.

```bash
php artisan make:module Product --no-test
```

### Documentation Options

#### `-sg, --swagger`

Generate OpenAPI documentation annotations.

```bash
php artisan make:module Product --swagger
```

Generates: `App\Docs\ProductDoc`

When used alone, generates only Swagger docs without other files:

```bash
php artisan make:module Product --swagger
# Only generates ProductDoc.php
```

#### `--no-swagger`

Explicitly disable Swagger generation.

```bash
php artisan make:module Product --no-swagger
```

### File Organization Options

#### `-c, --controller=Subdir`

Place controller in a subdirectory.

```bash
php artisan make:module Product --controller=Admin
# Generates: App/Http/Controllers/Api/V1/Admin/ProductController.php

php artisan make:module Report --controller=Admin/Reports
# Generates: App/Http/Controllers/Api/V1/Admin/Reports/ReportController.php
```

### Schema Definition Options

#### `--fields=SCHEMA`

Define fields inline without migration.

```bash
php artisan make:module Product --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
```

**Syntax:** `name:type[:modifier[:modifier...]]`

**Field Types:**

- `string` - VARCHAR
- `text` - TEXT
- `integer` - INT
- `boolean` - BOOLEAN
- `date` - DATE
- `datetime` - DATETIME
- `decimal(precision,scale)` - DECIMAL
- `json` - JSON
- `email` - VARCHAR (email validation)
- `uuid` - VARCHAR (UUID)
- `url` - VARCHAR (URL validation)

**Modifiers:**

- `nullable` - Allow NULL values
- `unique` - UNIQUE constraint
- `default=value` - Default value
- `fk=table.column` - Foreign key

**Examples:**

```bash
# Simple fields
--fields="name:string, description:text, price:decimal(10,2)"

# With modifiers
--fields="email:string:unique:nullable, phone:string:nullable"

# With foreign keys
--fields="category_id:integer:fk=categories.id, author_id:integer:fk=users.id"

# Mixed
--fields="title:string:unique, slug:string:unique, \
          published:boolean:default=false, author_id:integer:fk=users.id"
```

#### `-fm, --from-migration=MIGRATION`

Infer schema from migration file.

```bash
php artisan make:module Product --from-migration=create_products_table
```

Can be:

- Migration file name: `create_products_table`
- Migration filename: `2024_01_15_create_products_table`
- Full path: `database/migrations/2024_01_15_000000_create_products_table.php`

The generator will:

1. Find the migration file
2. Parse column definitions
3. Extract field types, nullability, defaults
4. Detect foreign keys and relationships
5. Use extracted metadata for generation

### Stack Control Options

#### `--all` / `--a` / `--full` / `--f`

Generate complete stack with all options enabled.

```bash
php artisan make:module Product --all
# OR
php artisan make:module Product --full
```

Enables:

- ✅ Controller (API)
- ✅ Form requests
- ✅ API resource
- ✅ DTO
- ✅ Provider
- ✅ Actions
- ✅ Tests
- ✅ Swagger docs

Equivalent to:

```bash
php artisan make:module Product -a --requests --tests --swagger
```

### Utility Options

#### `--force`

Overwrite existing files.

```bash
php artisan make:module Product --force
```

By default, existing files are skipped. Use `--force` to overwrite.

## Config File Reference

Located at: `config/module-generator.php`

### defaults

```php
'defaults' => [
    'controller_type' => 'api',              // 'api' or 'web'
    'with_form_requests' => true,
    'with_actions' => true,
    'with_tests' => true,
    'with_controller' => true,
    'with_resource' => true,
    'with_dto' => true,
    'with_provider' => true,
    'with_swagger' => false,
    'controller_middleware' => ['auth:sanctum'],
    'logging_channel' => null,
],
```

### paths

```php
'paths' => [
    'controller' => 'Http/Controllers/Api/V1',

    'repository' => [
        'contracts' => 'Repositories/Contracts',
        'eloquent' => 'Repositories/Eloquent',
    ],

    'service' => [
        'contracts' => 'Services/Contracts',
        'concretes' => 'Services',
    ],

    'dto' => 'DTOs',
    'actions' => 'Actions',
    'resource' => 'Http/Resources',
    'form_request' => 'Http/Requests',
    'docs' => 'Docs',

    'tests' => [
        'feature' => 'tests/Feature',
    ],
],
```

## Generated Class APIs

### Repository

```php
interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function store(mixed $payload): Product;
    public function update(int|string $id, mixed $payload): bool;
    public function getAll(): Collection;
    public function find(int|string $id): ?Product;
    public function delete(int|string $id): bool;
    public function getByDynamic(...$params): iterable;
}
```

**Usage:**

```php
$repository->store($data);                    // Create
$repository->find($id);                       // Single item
$repository->getAll();                        // All items
$repository->update($id, $data);              // Update
$repository->delete($id);                     // Delete
$repository->getByDynamic($where, $with);     // Dynamic query
```

### Service

```php
interface ProductServiceInterface extends BaseServiceInterface
{
    public function store(mixed $payload): Product;
    public function update(int|string $id, mixed $payload): bool;
    public function index(): mixed;
    public function show(int|string $id): mixed;
    public function destroy(int|string $id): bool;
    public function findDynamic(...$params): iterable;
}
```

**Usage:**

```php
$service->index();                     // List all
$service->show($id);                   // Single item
$service->store($payload);             // Create
$service->update($id, $payload);       // Update
$service->destroy($id);                // Delete
$service->findDynamic($where, $with);  // Complex query
```

### DTO

```php
class ProductDTO
{
    public mixed $name;
    public mixed $price;
    public mixed $description;

    public static function fromRequest(Request $request): self;
    public static function fromArray(array $data): self;
    public function toArray(): array;
}
```

**Usage:**

```php
$dto = ProductDTO::fromRequest($request);
$dto = ProductDTO::fromArray(['name' => '...', 'price' => 100]);
$array = $dto->toArray();
```

### Action

```php
class CreateProductAction extends BaseAction
{
    public function __invoke(mixed ...$arguments): Product;
    public function execute(mixed ...$arguments): Product;
    protected function handle(mixed ...$arguments): Product;
}
```

**Usage:**

```php
$action = app(CreateProductAction::class);
$product = $action($dto);
// OR
$product = app(CreateProductAction::class)($dto);
```

### Resource

```php
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array;
}
```

**Usage:**

```php
return new ProductResource($product);
return ProductResource::collection($products);
```

### Form Request

```php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool;
    public function rules(): array;
    public function validated(): array;
}
```

**Usage:**

```php
public function store(StoreProductRequest $request)
{
    $data = $request->validated();  // Pre-validated data
    // ...
}
```

## Error Handling

The generator handles these scenarios gracefully:

1. **Missing Model** - Falls back to inline schema or migration
2. **Missing Migration** - Uses model inspection or inline schema
3. **Missing Stub** - Tries alternate locations
4. **File Exists** - Shows warning, use `--force` to overwrite
5. **Invalid Schema** - Logs warning, skips invalid fields
6. **Database Error** - Returns empty schema, doesn't crash

## Examples

### Minimal API Module

```bash
php artisan make:module Product -a
```

### Complete E-Commerce

```bash
php artisan make:module Product -a --from-migration --tests --swagger
php artisan make:module Order -a --from-migration --tests --swagger
php artisan make:module Category -a --swagger
```

### CMS with Admin Panel

```bash
php artisan make:module Page -a --controller=Admin --tests --swagger
php artisan make:module BlogPost -a --controller=Admin/Blog --tests --swagger
php artisan make:module Comment -a --tests --swagger
```

### SaaS Multi-Tenant

```bash
php artisan make:module Tenant -a --swagger --tests
php artisan make:module Subscription -a --swagger --tests
php artisan make:module ApiKey -a --swagger --tests
```

---

For detailed examples, see [EXAMPLES.md](EXAMPLES.md).
For feature descriptions, see [FEATURES.md](FEATURES.md).
