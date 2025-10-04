# Module anatomy (Product sample)

[🇮🇷 فارسی](../fa/module-anatomy.md){ .language-switcher }

Understand exactly what the generator creates by walking through a full Product module. The examples below assume Laravel 10/11 with the package installed and the defaults left intact.

## Scenario

- **Goal:** expose CRUD endpoints for products with DTO/resource symmetry and feature tests.
- **Schema:** name, SKU, price, boolean status, and a release date in the Jalali calendar.

```bash
php artisan make:module Product \
  --api --requests --resource --dto --tests \
  --fields="name:string:unique, sku:string:unique, price:decimal(10,2), is_active:boolean, released_at:date"
```

## Generated files

| Path | Highlights |
| --- | --- |
| `app/Repositories/Contracts/ProductRepositoryInterface.php` | Declares `find`, `findDynamic`, and `getByDynamic` so consumers can opt into dynamic filters without touching the concrete class. |
| `app/Repositories/Eloquent/ProductRepository.php` | Extends the published base repository and inherits the dynamic query helpers for reuse in custom methods. |
| `app/Services/Contracts/ProductServiceInterface.php` | Service contract that mirrors the repository API and advertises collection lookups. |
| `app/Services/ProductService.php` | Wraps repository access, normalises DTO payloads, and re-exports `findDynamic`/`getByDynamic`. |
| `app/Http/Controllers/Api/V1/ProductController.php` | REST controller that returns JSON envelopes via `ApiResponseHelper`. |
| `app/Http/Resources/ProductResource.php` | Applies `ApiResponseHelper::formatDates`/`getStatus` so booleans and dates are consistent across the API. |
| `app/DTOs/ProductDTO.php` | Lightweight data object with `fromRequest()` and `toArray()` helpers for the service layer. |
| `app/Http/Requests/StoreProductRequest.php` & `UpdateProductRequest.php` | Validation rules derived from the schema (and auto-adjusted for update flows). |
| `tests/Feature/Products/ProductCrudTest.php` | End-to-end coverage for index, store, validation errors, show, update, and destroy. |

> The exact namespaces depend on `config/module-generator.php`. The examples below assume the defaults.

## Repository & service layer

The repository inherits the reusable query helpers. You can still add bespoke methods when you need to, but most dynamic filters are covered out of the box.

```php
namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }
}
```

```php
namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\ProductServiceInterface;

class ProductService extends BaseService implements ProductServiceInterface
{
    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
```

With the base classes in place you can query products dynamically:

```php
$products = $productService->getByDynamic(
    where: ['is_active', true],
    with: ['category'],
    whereBetween: [['price', [100_000, 750_000]]],
);

$latest = $productService->findDynamic(
    where: ['sku', 'new-product'],
    orWhereNull: [['released_at']],
);
```

Both calls reuse the same helper under the hood, so you keep fluent access without hand-writing queries in each module.

## Controller & API responses

Controllers keep the payloads slim by leaning on `ApiResponseHelper`. The helper wraps responses in a predictable envelope and automatically formats Jalali-aware dates and booleans.

```php
public function store(StoreProductRequest $request)
{
    $payload = ProductDTO::fromRequest($request);
    $product = $this->service->store($payload);

    return ApiResponseHelper::successResponse(
        new ProductResource($product),
        'created',
        201
    );
}
```

Example response:

```json
{
  "success": true,
  "message": "created",
  "data": {
    "name": "Toner Cartridge",
    "sku": "CARTRIDGE-XL",
    "price": 1899000,
    "is_active": {
      "name": "active",
      "fa_name": "فعال",
      "code": 1
    },
    "released_at": {
      "date": "2025-08-15",
      "time": "10:30:00",
      "fa_date": "۱۴۰۴-۰۵-۲۴",
      "iso": "2025-08-15T10:30:00+04:30"
    }
  }
}
```

## Requests & DTOs

```php
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'sku' => 'required|string|max:255|unique:products,sku',
            'price' => 'required|numeric',
            'is_active' => 'sometimes|boolean',
            'released_at' => 'sometimes|date',
        ];
    }
}
```

```php
$dto = ProductDTO::fromRequest($request);

// Later inside the service…
$product = $this->repository->store($dto->toArray());
```

DTOs only include fields that were marked as fillable, making it safe to pass straight through to `BaseService::store()` and `BaseService::update()`.

## Feature tests

The generated test exercises both success and failure paths so you can trust the scaffolding before moving on to business logic.

```php
$this->postJson('/api/v1/products', [
    'name' => 'Laptop Stand',
    'sku' => 'STAND-01',
    'price' => 990000,
    'released_at' => '1404-01-20',
])->assertCreated();

$this->postJson('/api/v1/products', [
    'name' => '',
    'sku' => 'STAND-01',
])->assertStatus(422);
```

Repeatable tests are a great safety net after customising the stubs or base classes.

## Where to go next

- Explore the [usage guide](usage.md) for additional command combinations.
- Customise stubs as needed via the [advanced guides](advanced.md).
- Dive deeper into date handling with the [Goli cookbook](goli-guide.md).
