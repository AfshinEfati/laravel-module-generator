# Jalali Support

This package comes with built-in support for the Jalali calendar, which is the official calendar of Iran and Afghanistan. This support is provided by the `goli()` helper function and the `Goli` class.

## The `goli()` Helper

The `goli()` helper function is a convenient way to create a new `Goli` instance from a Carbon instance or a date string.

```php
$goli = goli(now());
```

You can also create a `Goli` instance from a date string:

```php
$goli = goli('2024-05-01 12:00:00');
```

## The `Goli` Class

The `Goli` class provides a variety of methods for working with Jalali dates. Here are a few examples:

### Formatting Dates

You can format a `Goli` instance as a string using the `toGoliDateString()` and `toGoliDateTimeString()` methods.

```php
$goli = goli(now());

$dateString = $goli->toGoliDateString(); // 1403-02-12
$dateTimeString = $goli->toGoliDateTimeString(); // 1403-02-12 12:00:00
```

### Parsing Dates

You can parse a Jalali date string into a `Goli` instance using the `parseGoli()` method.

```php
$goli = Goli::parseGoli('1403-02-12 12:00:00');
```

### Converting to Carbon

You can convert a `Goli` instance to a Carbon instance using the `toCarbon()` method.

```php
$carbon = $goli->toCarbon();
```

## API Response Helper

The `ApiResponseHelper` that is generated with your modules is also aware of the Jalali calendar. It will automatically convert any Carbon instances in your API responses to Jalali date strings. This means that you don't have to worry about converting dates manually in your API resources.