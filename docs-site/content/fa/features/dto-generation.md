# تولید DTO (Data Transfer Objects)

<div dir="rtl" markdown="1">

[🇬🇧 English](../../en/features/dto-generation.md){ .language-switcher }

## معرفی

DTO‌ها (Data Transfer Objects) کلاس‌هایی هستند که برای انتقال داده بین لایه‌های مختلف برنامه استفاده می‌شوند. نسخه جدید ژنراتور از ویژگی‌های مدرن PHP 8.1+ برای ساخت DTO‌های تمیزتر و ایمن‌تر استفاده می‌کند.

## ویژگی‌های جدید

### 1. Constructor Property Promotion

به جای تعریف جداگانه property و constructor:

```php
// قدیمی ❌
class ProductDTO
{
    public mixed $name;
    public mixed $price;
    
    public function __construct(mixed $name = null, mixed $price = null)
    {
        $this->name = $name;
        $this->price = $price;
    }
}
```

اکنون به صورت خودکار:

```php
// جدید ✅
class ProductDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $price = null,
        public readonly mixed $description = null
    ) {}
}
```

### 2. Readonly Properties

تمام property‌ها `readonly` هستند، یعنی بعد از مقداردهی اولیه قابل تغییر نیستند:

```php
$dto = new ProductDTO(name: 'Laptop', price: 1500);
$dto->name = 'Phone'; // ❌ خطا: Cannot modify readonly property
```

### 3. Named Arguments

استفاده از named arguments برای خوانایی بهتر:

```php
$dto = new ProductDTO(
    name: 'Laptop',
    price: 1500,
    description: 'A powerful laptop'
);
```

## نحوه استفاده

### تولید خودکار

```bash
php artisan make:module Product --api
```

DTO به صورت خودکار ساخته می‌شود:

```php
<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ProductDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $price = null,
        public readonly mixed $description = null,
        public readonly mixed $category_id = null,
        public readonly mixed $is_active = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            price: $request->input('price'),
            description: $request->input('description'),
            category_id: $request->input('category_id'),
            is_active: $request->input('is_active'),
        );
    }

    public function toArray(): array
    {
        $out = [];
        if ($this->name !== null) { $out['name'] = $this->name; }
        if ($this->price !== null) { $out['price'] = $this->price; }
        if ($this->description !== null) { $out['description'] = $this->description; }
        if ($this->category_id !== null) { $out['category_id'] = $this->category_id; }
        if ($this->is_active !== null) { $out['is_active'] = $this->is_active; }
        return $out;
    }
}
```

## استفاده در Controller

```php
use App\DTOs\ProductDTO;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromRequest($request);
        
        $product = $this->service->store($dto);
        
        return new ProductResource($product);
    }
    
    public function update(UpdateProductRequest $request, Product $product)
    {
        $dto = ProductDTO::fromRequest($request);
        
        $this->service->update($product->id, $dto);
        
        return new ProductResource($product->fresh());
    }
}
```

## استفاده در Service

```php
use App\DTOs\ProductDTO;

class ProductService implements ProductServiceInterface
{
    public function store(ProductDTO $dto): Product
    {
        // تبدیل DTO به آرایه
        $data = $dto->toArray();
        
        // منطق تجاری
        $data['slug'] = Str::slug($dto->name);
        $data['user_id'] = auth()->id();
        
        return $this->repository->store($data);
    }
}
```

## سفارشی‌سازی DTO

### 1. افزودن متدهای کمکی

```php
class ProductDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $price = null
    ) {}
    
    // متد کمکی برای محاسبه قیمت با تخفیف
    public function getPriceWithDiscount(float $discountPercent): float
    {
        return $this->price * (1 - $discountPercent / 100);
    }
    
    // متد کمکی برای بررسی اعتبار
    public function isValid(): bool
    {
        return $this->name !== null && $this->price > 0;
    }
}
```

### 2. ساخت از منابع مختلف

```php
class ProductDTO
{
    // از Request
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            price: $request->input('price')
        );
    }
    
    // از Model
    public static function fromModel(Product $product): self
    {
        return new self(
            name: $product->name,
            price: $product->price,
            description: $product->description
        );
    }
    
    // از آرایه
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            price: $data['price'] ?? null
        );
    }
}
```

### 3. اعتبارسنجی در DTO

```php
class ProductDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $price = null
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('نام محصول الزامی است');
        }
        
        if ($this->price !== null && $this->price < 0) {
            throw new InvalidArgumentException('قیمت نمی‌تواند منفی باشد');
        }
    }
}
```

## الگوهای پیشرفته

### 1. DTO تو در تو (Nested DTOs)

```php
class AddressDTO
{
    public function __construct(
        public readonly mixed $street = null,
        public readonly mixed $city = null,
        public readonly mixed $country = null
    ) {}
}

class CustomerDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $email = null,
        public readonly ?AddressDTO $address = null
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            address: new AddressDTO(
                street: $request->input('address.street'),
                city: $request->input('address.city'),
                country: $request->input('address.country')
            )
        );
    }
}
```

### 2. DTO برای فیلترها

```php
class ProductFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int $categoryId = null,
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly ?string $sortBy = 'created_at',
        public readonly ?string $sortDirection = 'desc'
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->input('search'),
            categoryId: $request->input('category_id'),
            minPrice: $request->input('min_price'),
            maxPrice: $request->input('max_price'),
            sortBy: $request->input('sort_by', 'created_at'),
            sortDirection: $request->input('sort_direction', 'desc')
        );
    }
    
    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'category_id' => $this->categoryId,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
        ], fn($value) => $value !== null);
    }
}
```

### 3. Collection از DTOs

```php
use Illuminate\Support\Collection;

class ProductDTOCollection
{
    private Collection $items;
    
    public function __construct(array $items = [])
    {
        $this->items = collect($items);
    }
    
    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn($item) => ProductDTO::fromArray($item),
            $data
        );
        
        return new self($items);
    }
    
    public function toArray(): array
    {
        return $this->items
            ->map(fn(ProductDTO $dto) => $dto->toArray())
            ->toArray();
    }
    
    public function filter(callable $callback): self
    {
        return new self($this->items->filter($callback)->toArray());
    }
}
```

## مقایسه با روش قدیمی

### قبل از آپدیت

```php
class ProductDTO
{
    public mixed $name;
    public mixed $price;
    public mixed $description;
    
    public function __construct(
        mixed $name = null,
        mixed $price = null,
        mixed $description = null
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }
    
    public static function fromRequest(Request $request): self
    {
        $dto = new self();
        $dto->name = $request->input('name');
        $dto->price = $request->input('price');
        $dto->description = $request->input('description');
        return $dto;
    }
}

// قابل تغییر
$dto = new ProductDTO('Laptop', 1500);
$dto->price = 2000; // ✅ کار می‌کند
```

### بعد از آپدیت

```php
class ProductDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $price = null,
        public readonly mixed $description = null
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            price: $request->input('price'),
            description: $request->input('description'),
        );
    }
}

// غیرقابل تغییر
$dto = new ProductDTO(name: 'Laptop', price: 1500);
$dto->price = 2000; // ❌ خطا
```

## بهترین روش‌ها

### 1. استفاده از Type Safety

```php
// بهتر از mixed
class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $description = null
    ) {}
}
```

### 2. اعتبارسنجی در FormRequest

```php
// اعتبارسنجی را در FormRequest انجام دهید
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}

// DTO فقط داده را انتقال می‌دهد
$dto = ProductDTO::fromRequest($request);
```

### 3. تبدیل Null به مقادیر پیش‌فرض

```php
class ProductDTO
{
    public function toArray(): array
    {
        return [
            'name' => $this->name ?? '',
            'price' => $this->price ?? 0,
            'is_active' => $this->is_active ?? true,
        ];
    }
}
```

## نکات مهم

### تفاوت با Model

```php
// Model - برای تعامل با دیتابیس
class Product extends Model
{
    protected $fillable = ['name', 'price'];
    protected $casts = ['price' => 'decimal:2'];
}

// DTO - برای انتقال داده
class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price
    ) {}
}
```

### استفاده همزمان

```php
// دریافت از request
$dto = ProductDTO::fromRequest($request);

// ذخیره در دیتابیس
$product = Product::create($dto->toArray());

// یا از طریق service
$product = $this->service->store($dto);
```

## منابع بیشتر

- [PHP 8.1 Constructor Property Promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion)
- [Readonly Properties](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties)
- [DTO Pattern](https://martinfowler.com/eaaCatalog/dataTransferObject.html)

</div>
