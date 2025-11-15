# Laravel Module Generator - Complete Features Guide

## 📦 Core Features

### 1. **Repository Pattern**

- Auto-generated repository interfaces and implementations
- Eloquent query builder integration
- Support for complex queries with relationships
- Dynamic method forwarding to services

```php
// Generated Repository Interface
interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function store(mixed $payload): Product;
    public function update(int|string $id, mixed $payload): bool;
}

// Usage
$products = $this->repository->findDynamic(
    where: ['status' => 'active'],
    with: ['category'],
    whereIn: ['price' => [100, 500]],
    limit: 20
);
```

### 2. **Service Layer Architecture**

- Separates business logic from controllers
- Dependency injection ready
- Chainable query methods
- Exception handling and logging

```php
// Generated Service
class ProductService implements ProductServiceInterface
{
    public function store(mixed $payload): Product { }
    public function update(int|string $id, mixed $payload): bool { }
    public function findDynamic(
        array $where = [],
        array $with = [],
        // ... more filters
    ): iterable { }
}
```

### 3. **Data Transfer Objects (DTOs)**

- Type-safe payload handling
- Request validation helper methods
- Automatic array conversion
- Field-by-field initialization

```php
// Generated DTO
class ProductDTO
{
    public mixed $name;
    public mixed $price;
    public mixed $description;

    public static function fromRequest(Request $request): self
    {
        $dto = new self();
        $dto->name = $request->input('name');
        // ... populate fields
        return $dto;
    }

    public function toArray(): array
    {
        // ... return non-null fields
    }
}
```

### 4. **API Controllers**

- RESTful endpoint handlers
- Form request validation
- Resource transformation
- Middleware support

```php
// Generated API Controller
class ProductController extends Controller
{
    public function index()
    {
        $data = $this->service->index();
        return ProductResource::collection($data);
    }

    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromRequest($request);
        return new ProductResource($this->service->store($dto));
    }
}
```

### 5. **API Resources**

- JSON response formatting
- Relationship handling
- Data transformation
- Consistent API contracts

```php
// Generated Resource
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            // ... formatted fields
        ];
    }
}
```

### 6. **Form Requests**

- Auto-generated validation rules
- Store and Update variants
- Unique rule handling
- Foreign key constraints

```php
// Generated Store Request
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:products',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }
}
```

### 7. **Feature Tests**

- CRUD operation coverage
- Success and failure scenarios
- Field metadata based payloads
- Validation error assertions
- Relationship testing

```bash
# Tests include
✓ Index endpoint returns paginated data
✓ Show endpoint returns single resource
✓ Store creates new record with valid data
✓ Store rejects invalid data
✓ Update modifies existing record
✓ Delete removes record
✓ Validation errors are properly formatted
```

### 8. **Action Layer**

- Invokable classes for operations
- Business logic encapsulation
- Logging and error handling
- Clean separation of concerns

```php
// Generated Action
class CreateProductAction extends BaseAction
{
    public function __construct(
        private readonly ProductService $service,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);
    }

    protected function handle(mixed ...$arguments): Product
    {
        $payload = $arguments[0] ?? null;
        $this->logger->info('Creating product...');
        return $this->service->store($payload);
    }
}
```

### 9. **Service Providers**

- Automatic bindings registration
- Interface to implementation mapping
- Auto-registered in framework
- Customizable binding logic

```php
// Generated Provider
class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
        $this->app->bind(
            ProductServiceInterface::class,
            ProductService::class
        );
    }
}
```

### 10. **OpenAPI/Swagger Documentation**

- Automatic endpoint documentation
- Request/response schemas
- Authentication schemes
- Example payloads
- Error responses (401, 404, 422)

```php
// Generated Swagger Doc
class ProductDoc
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List products",
     *     tags={"Product"},
     *     @OA\Response(response=200, description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(): void { }
}
```

## 🎯 Schema Inference Features

### Multiple Source Support

1. **Migration Parsing** - Extract from migration files
2. **Model Inspection** - Read from Eloquent model
3. **Inline Definition** - Specify directly via `--fields`
4. **Runtime Detection** - Query database schema

### Supported Field Types

- **Primitives**: string, integer, boolean, float, numeric, text, json
- **Dates**: date, datetime, timestamp
- **Special**: uuid, email, url, enum, array
- **Relations**: foreign keys, many-to-many

### Field Modifiers

- `nullable` - Allow null values
- `unique` - Add unique constraint
- `default=value` - Set default value
- `fk=table.column` - Foreign key reference

## 🔧 Configuration Options

### Available Defaults

```php
'defaults' => [
    'controller_type' => 'api',           // 'api' or 'web'
    'with_form_requests' => true,         // Auto form requests
    'with_actions' => true,               // Auto actions
    'with_tests' => true,                 // Auto tests
    'with_controller' => true,            // Auto controller
    'with_resource' => true,              // Auto resource
    'with_dto' => true,                   // Auto DTO
    'with_provider' => true,              // Auto provider
    'with_swagger' => false,              // Auto swagger docs
    'controller_middleware' => [],        // Default middleware
    'logging_channel' => null,            // Logging configuration
]
```

### Path Customization

```php
'paths' => [
    'controller' => 'Http/Controllers/Api/V1',
    'repository' => ['contracts' => '...', 'eloquent' => '...'],
    'service' => ['contracts' => '...', 'concretes' => '...'],
    'dto' => 'DTOs',
    'actions' => 'Actions',
    'resource' => 'Http/Resources',
    'form_request' => 'Http/Requests',
    'tests' => ['feature' => 'tests/Feature'],
    'docs' => 'Docs',
]
```

## 🚀 Advanced Capabilities

### Dynamic Query Building

Services support dynamic, chainable query methods:

```php
$results = $service->findDynamic(
    where: ['status' => 'active'],              // Simple equality
    with: ['category', 'tags'],                 // Eager loading
    whereIn: ['category_id' => [1, 2, 3]],     // IN operator
    whereNot: ['archived' => true],             // NOT equal
    whereBetween: ['price' => [100, 500]],     // Range queries
    whereNull: ['deleted_at'],                  // NULL checks
    orWhere: ['status' => 'featured'],          // OR conditions
    orderBy: 'created_at',                      // Sorting
    limit: 20,                                  // Pagination
    offset: 40
);
```

### Error Handling & Exceptions

- Graceful fallbacks for missing resources
- Detailed error messages for debugging
- Exception handling in services and actions
- Database constraint violation handling

### Jalali Date Support

- Built-in Persian calendar helpers
- Automatic date formatting
- Carbon integration
- Multi-timezone support

```php
$jalali = goli(now())->toGoliDateString();  // 1403-07-31
$gregorian = Goli::parseGoli('1403-01-01');
```

## 📊 File Structure Generated

```
App/
├── Http/
│   ├── Controllers/Api/V1/
│   │   └── ProductController.php
│   ├── Requests/Product/
│   │   ├── StoreProductRequest.php
│   │   └── UpdateProductRequest.php
│   └── Resources/
│       └── ProductResource.php
├── Services/
│   ├── Contracts/
│   │   └── ProductServiceInterface.php
│   └── ProductService.php
├── Repositories/
│   ├── Contracts/
│   │   └── ProductRepositoryInterface.php
│   └── Eloquent/
│       └── ProductRepository.php
├── DTOs/
│   └── ProductDTO.php
├── Actions/Product/
│   ├── ListProductAction.php
│   ├── ShowProductAction.php
│   ├── CreateProductAction.php
│   ├── UpdateProductAction.php
│   ├── DeleteProductAction.php
│   └── ListWithRelationsProductAction.php
├── Docs/
│   └── ProductDoc.php
└── Providers/
    └── ProductServiceProvider.php

tests/
└── Feature/
    └── ProductCrudTest.php
```

## 🔐 Security Features

- Form request validation
- Middleware support on controllers
- Authentication middleware integration
- CORS and API resource protection
- Swagger security schemes

## 🧪 Testing Capabilities

- Auto-generated feature tests
- Field-aware test data generation
- Success/failure scenario coverage
- Relationship testing
- Validation error checking

## 📚 Documentation Generation

- OpenAPI 3.0 compliant
- Automatic endpoint discovery
- Request/response schema generation
- Live Swagger UI integration
- Multiple server support

---

All features work together seamlessly to provide a complete module generation system that follows Laravel best practices and conventions.
