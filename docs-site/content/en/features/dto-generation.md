# DTO Generation

[🇮🇷 فارسی](../../fa/features/dto-generation.md){ .language-switcher }

## Introduction

DTOs (Data Transfer Objects) are classes used to transfer data between different layers of your application. The new version of the generator uses modern PHP 8.1+ features to create cleaner and safer DTOs.

## New Features

### 1. Constructor Property Promotion

Instead of separate property and constructor definitions:

```php
// Old ❌
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

Now automatically:

```php
// New ✅
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

All properties are `readonly`, meaning they cannot be changed after initial assignment:

```php
$dto = new ProductDTO(name: 'Laptop', price: 1500);
$dto->name = 'Phone'; // ❌ Error: Cannot modify readonly property
```

### 3. Named Arguments

Using named arguments for better readability:

```php
$dto = new ProductDTO(
    name: 'Laptop',
    price: 1500,
   description: 'A powerful laptop'
);
```

## Usage

### Automatic Generation

```bash
php artisan make:module Product --api
```

DTO is automatically created:

```php
<?php

namespace App\DTOs;

use Illuminate\Http\Request;

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

    public function toArray(): array
    {
        $out = [];
        if ($this->name !== null) { $out['name'] = $this->name; }
        if ($this->price !== null) { $out['price'] = $this->price; }
        if ($this->description !== null) { $out['description'] = $this->description; }
        return $out;
    }
}
```

## Using in Controllers

```php
use App\DTOs\ProductDTO;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromRequest($request);
        $product = $this->service->store($dto);
        
        return new ProductResource($product);
    }
}
```

## Advanced Patterns

### 1. Helper Methods

```php
class ProductDTO
{
    public function __construct(
        public readonly mixed $name = null,
        public readonly mixed $price = null
    ) {}
    
    public function getPriceWithDiscount(float $discountPercent): float
    {
        return $this->price * (1 - $discountPercent / 100);
    }
}
```

### 2. Multiple Sources

```php
class ProductDTO
{
    public static function fromRequest(Request $request): self { }
    public static function fromModel(Product $product): self { }
    public static function fromArray(array $data): self { }
}
```

## Best Practices

### 1. Use Type Safety

```php
// Better than mixed
class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $description = null
    ) {}
}
```

### 2. Validate in FormRequest

```php
// Validation in FormRequest
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

// DTO just transfers data
$dto = ProductDTO::fromRequest($request);
```

## Learn More

- [PHP 8.1 Constructor Property Promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion)
- [Readonly Properties](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties)
- [DTO Pattern](https://martinfowler.com/eaaCatalog/dataTransferObject.html)
