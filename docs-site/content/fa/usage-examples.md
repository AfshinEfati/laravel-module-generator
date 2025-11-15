---
title: نمونه‌های استفاده
description: نمونه‌های عملی و موارد استفاده برای Laravel Module Generator
---

# نمونه‌های استفاده

یاد بگیرید چگونه از Laravel Module Generator با مثال‌های واقعی استفاده کنید.

## تولید ماژول اولیه

یک ماژول وبلاگ ساده تولید کنید:

```bash
php artisan make:module Blog --fields=title,content,published_at
```

این موارد را ایجاد می‌کند:

- `Blog/Controllers/BlogController.php`
- `Blog/Services/BlogService.php`
- `Blog/DTOs/BlogDTO.php`
- `Blog/Requests/StoreBlogRequest.php`
- Database migration
- Tests

## تولید با Namespace سفارشی

```bash
php artisan make:module Admin\\Dashboard --fields=widget_type,config
```

## تولید فقط اجزای خاص

```bash
# فقط controller و service
php artisan make:module Products --only=controller,service

# فقط DTO و request
php artisan make:module Orders --only=dto,request
```

## استفاده از ماژول تولیدشده

پس از تولید، ماژول شما آماده استفاده است:

```php
use Modules\Blog\Services\BlogService;
use Modules\Blog\DTOs\BlogDTO;

$service = new BlogService();
$dto = new BlogDTO(
    title: 'پست من',
    content: 'محتوای پست...',
    published_at: now()
);

$blog = $service->store($dto);
```

## عملیات Database

انبار تولیدشده انتزاع database را فراهم می‌کند:

```php
use Modules\Blog\Repositories\BlogRepository;

$repo = new BlogRepository();
$posts = $repo->all();
$post = $repo->find(1);
$post = $repo->store($dto);
$repo->update(1, $dto);
$repo->delete(1);
```

## تست ماژول تولیدشده

تست‌ها به طور خودکار تولید می‌شوند:

```bash
phpunit tests/Feature/BlogTest.php
```

## انتشار Stubs سفارشی

فایل‌های تولیدشده را سفارشی کنید:

```bash
php artisan vendor:publish --tag=module-generator-stubs
```

Stubs را در `resources/stubs/modules/` ویرایش کنید و ماژول‌ها را دوباره تولید کنید.
