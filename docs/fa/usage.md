# نحوهٔ استفاده

<div dir="rtl" markdown="1">

[🇬🇧 English](../en/usage.md){ .language-switcher }

در این بخش ترکیب‌های پرکاربرد دستور و جریان‌های کاری روزمره را می‌بینید.

## نمای کلی دستور

```bash
php artisan make:module {Name}
  {--api}
  {--requests}
  {--dto}
  {--resource}
  {--tests}
  {--policy}
  {--force}
  {--fields=}
  {--from-migration=}
```

برای مشاهدهٔ توضیحات کامل فلگ‌ها `php artisan make:module --help` را اجرا کنید.

## ساخت ماژول REST

```bash
php artisan make:module Invoice \
  --api --dto --resource --requests --tests \
  --fields="number:string:unique, issued_at:date, total:decimal(12,2)"
```

خروجی این دستور:

- کنترلر API با اکشن‌های کامل CRUD.
- فرم‌ریکوئست‌هایی که بر اساس اسکیما تعریف‌شده اعتبارسنجی می‌کنند.
- کلاس‌های DTO و ریسورس با متادیتای مشترک.
- تست فیچری برای سناریوهای موفق و شکست اعتبارسنجی.

## استفاده از مایگریشن به‌عنوان منبع اصلی

```bash
php artisan make:module Invoice \
  --api --requests --tests \
  --from-migration=database/migrations/2024_05_01_000001_create_invoices_table.php
```

با اشاره به مسیر مایگریشن، ژنراتور انواع ستون، مقادیر پیش‌فرض، nullable، کلیدهای خارجی و توضیحات را استخراج کرده و در DTO، ریسورس و قوانین اعتبارسنجی استفاده می‌کند.

## ماژول‌های صرفاً DTO

اگر فقط به ساختار داده نیاز دارید، فلگ API را حذف و ریسورس/تست را غیرفعال کنید.

```bash
php artisan make:module Money --dto --fields="amount:decimal(8,2), currency:string:3"
```

این دستور کلاس DTO و کارخانهٔ مربوط را تولید می‌کند تا در سایر سرویس‌ها استفاده شود.

## تولید دوباره پس از تغییرات

قالب‌ها قابل شخصی‌سازی هستند؛ بنابراین هنگام افزودن فیلد جدید می‌توانید با فلگ `--force` دستور را دوباره اجرا کنید. ژنراتور فایل‌های موجود را فقط در صورت نیاز بازنویسی می‌کند.

```bash
php artisan make:module Invoice --fields="number:string, total:decimal(12,2), due_at:date" --force
```

اگر فایل‌ها را به‌صورت دستی ویرایش کرده‌اید قبل از کامیت خروجی `git diff` را بررسی کنید.

## تست

تست‌های تولیدشده از تنظیمات پایگاه‌داده در `.env` استفاده می‌کنند. برای اطمینان از سلامت ماژول آن‌ها را محلی اجرا کنید.

```bash
phpunit --testsuite=Feature
```

برای یادگیری شخصی‌سازی پیشرفته به [راهنمای پیشرفته](advanced.md) مراجعه کنید.

</div>
