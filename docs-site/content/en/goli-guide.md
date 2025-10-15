# Goli date helper guide

[🇮🇷 فارسی](../fa/goli-guide.md){ .language-switcher }

`Efati\ModuleGenerator\Support\Goli` ships alongside the generator so you can work with Jalali dates without pulling in extra packages. This guide shows how to create instances, format values, and combine Goli with `ApiResponseHelper`.

## Creating instances

```php
use Efati\ModuleGenerator\Support\Goli;
use Carbon\CarbonInterval;

$fromCarbon = Goli::parse(now());
$fromTimestamp = Goli::fromTimestamp(time(), 'Asia/Tehran');
$fromJalali = Goli::parseGoli('1404-06-01 18:45:00', 'Asia/Tehran');
$fromComponents = Goli::create(1404, 6, 1, 18, 45, 0);

$oneDayLater = $fromJalali->copy()->addDays(1);
```

- `parse()` accepts anything Carbon understands (Carbon instances, integers, ISO strings, or even another `Goli`).
- `parseGoli()` converts Jalali strings with optional AM/PM markers into their Gregorian equivalent.
- `create()` accepts Jalali components directly; behind the scenes it converts them with `goliToGregorian()`.

!!! tip
    The global helper `goli()` resolves from the container and mirrors `Goli::instance()`, so you can write `goli('1404-01-01')` anywhere in your application.

## Formatting output

```php
$goli = Goli::parseGoli('1404-04-15 09:30:00');

$goli->toGoliDateString();          // "1404-04-15"
$goli->toGoliDateString(true);      // "۱۴۰۴-۰۴-۱۵"
$goli->toGoliDateTimeString(true);  // "۱۴۰۴-۰۴-۱۵ ۰۹:۳۰:۰۰"
$goli->format('Y/m/d H:i');         // "1404/04/15 09:30"
$goli->formatGregorian('c');        // "2025-07-06T09:30:00+04:30"
$goli->toGoliArray(true);
/*
[
    'year'   => 1404,
    'month'  => 4,
    'day'    => 15,
    'hour'   => 9,
    'minute' => 30,
    'second' => 0,
]
*/
```

Use `persianNumbers()` and `latinNumbers()` when you need to convert digits manually:

```php
Goli::persianNumbers('Price 123'); // "Price ۱۲۳"
Goli::latinNumbers('۰۹:۳۰');       // "09:30"
```

## Working with Carbon

`toCarbon()` returns a clone of the underlying `Carbon\Carbon` instance, so you can use familiar APIs when needed.

```php
$goli = Goli::parse('2025-07-06 09:30:00', 'Asia/Tehran');
$carbon = $goli->toCarbon();

$carbon->addMonths(1);
$goli->setTimestamp($carbon->getTimestamp());
```

`diffForHumans()` honours the active timezone and is available in Persian digits too:

```php
$goli = Goli::parse(now()->subMinutes(42));
$goli->diffForHumans();        // "42 دقیقه پیش"
$goli->diffForHumans(null, true); // "۴۲ دقیقه پیش"
```

## Integration with ApiResponseHelper

`ApiResponseHelper::formatDates()` already accepts `Goli` instances, Carbon objects, timestamps, ISO strings, and Jalali strings. A typical resource method looks like this:

```php
return [
    'name' => $this->name,
    'released_at' => ApiResponseHelper::formatDates($this->released_at),
    'status' => ApiResponseHelper::getStatus((bool) $this->is_active),
];
```

When the database column is stored as a Carbon date, the helper returns:

```json
{
  "date": "2025-08-15",
  "time": "10:30:00",
  "fa_date": "۱۴۰۴-۰۵-۲۴",
  "iso": "2025-08-15T10:30:00+04:30"
}
```

## Migration guide for v7.1

If you upgraded from v7.0 or earlier:

- `parseJalali()` has been renamed to `parseGoli()` for clarity.
- Other helpers follow the same pattern (`setGoliDate`, `toGoliDateString`, `goliToGregorian`, ...).
- `Carbon\Carbon` is now imported as `CarbonDate`; if you referenced the class directly, update the imports accordingly.

Search for `parseJalali`, `toJalaliDateString`, and similar names in your project and replace them with the new `Goli`-prefixed APIs.
