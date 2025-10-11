# لایهٔ اکشن

این پکیج گزینه‌ای برای تولید یک لایه اکشن برای ماژول‌های شما فراهم می‌کند. لایه اکشن مجموعه‌ای از کلاس‌های قابل فراخوانی است که منطق تجاری برنامه شما را در بر می‌گیرد.

## تولید اکشن‌ها

شما می‌توانید با استفاده از پرچم `--actions` با دستور `make:module`، اکشن‌ها را برای یک ماژول تولید کنید.

```bash
php artisan make:module Product --actions
```

این دستور مجموعه‌ای از کلاس‌های اکشن را در دایرکتوری `app/Actions/Product` تولید می‌کند. اکشن‌های تولید شده با عملیات CRUD ماژول مطابقت دارند:

- `ListProductAction`
- `CreateProductAction`
- `ShowProductAction`
- `UpdateProductAction`
- `DeleteProductAction`

## استفاده از اکشن‌ها

کنترلر تولید شده برای ماژول به طور خودکار برای استفاده از کلاس‌های اکشن جدید سیم‌کشی می‌شود. در اینجا مثالی از نحوه izgled متد `store` در کنترلر آمده است:

```php
public function store(StoreProductRequest $request, CreateProductAction $createProductAction)
{
    $dto = ProductDTO::fromRequest($request);
    $product = $createProductAction($dto);
    return ApiResponseHelper::successResponse(new ProductResource($product), 'created', 201);
}
```

همانطور که می‌بینید، کنترلر اکنون بسیار سبک‌تر است و منطق تجاری در کلاس `CreateProductAction` کپسوله شده است.

## BaseAction

تمام اکشن‌های تولید شده از یک کلاس `BaseAction` ارث‌بری می‌کنند. این کلاس چند ویژگی مفید مانند لاگ‌گیری را فراهم می‌کند.

### لاگ‌گیری

کلاس `BaseAction` دارای یک ویژگی `logger` است که می‌توانید از آن برای ثبت پیام‌ها استفاده کنید. لاگر به طور خودکار برای استفاده از کانال لاگ‌گیری که در فایل `config/module-generator.php` تعریف شده است، پیکربندی می‌شود.

در اینجا مثالی از نحوه استفاده از لاگر در یک اکشن آمده است:

```php
class CreateProductAction extends BaseAction
{
    // ...

    protected function handle(mixed ...$payload): mixed
    {
        $this->logger->info('Creating a new product...');
        return $this->service->store(...$payload);
    }
}
```