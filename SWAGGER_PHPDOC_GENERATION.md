# 📝 Swagger Documentation Generation

## دو روش برای ایجاد Swagger Documentation

### روش ۱: PHPDoc Annotations (خودکار در module)

```bash
# ایجاد module با swagger documentation
php artisan make:module Product --swagger

# یا با short flag
php artisan make:module Product -s
```

**نتیجه:** فایل `app/Docs/ProductDoc.php` با `@OA\` annotations

```php
<?php

namespace App\Docs;

/**
 * @OA\Tag(name="Product")
 *
 * Note: This file contains OpenAPI annotations...
 */
class ProductDoc
{
    // @OA\Get(), @OA\Post(), etc.
}
```

### روش ۲: تولید تمام Documentation

```bash
# تولید swagger docs برای تمام routes
php artisan make:swagger

# یا force overwrite
php artisan make:swagger --force
```

**نتیجه:** فایل‌های PHPDoc در `app/Docs/` directory

---

## استفاده مستقل

### صرفاً برای یک model

```bash
php artisan make:module Product --swagger --no-controller --no-dto --no-service
# تنها ProductDoc.php ایجاد می‌شود
```

---

## Format و ساختار

### Generated File مثال

```php
<?php

namespace App\Docs;

/**
 * @OA\Tag(name="Product")
 *
 * Note: This file contains OpenAPI annotations that can be processed by:
 * - zircote/swagger-php (https://github.com/zircote/swagger-php)
 * - darkaonline/l5-swagger (wrapper for swagger-php)
 */
class ProductDoc
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List products",
     *     @OA\Response(
     *         response=200,
     *         description="List of products"
     *     )
     * )
     */
    public function listProducts() {}

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create product",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=201, description="Product created")
     * )
     */
    public function storeProduct() {}
}
```

---

## اختیاری: استفاده با swagger-php

اگر می‌خواهید Swagger UI یا OpenAPI JSON تولید کنید:

```bash
# نصب swagger-php
composer require zircote/swagger-php

# یا l5-swagger
composer require darkaonline/l5-swagger
```

سپس:

```bash
# L5-Swagger
php artisan l5-swagger:generate

# یا مستقیم
./vendor/bin/openapi --output docs/swagger.json app/Docs/
```

---

## Options

### make:module

```bash
# تنها swagger
php artisan make:module Product --swagger

# با سایر options
php artisan make:module Product --swagger --api --tests

# اجباری overwrite
php artisan make:module Product --swagger --force
```

### make:swagger

```bash
# تولید برای تمام routes
php artisan make:swagger

# با force
php artisan make:swagger --force

# برای مسیر خاص
php artisan make:swagger --path=api

# برای controller خاص
php artisan make:swagger --controller=Product

# output directory مختص
php artisan make:swagger --output=resources/swagger
```

---

## Zero Dependencies

✅ تولید PHPDoc annotations **بدون وابستگی**
✅ استفاده از `@OA\` tags برای documentation
✅ اختیاری: نصب swagger-php برای تولید UI

---

## مثال کامل

```bash
# ۱. ایجاد module با swagger
php artisan make:module Product --swagger --api --tests

# ۲. نتیجه:
#    app/Repositories/Eloquent/ProductRepository.php
#    app/Services/ProductService.php
#    app/DTOs/ProductDTO.php
#    app/Http/Controllers/Api/ProductController.php
#    app/Docs/ProductDoc.php  ← Swagger documentation
#    tests/Feature/ProductTest.php

# ۳. اگر می‌خواهید Swagger UI هم:
composer require zircote/swagger-php
php artisan l5-swagger:generate
```

---

## Troubleshooting

### "make:swagger: command not found"

```bash
composer dump-autoload
php artisan package:discover
```

### فایل‌ها overwrite نمی‌شوند

```bash
php artisan make:swagger --force
```

### PHP Errors در generated file

```bash
php -l app/Docs/ProductDoc.php
# چک کنید syntax درست است
```

---

## Next Steps

- ✅ تولید modules با swagger
- ✅ Edit `app/Docs/ProductDoc.php` برای customization
- ⭕ Install swagger-php و generate UI (optional)
- ⭕ Integration با L5-Swagger (optional)

**موفق باشید!** 🚀
