# Route-Based Swagger Documentation Generator

## Overview

این دستور جدید به شما امکان می‌دهد که مستندات Swagger را مستقل از مدل‌ها و فقط بر اساس روت‌های موجود در پروژه Laravel تولید کنید.

## استفاده پایه

### اسکن تمام روت‌ها

برای تولید مستندات Swagger از تمام روت‌های پروژه:

```bash
php artisan make:swagger
```

این دستور:
- ✅ تمام روت‌های Laravel را اسکن می‌کند
- ✅ روت‌ها را بر اساس Controller گروه‌بندی می‌کند
- ✅ برای هر Controller یک فایل Swagger جداگانه می‌سازد
- ✅ Closure route ها را نادیده می‌گیرد

### فیلتر کردن بر اساس مسیر

فقط روت‌های API را مستندسازی کنید:

```bash
php artisan make:swagger --path=api
```

یا روت‌های یک نسخه خاص:

```bash
php artisan make:swagger --path=api/v1
```

### فیلتر کردن بر اساس Controller

فقط Controller های خاصی را مستندسازی کنید:

```bash
php artisan make:swagger --controller=Api
```

یا یک namespace کامل:

```bash
php artisan make:swagger --controller="App\Http\Controllers\Api\V1"
```

### تعیین مسیر خروجی

مسیر ذخیره فایل‌های Swagger را تغییر دهید:

```bash
php artisan make:swagger --output=app/Documentation
```

### بازنویسی فایل‌های موجود

برای بازنویسی فایل‌های Swagger موجود:

```bash
php artisan make:swagger --force
```

## ترکیب فیلترها

می‌توانید چندین فیلتر را با هم ترکیب کنید:

```bash
# فقط API V1 controllers
php artisan make:swagger --path=api/v1 --controller=V1 --force

# فقط روت‌های admin
php artisan make:swagger --path=admin --output=app/Docs/Admin
```

## مثال‌های عملی

### مثال 1: مستندسازی API

فرض کنید این روت‌ها را دارید:

```php
// routes/api.php
Route::prefix('api/v1')->middleware(['api', 'auth:sanctum'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('posts', PostController::class);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});
```

با اجرای دستور:

```bash
php artisan make:swagger --path=api/v1
```

خروجی:

```
🔍 Scanning Laravel routes...
📋 Found 12 routes to document.
  ✓ Generated: UserDoc.php
  ✓ Generated: PostDoc.php
  ✓ Generated: AuthDoc.php
✅ Successfully generated 3 swagger documentation file(s).
```

### مثال 2: فایل تولید شده

برای `UserController` فایل `app/Docs/UserDoc.php` ساخته می‌شود:

```php
<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="User")
 */
class UserDoc
{
    /**
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     description="Pass a valid bearer token retrieved from the authentication endpoint."
     * )
     */
    public function bearerAuthSecurity(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="List User",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index_ApiV1Users(): void
    {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Create User",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function store_ApiV1Users(): void
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Show User",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show_ApiV1UsersId(): void
    {
    }

    // ... update و destroy methods
}
```

## ویژگی‌های خودکار

### 1. تشخیص نوع پارامتر

دستور به صورت هوشمند نوع پارامترها را تشخیص می‌دهد:

- `{id}`, `{user_id}` → `integer`
- `{uuid}`, `{token}` → `string`
- سایر موارد → `string`

### 2. تشخیص احراز هویت

اگر روت دارای middleware های زیر باشد، به صورت خودکار:
- Response 401 اضافه می‌شود
- Security scheme اضافه می‌شود

Middleware های پشتیبانی شده:
- `auth`
- `auth:api`
- `auth:sanctum`
- `auth:web`

### 3. Response های هوشمند

بر اساس HTTP method، response های مناسب اضافه می‌شود:

| Method | Responses |
|--------|-----------|
| GET | 200, 401 (if auth), 404 |
| POST | 201, 401 (if auth), 422 |
| PUT/PATCH | 200, 401 (if auth), 404, 422 |
| DELETE | 204, 401 (if auth), 404 |

### 4. گروه‌بندی خودکار

روت‌ها به صورت خودکار بر اساس Controller گروه‌بندی می‌شوند:
- `UserController` → `UserDoc.php`
- `PostController` → `PostDoc.php`
- `AuthController` → `AuthDoc.php`

## پیکربندی

### تنظیمات Security Scheme

در `config/module-generator.php`:

```php
'swagger' => [
    'security' => [
        'auth_middleware' => ['auth', 'auth:api', 'auth:sanctum'],
        'default' => 'bearerAuth',
        'schemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearer_format' => 'JWT',
                'description' => 'Pass a valid bearer token retrieved from the authentication endpoint.'
            ],
            // می‌توانید scheme های دیگر اضافه کنید
            'apiKey' => [
                'type' => 'apiKey',
                'in' => 'header',
                'name' => 'X-API-Key',
                'description' => 'API Key for authentication'
            ],
        ],
    ],
],
```

## تفاوت با `make:module --swagger`

| ویژگی | `make:module --swagger` | `make:swagger` |
|-------|------------------------|----------------|
| **منبع** | مدل و تعریف دستی | روت‌های موجود Laravel |
| **وابستگی** | نیاز به مدل دارد | مستقل از مدل |
| **خروجی** | یک فایل برای یک مدل | چندین فایل برای تمام Controller ها |
| **کاربرد** | زمان ساخت ماژول جدید | زمان مستندسازی پروژه موجود |
| **انعطاف** | محدود به CRUD | تمام روت‌ها (حتی custom) |

## Workflow پیشنهادی

### برای پروژه‌های جدید:

```bash
# 1. ساخت ماژول با swagger
php artisan make:module User --api --swagger

# 2. اضافه کردن روت‌ها
# routes/api.php

# 3. به‌روزرسانی مستندات از روت‌ها
php artisan make:swagger --path=api --force
```

### برای پروژه‌های موجود:

```bash
# مستندسازی تمام API های موجود
php artisan make:swagger --path=api

# یا فقط یک بخش خاص
php artisan make:swagger --path=api/v1 --controller=V1
```

## عیب‌یابی

### مشکل: هیچ روتی پیدا نشد

**علت:** فیلترها خیلی محدود هستند یا روت‌ها Closure هستند.

**راه حل:**
```bash
# بدون فیلتر اجرا کنید
php artisan make:swagger

# یا فیلتر را کلی‌تر کنید
php artisan make:swagger --path=api
```

### مشکل: فایل‌ها بازنویسی نمی‌شوند

**راه حل:**
```bash
php artisan make:swagger --force
```

### مشکل: Tag ها درست نیستند

**علت:** نام Controller ها استاندارد نیستند.

**راه حل:** Tag از نام Controller استخراج می‌شود:
- `UserController` → Tag: `User`
- `PostController` → Tag: `Post`
- `AuthController` → Tag: `Auth`

## یکپارچه‌سازی با L5-Swagger

بعد از تولید فایل‌ها:

```bash
# تولید مستندات نهایی
php artisan l5-swagger:generate

# مشاهده مستندات
# http://your-app.test/api/documentation
```

## مثال کامل

```bash
# 1. اسکن و تولید مستندات
php artisan make:swagger --path=api/v1 --force

# 2. بررسی فایل‌های تولید شده
ls -la app/Docs/

# 3. تولید مستندات Swagger نهایی
php artisan l5-swagger:generate

# 4. باز کردن مرورگر
open http://localhost:8000/api/documentation
```

## نکات مهم

1. **Closure Routes:** روت‌های Closure نادیده گرفته می‌شوند
2. **Route Names:** اگر روت name نداشته باشد، از URI استفاده می‌شود
3. **Method Names:** نام متدها از route name یا URI ساخته می‌شود
4. **Security:** فقط اگر middleware احراز هویت وجود داشته باشد، security اضافه می‌شود
5. **Overwrite:** بدون `--force` فایل‌های موجود بازنویسی نمی‌شوند

## خلاصه

دستور `make:swagger` یک ابزار قدرتمند برای:
- ✅ مستندسازی خودکار API های موجود
- ✅ مستقل از مدل‌ها و ساختار پروژه
- ✅ پشتیبانی از فیلترهای مختلف
- ✅ تولید مستندات استاندارد OpenAPI
- ✅ تشخیص خودکار احراز هویت و پارامترها

برای پروژه‌های بزرگ با روت‌های زیاد، این دستور زمان زیادی صرفه‌جویی می‌کند! 🚀
