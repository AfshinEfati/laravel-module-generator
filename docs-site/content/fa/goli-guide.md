# راهنمای هِلپر تاریخ Goli

<div dir="rtl" markdown="1">

[🇬🇧 English](../en/goli-guide.md){ .language-switcher }

کلاس `Efati\ModuleGenerator\Support\Goli` همراه پکیج ارائه می‌شود تا بدون وابستگی اضافی با تاریخ‌های جلالی کار کنید. این صفحه روش ساخت نمونه‌ها، قالب‌بندی خروجی و ترکیب آن با `ApiResponseHelper` را پوشش می‌دهد.

## ساخت نمونه‌ها

```php
use Efati\ModuleGenerator\Support\Goli;

$fromCarbon = Goli::parse(now());
$fromTimestamp = Goli::fromTimestamp(time(), 'Asia/Tehran');
$fromJalali = Goli::parseGoli('1404-06-01 18:45:00', 'Asia/Tehran');
$fromComponents = Goli::create(1404, 6, 1, 18, 45, 0);

$tomorrow = $fromJalali->copy()->addDays(1);
```

- `parse()` هر چیزی را که Carbon می‌شناسد می‌پذیرد (نمونهٔ Carbon، عدد، رشتهٔ ISO یا یک نمونهٔ دیگر از Goli).
- `parseGoli()` رشته‌های جلالی را به تاریخ میلادی تبدیل می‌کند.
- `create()` فیلدهای سال/ماه/روز شمسی را گرفته و به صورت داخلی با `goliToGregorian()` تبدیل می‌کند.

!!! نکته
    هِلپر سراسری `goli()` معادل `Goli::instance()` است؛ بنابراین در هر جای برنامه می‌توانید از آن استفاده کنید.

## قالب‌بندی خروجی

```php
$goli = Goli::parseGoli('1404-04-15 09:30:00');

$goli->toGoliDateString();          // "1404-04-15"
$goli->toGoliDateString(true);      // "۱۴۰۴-۰۴-۱۵"
$goli->toGoliDateTimeString(true);  // "۱۴۰۴-۰۴-۱۵ ۰۹:۳۰:۰۰"
$goli->format('Y/m/d H:i');         // "1404/04/15 09:30"
$goli->formatGregorian('c');        // "2025-07-06T09:30:00+04:30"
$goli->toGoliArray(true);
```

تبدیل رقم‌ها نیز در دسترس است:

```php
Goli::persianNumbers('Price 123'); // "Price ۱۲۳"
Goli::latinNumbers('۰۹:۳۰');       // "09:30"
```

## کار با Carbon

```php
$goli = Goli::parse('2025-07-06 09:30:00', 'Asia/Tehran');
$carbon = $goli->toCarbon();

$carbon->addMonths(1);
$goli->setTimestamp($carbon->getTimestamp());
```

متد `diffForHumans()` نیز در دسترس است و می‌توانید خروجی فارسی یا انگلیسی بگیرید:

```php
$ago = Goli::parse(now()->subMinutes(42));
$ago->diffForHumans();          // "42 دقیقه پیش"
$ago->diffForHumans(null, true); // "۴۲ دقیقه پیش"
```

## هم‌نشینی با ApiResponseHelper

```php
return [
    'name' => $this->name,
    'released_at' => ApiResponseHelper::formatDates($this->released_at),
    'status' => ApiResponseHelper::getStatus((bool) $this->is_active),
];
```

خروجی نمونه:

```json
{
  "date": "2025-08-15",
  "time": "10:30:00",
  "fa_date": "۱۴۰۴-۰۵-۲۴",
  "iso": "2025-08-15T10:30:00+04:30"
}
```

## راهنمای مهاجرت به v7.1

- متد `parseJalali` به `parseGoli` تغییر نام داده است.
- سایر متدها نیز همین الگو را دنبال می‌کنند (`setGoliDate`، `toGoliDateString`، `goliToGregorian` و ...).
- نام کلاس Carbon در داخل فایل به `CarbonDate` تغییر کرده تا تداخلی با `use Carbon\Carbon` در پروژهٔ شما ایجاد نشود.

بنابراین کافی است فراخوانی‌های قدیمی را در پروژهٔ خود جست‌وجو کرده و با نام‌های جدید جایگزین کنید.

</div>
