# نحوهٔ استفاده

<div dir="rtl" markdown="1">

[🇬🇧 English](../en/usage.md){ .language-switcher }


در این بخش ترکیب‌های پرکاربرد دستور و جریان‌های کاری روزمره را می‌بینید. برای مشاهدهٔ خروجی کامل یک ماژول واقعی، صفحهٔ [آناتومی ماژول Product](module-anatomy.md) را ببینید.

## نمای کلی دستور

```bash
php artisan make:module {Name}
  {--api}
  {--requests}
  {--actions}
  {--dto}
  {--resource}
  {--tests}
  {--policy}
  {--force}
  {--swagger}
  {--no-swagger}
  {--fields=}
  {--from-migration=}
```

برای مشاهدهٔ توضیحات کامل فلگ‌ها `php artisan make:module --help` را اجرا کنید.

### فعال‌سازی مستندات Swagger

فلگ‌های `--api` و `--swagger` را کنار هم به کار ببرید تا برای هر اکشن CRUD داکیومنت OpenAPI (`@OA`) بسازید. خروجی در قالب کلاس `App\Docs\{Module}Doc` (به‌طور پیش‌فرض در مسیر `app/Docs`؛ قابل‌تغییر با `paths.docs`) ایجاد می‌شود تا کنترلر تمیز بماند و اگر بستهٔ Swagger نصب نباشد یک هشدار دریافت می‌کنید و عملیات بدون خطا ادامه می‌یابد. پیش از استفاده، یکی از بسته‌های `darkaonline/l5-swagger` یا `zircote/swagger-php` را نصب کنید.

## ساخت ماژول REST

```bash
php artisan make:module Invoice \
  --api --actions --dto --resource --requests --tests \
  --fields="number:string:unique, issued_at:date, total:decimal(12,2)"
```

خروجی این دستور:

- کنترلر API با اکشن‌های کامل CRUD.
- لایهٔ اکشن که هر مورد استفاده (List/Show/Create/Update/Delete) را در کلاس‌های مجزا اجرا می‌کند و در صورت خطا، اطلاعات کامل exception را در لاگ ثبت می‌کند.
- فرم‌ریکوئست‌هایی که بر اساس اسکیما تعریف‌شده اعتبارسنجی می‌کنند.
- کلاس‌های DTO و ریسورس با متادیتای مشترک.
- تست فیچری برای سناریوهای موفق و شکست اعتبارسنجی.
- فرم‌ریکوئست‌ها در مسیر `App\Http\Requests\Invoice\` ساخته می‌شوند تا ساختار پروژه‌های بزرگ مرتب بماند.

```php
public function show(Invoice $invoice): mixed
{
    $model = ($this->showAction)($invoice->getKey());
    if (!$model) {
        return ApiResponseHelper::errorResponse('not found', 404);
    }

    $model->load(['customer', 'lines']);

    return ApiResponseHelper::successResponse(new InvoiceResource($model), 'success');
}
```

> در صورت تمایل می‌توانید با فلگ `--no-actions` این لایه را حذف کنید و کنترلر مستقیماً سرویس را صدا بزند.

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
