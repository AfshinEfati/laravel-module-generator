# شروع سریع

<div dir="rtl" markdown="1">

اولین ماژول خود را در کمتر از پنج دقیقه بسازید.

## مرحلهٔ ۱: نصب بسته

```bash
composer require efati/laravel-module-generator
```

Service Provider به‌صورت خودکار هنگام راه‌اندازی کنسول رجیستر می‌شود. نیاز به دستور انتشار اضافی نیست!

## مرحلهٔ ۲: تعریف Schema (یکی را انتخاب کنید)

### گزینهٔ الف: فیلدهای درون‌خطی

```bash
php artisan make:module Product \
  --fields="name:string:unique, price:decimal(10,2), stock:integer, is_active:boolean"
```

### گزینهٔ ب: از مایگریشن موجود

```bash
# اگر مایگریشن موجود است:
php artisan make:module Product --from-migration

# یا مسیر مایگریشن را مشخص کنید:
php artisan make:module Product --from-migration=database/migrations/2024_01_15_create_products_table.php
```

## مرحلهٔ ۳: تولید ماژول

برای یک **ماژول API کامل**:

```bash
php artisan make:module Product \
  --api \
  --requests \
  --tests \
  --swagger \
  --fields="name:string:unique, price:decimal(10,2), stock:integer, is_active:boolean"
```

این موارد را تولید می‌کند:

✅ ریپازیتوری + Interface
✅ سرویس + Interface
✅ DTO با اعتبارسنجی
✅ کنترلر API
✅ فرم‌های ریکوئست
✅ ریسورس API
✅ لایهٔ Actions (۷ action)
✅ تست‌های فیچر
✅ سرویس‌پرووایدر (خودکار رجیستر)
✅ مستندات OpenAPI

## مرحلهٔ ۴: ثبت مسیرها

```php
// routes/api.php
Route::apiResource('products', ProductController::class);

// یا با پیشوند دلخواه:
Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
});
```

## مرحلهٔ ۵: تست‌کردن

```bash
# اجرای تست‌های فیچر
php artisan test tests/Feature/ProductCrudTest.php

# مشاهدهٔ مستندات Swagger (اگر l5-swagger نصب شده است)
php artisan l5-swagger:generate
# بروید به: http://yourapp.test/api/documentation
```

## شخصی‌سازی فایل‌های تولیدشده (اختیاری)

برای قالب‌های دلخواه:

```bash
php artisan vendor:publish \
  --provider="Efati\ModuleGenerator\ModuleGeneratorServiceProvider" \
  --tag=module-generator-stubs
```

فایل‌ها را در `resources/stubs/module-generator/` ویرایش کنید و بعد با `--force` دوباره تولید کنید:

```bash
php artisan make:module Product --api --force
```

## ساختار فایل‌های تولیدشده

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
│   └── ... (۵ action دیگر)
├── Providers/ProductServiceProvider.php
└── Docs/ProductDoc.php

tests/
└── Feature/ProductCrudTest.php
```

## گام‌های بعدی

- [تولید ماژول‌ها](./features/generating-modules.md) را برای تمام گزینه‌های دستور بخوانید
- درباره [ویژگی‌های Schema-Aware](./features/schema-aware-generation.md) بیاموزید
- الگوهای [لایهٔ Action](./features/action-layer.md) را بررسی کنید
- [مرجع API](./reference.md) را برای تمام فلگ‌های CLI مشاهده کنید

</div>
