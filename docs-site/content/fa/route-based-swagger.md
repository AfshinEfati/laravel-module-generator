---
title: مستندات Swagger مبتنی بر Route
description: تولید مستندات API Swagger خودکار از routes
---

# مستندات Swagger مبتنی بر Route

Laravel Module Generator شامل تولید مستندات API خودکار با استفاده از مشخصات Swagger/OpenAPI است.

## بررسی اجمالی

Routes تعریف‌شده در ماژول‌های شما به طور خودکار مستند می‌شوند با:

- مسیرهای endpoint و روش‌های HTTP
- پارامترهای درخواست و schemas بدن
- Schemas پاسخ
- نیاز‌های احراز هویت
- پاسخ‌های خطا

## تولید مستندات

### تولید خودکار

وقتی شما یک ماژول با مولد تولید می‌کنید، مستندات Swagger به طور خودکار ایجاد می‌شود:

```bash
php artisan make:module Blog --fields=title,content,author_id
```

### تولید دستی

```bash
php artisan generate:swagger
```

این تمام routes را اسکن می‌کند و مستندات Swagger را تولید/بروزرسانی می‌کند.

## نمایش مستندات

### رابط وب

مستندات Swagger را دسترسی دارید:

```bash
php artisan serve
# بازدید http://localhost:8000/api/documentation
```

### صادرات JSON/YAML

```bash
# صادرات به صورت JSON
php artisan swagger:export --format=json > swagger.json

# صادرات به صورت YAML
php artisan swagger:export --format=yaml > swagger.yaml
```

## سفارشی‌سازی

### اضافه کردن توضیحات

توضیحات را به اعمال controller خود اضافه کنید.

### Schemas مدل

مدل‌های خود را به عنوان schemas تعریف کنید.

## پیکربندی

تولید Swagger در `config/module-generator.php` پیکربندی می‌شود.

## بهترین تمرین‌ها

1. **Annotations را همگام نگه دارید** - هنگام تغییر routes بروزرسانی کنید
2. **از خلاصه‌های توصیفی استفاده کنید** - به کاربران API کمک کنید
3. **پاسخ‌های خطا را مستند کنید** - شامل نمونه‌های خطای validation
4. **نمونه‌ها را شامل کنید** - نمونه‌های درخواست/پاسخ واقعی را ارائه دهید
5. **API خود را نسخه‌بندی کنید** - نسخه را هنگام ایجاد تغییرات آسیب‌رسان بروزرسانی کنید
