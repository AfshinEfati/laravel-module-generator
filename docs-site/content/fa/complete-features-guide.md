---
title: راهنمای ویژگی‌های کامل
description: راهنمای عمقی برای تمام ویژگی‌های Laravel Module Generator
---

# راهنمای ویژگی‌های کامل

بررسی جامع تمام ویژگی‌های موجود در Laravel Module Generator.

## ویژگی‌های اصلی

### 1. تولید معماری مدولار

به طور خودکار ساختار مدولار خوب‌سازماندهی شده را طبق اصول معماری تمیز تولید کنید.

- تنظیم namespace خودکار
- سازماندهی پوشه درست
- اجرای service layer
- پشتیبانی from pattern repository
- تولید DTO layer

### 2. توسعه Database-First

ساختار ماژول خود را از طریق فیلدهای database تعریف کنید:

```bash
php artisan make:module Article --fields=title:string,content:text,author_id:foreign,published:boolean
```

ویژگی‌ها:

- تولید migration
- ایجاد Factory
- پشتیبانی Seeder
- مدیریت Relationship

### 3. تولید کد Schema-Aware

مولد فیلدهای تعریف‌شده شما را تجزیه می‌کند و validation، casting و serialization مناسب را ایجاد می‌کند.

### 4. Request Validation

Request forms به طور خودکار تولیدشده با validation هوشمند.

### 5. API Resources

به طور خودکار کلاس‌های resource برای پاسخ‌های API ایجاد می‌کند.

### 6. Service Layer

کلاس‌های service تمیز برای منطق تجاری.

### 7. Repository Pattern

انتزاع لایه دسترسی داده‌ها.

### 8. Data Transfer Objects (DTOs)

container‌های داده محفوظ از نوع:

### 9. تست خودکار

پایه‌های test تولیدشده.

### 10. مستندات API

تولید مستندات Swagger/OpenAPI شامل است.

## ویژگی‌های پیشرفته

### پشتیبانی تاریخ جلالی

تبدیل یکپارچه میلادی/جلالی با trait `HasGoliDates`.

### Action Layer

کلاس‌های action اضافی برای عملیات پیچیده.

### تولید Policy

سیاست‌های مجوزدهی برای منابع ماژول.

### Web UI

رابط وب interactive برای مولد ماژول.

## بهترین تمرین‌ها

1. **فیلدها را به وضوح تعریف کنید** - از نوع‌های فیلد خاص برای تولید بهتر استفاده کنید
2. **از DTOs استفاده کنید** - برای ایمنی نوع از DTOs تولیدشده استفاده کنید
3. **تست‌ها را بنویسید** - از پایه‌های test تولیدشده به عنوان نقطه شروع استفاده کنید
4. **APIها را مستند کنید** - تولید Swagger در مستندات API کمک می‌کند
5. **Stubها را سفارشی کنید** - Stubs را منتشر و اصلاح کنید برای معیارهای پروژه
