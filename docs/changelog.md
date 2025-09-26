# Release Highlights

خلاصه‌ای از نسخه‌های کلیدی بسته «Laravel Module Generator».

## v6.2.4
- بهبود پردازش `--fields` و نگاشت به DTO، Resource و تست‌ها؛ پشتیبانی از nullable/unique و کلید خارجی درون‌خطی.【F:src/Support/SchemaParser.php†L9-L138】【F:src/Commands/MakeModuleCommand.php†L92-L138】
- استخراج متادیتای جداول، روابط و قوانین اعتبارسنجی از مایگریشن‌ها برای استفاده مشترک در تمام ژنراتورها.【F:src/Support/MigrationFieldParser.php†L9-L213】【F:src/Support/MigrationFieldParser.php†L214-L325】
- ثبت خودکار Service Provider در `bootstrap/providers.php` یا `config/app.php` بعد از ساخت ماژول.【F:src/Generators/ProviderGenerator.php†L37-L72】
- خروجی Resource با `StatusHelper` تاریخ و مقادیر بولی را نرمال می‌کند و روابط لود شده را به Resourceهای متناظر متصل می‌سازد.【F:src/Generators/ResourceGenerator.php†L77-L158】【F:src/Stubs/Helpers/StatusHelper.php†L1-L83】

## v6.2.0
- معرفی موتور تحلیل مایگریشن و اسکیمای درون‌خطی برای تولید اعتبارسنجی، DTO و تست‌ها پیش از آماده شدن مدل‌ها.【F:src/Commands/MakeModuleCommand.php†L47-L138】【F:src/Support/MigrationFieldParser.php†L9-L213】【F:src/Support/SchemaParser.php†L9-L138】
- افزودن `StatusHelper` و helper جلالی `goli()` برای یکپارچه‌سازی پاسخ‌های API و تاریخ‌ها در تمام خروجی‌ها.【F:src/Stubs/Helpers/StatusHelper.php†L1-L83】【F:src/ModuleGeneratorServiceProvider.php†L14-L53】
- ژنراتور تست CRUD با متادیتای کامل فیلدها و روابط برای سناریوهای موفق و ناموفق ایجاد می‌شود.【F:src/Generators/TestGenerator.php†L11-L157】

## v5.3
- اضافه شدن شورت‌کات‌های خط فرمان (`-a`, `-c`, `-r`, `-t` و ...).【F:src/Commands/MakeModuleCommand.php†L18-L174】
- ایمن‌سازی بازنویسی فایل‌ها: در صورت نبود `--force/-f` فایل‌های موجود رد می‌شوند.【F:src/Commands/MakeModuleCommand.php†L117-L174】
- انطباق کنترلرها با حالت API یا وب و بازگشت خروجی مناسب با توجه به فعال بودن DTO/Resource.【F:src/Generators/ControllerGenerator.php†L78-L167】【F:src/Generators/ControllerGenerator.php†L170-L248】

## v5.2
- افزودن تست‌های کامل CRUD با سناریوهای موفق و ناموفق.【F:src/Generators/TestGenerator.php†L11-L157】
- حذف اجبار استفاده از SQLite در تست‌ها و استفاده از تنظیمات پایگاه‌داده `.env`.【F:src/Generators/TestGenerator.php†L19-L44】
- بهبود یکپارچه‌سازی DTO در کنترلرها و سرویس‌ها.【F:src/Generators/ControllerGenerator.php†L78-L167】【F:src/Commands/MakeModuleCommand.php†L113-L150】

## v5.1
- رفع باگ‌های تولید Form Request و بهبود مدیریت مسیرها در پیکربندی.【F:src/Commands/MakeModuleCommand.php†L100-L151】【F:src/config/module-generator.php†L19-L53】

## v5.0
- بازنویسی عمده برای پشتیبانی از Laravel 11 و namespace پویا.【F:src/ModuleGeneratorServiceProvider.php†L14-L53】【F:src/config/module-generator.php†L5-L53】
- اتصال خودکار Service و Repository در Service Provider و تولید Provider اختصاصی.【F:src/Generators/ProviderGenerator.php†L9-L72】

برای تاریخچه کامل و جزئیات تغییرات، فایل‌های [`CHABELOG.md`](https://github.com/efati/laravel-module-generator/blob/main/CHABELOG.md) و [`version.md`](https://github.com/efati/laravel-module-generator/blob/main/version.md) را بررسی کنید.
