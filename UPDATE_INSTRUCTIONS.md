# دستورالعمل به‌روزرسانی پکیج

## مشکل فعلی

خطای `Syntax error, unexpected '.'` در هنگام اجرای `php artisan l5-swagger:generate` به این دلیل است که:
- پکیج در پروژه Laravel شما نسخه قدیمی است
- تغییرات جدید (اصلاح backslash ها) هنوز در پروژه شما اعمال نشده

## راه حل 1: به‌روزرسانی از طریق Composer (توصیه می‌شود)

اگر پکیج را از طریق Composer نصب کرده‌اید:

```bash
cd /var/www/myAgency/agency-main

# حذف کش composer
composer clear-cache

# به‌روزرسانی پکیج
composer update efati/laravel-module-generator

# پاک کردن فایل‌های قدیمی
rm -rf app/Docs/*

# تولید مجدد با نسخه جدید
php artisan make:swagger --force

# تست
php artisan l5-swagger:generate
```

## راه حل 2: نصب مستقیم از سورس (برای توسعه)

اگر می‌خواهید مستقیماً از سورس استفاده کنید:

```bash
cd /var/www/myAgency/agency-main

# ویرایش composer.json
# اضافه کردن به بخش repositories:
```

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/home/afshin/dev-stack/projects/laravel-module-generator"
        }
    ]
}
```

```bash
# نصب از مسیر local
composer require efati/laravel-module-generator:@dev

# پاک کردن فایل‌های قدیمی
rm -rf app/Docs/*

# تولید مجدد
php artisan make:swagger --force

# تست
php artisan l5-swagger:generate
```

## راه حل 3: کپی دستی فایل (موقت)

اگر فوری نیاز دارید:

```bash
# کپی فایل جدید به vendor
cp /home/afshin/dev-stack/projects/laravel-module-generator/src/Commands/GenerateSwaggerCommand.php \
   /var/www/myAgency/agency-main/vendor/efati/laravel-module-generator/src/Commands/

# پاک کردن کش Laravel
cd /var/www/myAgency/agency-main
php artisan cache:clear
php artisan config:clear

# پاک کردن فایل‌های قدیمی
rm -rf app/Docs/*

# تولید مجدد
php artisan make:swagger --force

# تست
php artisan l5-swagger:generate
```

## تست صحت نصب

برای اطمینان از اینکه نسخه جدید نصب شده:

```bash
cd /var/www/myAgency/agency-main

# بررسی محتوای فایل Command
grep '@OA\\\\\\\\Get' vendor/efati/laravel-module-generator/src/Commands/GenerateSwaggerCommand.php

# اگر خروجی داشت، نسخه جدید نصب شده ✅
# اگر خروجی نداشت، نسخه قدیمی است ❌
```

## بررسی فایل تولید شده

بعد از اجرای `make:swagger`، یکی از فایل‌های تولید شده را باز کنید:

```bash
cat app/Docs/UserDoc.php | head -30
```

باید این را ببینید:
```php
use OpenApi\Annotations as OA;  // ✅ یک backslash

/**
 * @OA\Tag(name="User")  // ✅ یک backslash در فایل نهایی
 */
```

اگر این را دیدید، مشکل حل شده:
```php
use OpenApi\\Annotations as OA;  // ❌ دو backslash (اشتباه)
```

## عیب‌یابی

### خطا همچنان وجود دارد

1. **بررسی نسخه پکیج:**
```bash
composer show efati/laravel-module-generator
```

2. **پاک کردن کامل و نصب مجدد:**
```bash
composer remove efati/laravel-module-generator
rm -rf vendor/efati
composer require efati/laravel-module-generator
```

3. **بررسی فایل Command در vendor:**
```bash
cat vendor/efati/laravel-module-generator/src/Commands/GenerateSwaggerCommand.php | grep -A 2 "sprintf.*@OA"
```

باید `@OA\\\\` را ببینید (4 backslash در کد = 2 backslash در string = 1 backslash در فایل نهایی)

### فایل‌های قدیمی هنوز موجود هستند

```bash
# حذف کامل فایل‌های Docs
rm -rf app/Docs/*

# تولید مجدد
php artisan make:swagger --force
```

## توضیح تکنیکی

### چرا باید `@OA\\` باشد؟

در PHP، وقتی می‌خواهیم یک backslash در string بنویسیم:

```php
// در کد PHP:
$line = '     * @OA\\Get(';

// در فایل تولید شده:
     * @OA\Get(

// swagger-php می‌خواند:
@OA\Get  ✅
```

اگر فقط `@OA\` بنویسیم:

```php
// در کد PHP:
$line = '     * @OA\Get(';  // ❌ \G به عنوان escape sequence

// خطا: Invalid escape sequence
```

## خلاصه

1. ✅ تغییرات در `/home/afshin/dev-stack/projects/laravel-module-generator` انجام شده
2. ❌ پروژه `/var/www/myAgency/agency-main` هنوز نسخه قدیمی دارد
3. 🔧 باید پکیج را در پروژه Laravel به‌روز کنید

**توصیه:** از راه حل 1 (composer update) استفاده کنید.
