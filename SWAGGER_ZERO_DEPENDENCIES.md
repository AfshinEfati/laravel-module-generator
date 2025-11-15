# 🔧 Zero Dependencies - Swagger Without External Packages

## مشکل قبلی

Generated swagger documentation files شامل:

```php
use OpenApi\Annotations as OA;
```

این خط عارض error می‌شود **اگر** `zircote/swagger-php` یا `darkaonline/l5-swagger` نصب نباشند:

```
Fatal error: Uncaught Error: Class "OpenApi\Annotations" not found
```

همچنین validation rule objects (Password, Email, etc.) باعث error می‌شدند:

```
Error: Object of class Illuminate\Validation\Rules\Password could not be converted to string
```

## ✅ حل جدید (اکنون فعال)

### 1. PHPDoc Annotations Without Use Statement

Generated files **اکنون** بدون `use OpenApi\Annotations` ایجاد می‌شوند:

```php
<?php
namespace App\Docs;

/**
 * @OA\Tag(name="Product")
 *
 * Note: This file contains OpenAPI annotations that work WITHOUT any external dependencies.
 */
class ProductDoc
{
    /**
     * @OA\Get(path="/api/products", ...)
     */
    public function get_api_products(){}
}
```

✅ این فایل **بدون هیچ external package** کار می‌کند!

### 2. Why This Works

- `@OA\` annotations موجود در **PHP comments** هستند
- PHP انها را نادیده می‌گیرد (comments فقط برای documentation هستند)
- External packages (swagger-php) آنها را parse می‌کنند و به JSON تبدیل می‌کنند

### 3. Recommended Workflow

#### ✅ روش جدید (Recommended)

```bash
# 1. Generate PHPDoc files from routes
php artisan make:swagger --force

# 2. Files created in app/Docs/ without errors
ls -la app/Docs/

# 3. (اختیاری) Install swagger-php for UI generation
composer require zircote/swagger-php

# 4. Process with swagger-php
./vendor/bin/openapi --output public/docs/swagger.json app/Docs/
```

#### یا استفاده از Standalone UI

```bash
# 1. Initialize Swagger UI
php artisan swagger:init

# 2. Generate JSON spec
php artisan swagger:generate

# 3. View
php artisan swagger:ui
# Open: http://localhost:8000/docs
```

#### یا استفاده با L5-Swagger (اختیاری)

```bash
# 1. Install L5-Swagger (optional)
composer require darkaonline/l5-swagger

# 2. Generate PHPDoc files
php artisan make:swagger --force

# 3. Generate UI
php artisan l5-swagger:generate

# 4. View at: http://localhost:8000/docs
```

**Benefits:**

- ✅ Zero external dependencies
- ✅ No OpenAPI\Annotations needed
- ✅ Completely self-contained
- ✅ Easy to customize themes

---

## 🎯 مقایسه روش‌ها

| روش               | Dependencies | Output         | استفاده          |
| ----------------- | ------------ | -------------- | ---------------- |
| **PHPDoc فقط**    | ❌ None      | `.php` files   | Development      |
| **Standalone UI** | ❌ None      | JSON + HTML UI | Production       |
| **+ Swagger-PHP** | ✅ Optional  | JSON file      | Integration      |
| **+ L5-Swagger**  | ✅ Optional  | Full UI        | Production-ready |

---

## 🔧 مثال عملی

### Step 1: Generate PHPDoc Files

```bash
php artisan make:swagger --force
```

**Result:** `app/Docs/ProductDoc.php` بدون `use OpenApi\Annotations`:

```php
<?php
namespace App\Docs;

/**
 * @OA\Tag(name="Product")
 * Note: This file contains OpenAPI annotations that work WITHOUT any external dependencies.
 */
class ProductDoc
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List Products",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function get_api_products(){}
}
```

✅ **فایل بدون errors ایجاد می‌شود!**

### Step 2 (اختیاری): استفاده از Swagger-PHP

```bash
composer require zircote/swagger-php
./vendor/bin/openapi --output public/docs/api.json app/Docs/
```

✅ `@OA\` annotations توسط swagger-php parse می‌شوند

### Step 3 (اختیاری): استفاده از L5-Swagger

```bash
composer require darkaonline/l5-swagger
php artisan l5-swagger:generate
php artisan serve
# Visit: http://localhost:8000/docs
```

✅ Full UI ready!

---

## ✅ تایید کنید همه چیز کار می‌کند

```bash
# 1. Generate
php artisan make:swagger --force

# 2. Check syntax
php -l app/Docs/ProductDoc.php
# Should output: No syntax errors detected

# 3. Check file content
cat app/Docs/ProductDoc.php | head -20
# Should NOT show: "use OpenApi\Annotations"
# Should show: "@OA\ annotations in comments only"
```

---

## 🚨 Troubleshooting

### Problem: "Class OpenApi\Annotations not found"

**Reason:** Old files have `use OpenApi\Annotations`

**Solution:**

```bash
# Regenerate with --force
php artisan make:swagger --force
```

### Problem: "Validation rule error"

**Reason:** Password/Email rule objects can't be converted to string

**Solution:** ✅ Fixed! Now automatically detects rule objects

### Problem: "@OA\ annotations not appearing in file"

**Reason:** File wasn't generated properly

**Solution:**

```bash
rm -rf app/Docs/
php artisan make:swagger --force
ls -la app/Docs/
```

---

## 📚 مستندات مرتبط

- [PHPDoc Generation](SWAGGER_PHPDOC_GENERATION.md) - How to generate docs
- [Integration Guide](INTEGRATION_GUIDE.md) - Using optional packages
- [Command Reference](COMMAND_REFERENCE.md) - All available commands

---

## خلاصه

**Zero Dependencies Approach** اکنون کامل است:

✅ `make:swagger` command PHP files ایجاد می‌کند **بدون external packages**
✅ `@OA\` annotations در comments موجود هستند (PHP error نمی‌دهند)
✅ Validation rule objects (Password, Email, etc.) مدیریت می‌شوند
✅ Optional: swagger-php یا l5-swagger برای UI generation
✅ Standalone UI بدون هیچ dependency

**یکی از این گزینه‌ها را انتخاب کنید:**

1. **صرفاً PHPDoc files** - Development
2. **+ Standalone UI** - Quick viewing
3. **+ Swagger-PHP** - Advanced integration
4. **+ L5-Swagger** - Production-ready
