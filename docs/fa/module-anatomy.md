# آناتومی ماژول (نمونهٔ Product)

<div dir="rtl" markdown="1">

[🇬🇧 English](../en/module-anatomy.md){ .language-switcher }

در این صفحه خروجی کامل دستور `make:module` را روی یک ماژول Product بررسی می‌کنیم تا بدانید هر فایل چه نقشی دارد و چطور می‌توانید آن را گسترش دهید.

## سناریو

- **هدف:** پیاده‌سازی CRUD برای محصول‌ها به همراه DTO، ریسورس و تست‌های فیچری.
- **اسکیما:** نام، کد SKU، قیمت، وضعیت فعال و تاریخ انتشار بر اساس تقویم جلالی.

```bash
php artisan make:module Product \
  --api --requests --resource --dto --tests \
  --fields="name:string:unique, sku:string:unique, price:decimal(10,2), is_active:boolean, released_at:date"
```

## فایل‌های تولیدشده

| مسیر | توضیح |
| --- | --- |
| `app/Repositories/Contracts/ProductRepositoryInterface.php` | اینترفیس ریپازیتوری که علاوه بر `find`، متدهای `findDynamic` و `getByDynamic` را برای فیلترهای انعطاف‌پذیر در اختیار می‌گذارد. |
| `app/Repositories/Eloquent/ProductRepository.php` | پیاده‌سازی مبتنی بر `BaseRepository` که بدون نیاز به کدنویسی اضافه از هلپرهای کوئری داینامیک استفاده می‌کند. |
| `app/Services/Contracts/ProductServiceInterface.php` | قرارداد سرویس که کاربرد `getByDynamic` را به لایهٔ بالاتر انتشار می‌دهد. |
| `app/Services/ProductService.php` | سرویس اصلی که DTO را به آرایه تبدیل کرده و متدهای داینامیک را در دسترس نگه می‌دارد. |
| `app/Http/Controllers/Api/V1/ProductController.php` | کنترلر REST که پاسخ‌ها را با `ApiResponseHelper` استاندارد می‌کند. |
| `app/Http/Resources/ProductResource.php` | تبدیل خروجی به JSON دلخواه با قالب‌بندی تاریخ‌ها و بولین‌ها. |
| `app/DTOs/ProductDTO.php` | DTO سبک برای نگهداری فیلدهای مجاز و تبدیل درخواست به آرایه. |
| `app/Http/Requests/StoreProductRequest.php` و `UpdateProductRequest.php` | قوانین اعتبارسنجی مستقیم از اسکیما مشتق می‌شوند و برای بروزرسانی تنظیم می‌گردند. |
| `tests/Feature/Products/ProductCrudTest.php` | تست کامل فیچری برای سناریوهای موفق و خطای اعتبارسنجی. |

> بسته به تنظیمات `config/module-generator.php` مسیر دقیق فایل‌ها ممکن است متفاوت باشد. مثال‌ها بر اساس مقادیر پیش‌فرض نوشته شده‌اند.

## لایهٔ ریپازیتوری و سرویس

ریپازیتوری از کلاس پایه ارث‌بری می‌کند؛ بنابراین تمام متدهای کمکی بدون کدنویسی اضافه در دسترس‌اند و در صورت نیاز می‌توانید متدهای اختصاصی خود را اضافه کنید.

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

#### استفاده از متدهای داینامیک

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

بدون نوشتن کوئری جدید می‌توانید ترکیبی از شرط‌های `where`، `orWhere`، `between` و ... را به سرویس بدهید و همان ساختار در همهٔ ماژول‌ها قابل استفاده است.

## کنترلر و پاسخ API

کنترلر با کمک `ApiResponseHelper` خروجی را در قالبی استاندارد برمی‌گرداند؛ تاریخ‌ها و بولین‌ها نیز توسط `ProductResource` به شکل یکسان نمایش داده می‌شوند.

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

نمونهٔ خروجی:

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

## فرم‌ریکوئست و DTO

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
$product = $this->repository->store($dto->toArray());
```

DTO فقط فیلدهای مجاز را نگه می‌دارد و به همین دلیل عبور دادن آن به متدهای `store` و `update` امن است.

## تست فیچری

تست تولیدشده هم مسیرهای موفق و هم سناریوهای خطا را پوشش می‌دهد.

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

بعد از هر تغییری در استاب‌ها یا کلاس‌های پایه می‌توانید این تست‌ها را اجرا کنید تا مطمئن شوید خروجی هنوز سالم است.

## ادامهٔ مسیر

- برای ترکیب‌های دیگر فلگ‌ها به [راهنمای کار با ابزار](usage.md) سر بزنید.
- برای شخصی‌سازی استاب‌ها از [راهنمای پیشرفته](advanced.md) استفاده کنید.
- برای کار با تاریخ‌های شمسی مستند [راهنمای Goli](goli-guide.md) را بخوانید.

</div>
