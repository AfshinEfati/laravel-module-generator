<?php

namespace Efati\ModuleGenerator\Support;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Lightweight Jalali date helper inspired by hekmatinasser/verta.
 *
 * Provides conversion utilities between Jalali and Gregorian calendars,
 * date formatting helpers, Jalali-aware parsing (including Persian digits),
 * and interoperability with Carbon.
 */
class Goli implements JsonSerializable
{
    /**
     * Full month names in Persian.
     *
     * @var array<int, string>
     */
    public const MONTH_NAMES = [
        1  => 'فروردین',
        2  => 'اردیبهشت',
        3  => 'خرداد',
        4  => 'تیر',
        5  => 'مرداد',
        6  => 'شهریور',
        7  => 'مهر',
        8  => 'آبان',
        9  => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    /**
     * Short month names in Persian.
     *
     * @var array<int, string>
     */
    public const SHORT_MONTH_NAMES = [
        1  => 'فرو',
        2  => 'ارد',
        3  => 'خرد',
        4  => 'تیر',
        5  => 'مرد',
        6  => 'شهر',
        7  => 'مهر',
        8  => 'آبا',
        9  => 'آذر',
        10 => 'دی',
        11 => 'بهم',
        12 => 'اسف',
    ];

    /**
     * Day names in Persian (Carbon::dayOfWeek order, Sunday first).
     *
     * @var array<int, string>
     */
    public const DAY_NAMES = [
        0 => 'یکشنبه',
        1 => 'دوشنبه',
        2 => 'سه‌شنبه',
        3 => 'چهارشنبه',
        4 => 'پنجشنبه',
        5 => 'جمعه',
        6 => 'شنبه',
    ];

    /**
     * Short day names in Persian (Carbon::dayOfWeek order, Sunday first).
     *
     * @var array<int, string>
     */
    public const SHORT_DAY_NAMES = [
        0 => 'یک',
        1 => 'دو',
        2 => 'سه',
        3 => 'چه',
        4 => 'پن',
        5 => 'جم',
        6 => 'شن',
    ];

    /**
     * Mapping latin to persian numbers.
     */
    private const LATIN_TO_PERSIAN_DIGITS = [
        '0' => '۰',
        '1' => '۱',
        '2' => '۲',
        '3' => '۳',
        '4' => '۴',
        '5' => '۵',
        '6' => '۶',
        '7' => '۷',
        '8' => '۸',
        '9' => '۹',
    ];

    /**
     * Mapping persian and arabic digits back to latin numbers.
     */
    private const PERSIAN_TO_LATIN_DIGITS = [
        '۰' => '0', '٠' => '0',
        '۱' => '1', '١' => '1',
        '۲' => '2', '٢' => '2',
        '۳' => '3', '٣' => '3',
        '۴' => '4', '٤' => '4',
        '۵' => '5', '٥' => '5',
        '۶' => '6', '٦' => '6',
        '۷' => '7', '٧' => '7',
        '۸' => '8', '٨' => '8',
        '۹' => '9', '٩' => '9',
    ];

    /**
     * Translation table for diffForHumans units.
     *
     * @var array<string, array<string, string>>
     */
    private const HUMAN_DIFF_UNITS = [
        'second' => ['singular' => 'ثانیه', 'plural' => 'ثانیه'],
        'minute' => ['singular' => 'دقیقه', 'plural' => 'دقیقه'],
        'hour'   => ['singular' => 'ساعت', 'plural' => 'ساعت'],
        'day'    => ['singular' => 'روز', 'plural' => 'روز'],
        'month'  => ['singular' => 'ماه', 'plural' => 'ماه'],
        'year'   => ['singular' => 'سال', 'plural' => 'سال'],
    ];

    /**
     * Direction labels for diffForHumans output.
     *
     * @var array<string, string>
     */
    private const HUMAN_DIFF_DIRECTIONS = [
        'past'   => 'پیش',
        'future' => 'بعد',
    ];

    private const HUMAN_DIFF_NOW = 'همین حالا';

    protected Carbon $datetime;

    /**
     * @param  Carbon|DateTimeInterface|int|string|array<int|string, mixed>|self|null  $datetime
     */
    public function __construct(
        Carbon|DateTimeInterface|int|string|array|self|null $datetime = null,
        DateTimeZone|string|null $timezone = null
    ) {
        $this->datetime = static::parseDateTime($datetime, $timezone);
    }

    public static function instance(
        Carbon|DateTimeInterface|int|string|array|self|null $datetime = null,
        DateTimeZone|string|null $timezone = null
    ): self {
        return new self($datetime, $timezone);
    }

    public static function now(DateTimeZone|string|null $timezone = null): self
    {
        return new self(Carbon::now(static::normalizeTimezone($timezone)));
    }

    public static function parse(
        Carbon|DateTimeInterface|int|string|array|self|null $datetime = null,
        DateTimeZone|string|null $timezone = null
    ): self {
        return new self($datetime, $timezone);
    }

    public static function parseJalali(string $datetime, DateTimeZone|string|null $timezone = null): self
    {
        $tz = static::normalizeTimezone($timezone);
        $normalized = static::latinNumbers($datetime);

        $carbon = static::tryParseJalaliString($normalized, $tz);

        if ($carbon === null) {
            throw new InvalidArgumentException('Unable to parse Jalali datetime string.');
        }

        return new self($carbon);
    }

    public static function create(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        DateTimeZone|string|null $timezone = null
    ): self {
        [$gy, $gm, $gd] = static::jalaliToGregorian($year, $month, $day);

        return new self(Carbon::create($gy, $gm, $gd, $hour, $minute, $second, static::normalizeTimezone($timezone)));
    }

    public static function fromTimestamp(int $timestamp, DateTimeZone|string|null $timezone = null): self
    {
        return new self(Carbon::createFromTimestamp($timestamp, static::normalizeTimezone($timezone)));
    }

    public function copy(): self
    {
        return new self($this->datetime->copy());
    }

    public function toCarbon(): Carbon
    {
        return $this->datetime->copy();
    }

    public function timezone(DateTimeZone|string|null $timezone = null): DateTimeZone|string|null|self
    {
        if ($timezone === null) {
            return $this->datetime->getTimezone();
        }

        $this->datetime->setTimezone(static::normalizeTimezone($timezone));

        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->datetime->getTimestamp();
    }

    public function setTimestamp(int $timestamp): self
    {
        $this->datetime->setTimestamp($timestamp);

        return $this;
    }

    public function setJalaliDate(int $year, int $month, int $day): self
    {
        [$gy, $gm, $gd] = static::jalaliToGregorian($year, $month, $day);

        $this->datetime->setDate($gy, $gm, $gd);

        return $this;
    }

    public function setJalaliDateTime(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0
    ): self {
        [$gy, $gm, $gd] = static::jalaliToGregorian($year, $month, $day);

        $this->datetime->setDate($gy, $gm, $gd);
        $this->datetime->setTime($hour, $minute, $second);

        return $this;
    }

    public function addDays(int $days): self
    {
        $this->datetime->addDays($days);

        return $this;
    }

    public function subDays(int $days): self
    {
        $this->datetime->subDays($days);

        return $this;
    }

    /**
     * Format Jalali representation.
     */
    public function format(string $format, bool $convertNumbers = false): string
    {
        $jalali = $this->getJalaliDateParts();
        $result = '';
        $length = strlen($format);

        for ($i = 0; $i < $length; $i++) {
            $char = $format[$i];

            if ($char === '\\') {
                $result .= $format[++$i] ?? '';
                continue;
            }

            $result .= $this->formatToken($char, $jalali);
        }

        return $convertNumbers ? static::persianNumbers($result) : $result;
    }

    public function formatGregorian(string $format): string
    {
        return $this->datetime->format($format);
    }

    public function toJalaliDateString(bool $convertNumbers = false): string
    {
        return $this->format('Y-m-d', $convertNumbers);
    }

    public function toJalaliDateTimeString(bool $convertNumbers = false): string
    {
        return $this->format('Y-m-d H:i:s', $convertNumbers);
    }

    public function toJalaliArray(bool $withTime = false): array
    {
        $jalali = $this->getJalaliDateParts();

        if ($withTime) {
            $jalali['hour'] = (int) $this->datetime->format('H');
            $jalali['minute'] = (int) $this->datetime->format('i');
            $jalali['second'] = (int) $this->datetime->format('s');
        }

        return $jalali;
    }

    public function toDateTimeString(): string
    {
        return $this->datetime->toDateTimeString();
    }

    public function toIso8601String(): string
    {
        return $this->datetime->toIso8601String();
    }

    public function diffForHumans(?Carbon $other = null, bool $persianDigits = false): string
    {
        $reference = $other ? $other->copy() : Carbon::now($this->datetime->getTimezone());

        if ($other !== null) {
            $reference->setTimezone($this->datetime->getTimezone());
        }

        $diffInSeconds = $this->datetime->getTimestamp() - $reference->getTimestamp();

        if ($diffInSeconds === 0) {
            $result = self::HUMAN_DIFF_NOW;

            return $persianDigits ? static::persianNumbers($result) : $result;
        }

        $comparison = $reference;

        if (($years = $this->datetime->diffInYears($comparison, true)) > 0) {
            $unit = 'year';
            $value = $years;
        } elseif (($months = $this->datetime->diffInMonths($comparison, true)) > 0) {
            $unit = 'month';
            $value = $months;
        } elseif (($days = $this->datetime->diffInDays($comparison, true)) > 0) {
            $unit = 'day';
            $value = $days;
        } elseif (($hours = $this->datetime->diffInHours($comparison, true)) > 0) {
            $unit = 'hour';
            $value = $hours;
        } elseif (($minutes = $this->datetime->diffInMinutes($comparison, true)) > 0) {
            $unit = 'minute';
            $value = $minutes;
        } else {
            $unit = 'second';
            $value = max(1, $this->datetime->diffInSeconds($comparison, true));
        }

        $directionKey = $diffInSeconds > 0 ? 'future' : 'past';
        $forms = self::HUMAN_DIFF_UNITS[$unit];
        $unitLabel = $value === 1 ? $forms['singular'] : ($forms['plural'] ?? $forms['singular']);

        $result = sprintf('%d %s %s', $value, $unitLabel, self::HUMAN_DIFF_DIRECTIONS[$directionKey]);

        return $persianDigits ? static::persianNumbers($result) : $result;
    }

    public function jsonSerialize(): mixed
    {
        return $this->datetime->jsonSerialize();
    }

    public function __toString(): string
    {
        return $this->toIso8601String();
    }

    public function __get(string $name): mixed
    {
        if ($name === 'year') {
            return $this->getJalaliDateParts()['year'];
        }

        if ($name === 'month') {
            return $this->getJalaliDateParts()['month'];
        }

        if ($name === 'day') {
            return $this->getJalaliDateParts()['day'];
        }

        return $this->datetime->$name;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $result = $this->datetime->$name(...$arguments);

        if ($result instanceof Carbon) {
            return new self($result);
        }

        return $result;
    }

    public static function persianNumbers(string $value): string
    {
        return strtr($value, self::LATIN_TO_PERSIAN_DIGITS);
    }

    public static function latinNumbers(string $value): string
    {
        return strtr($value, self::PERSIAN_TO_LATIN_DIGITS);
    }

    public static function jalaliToGregorian(int $year, int $month, int $day): array
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            throw new InvalidArgumentException('Invalid Jalali date provided.');
        }

        $jy = $year - 979;
        $jm = $month - 1;
        $jd = $day - 1;

        $jDayNo = 365 * $jy + intdiv($jy, 33) * 8 + intdiv(($jy % 33 + 3), 4);
        for ($i = 0; $i < $jm; $i++) {
            $jDayNo += ($i < 6) ? 31 : 30;
        }
        $jDayNo += $jd;

        $gDayNo = $jDayNo + 79;

        $gy = 1600 + 400 * intdiv($gDayNo, 146097);
        $gDayNo %= 146097;

        $leap = true;
        if ($gDayNo >= 36525) {
            $gDayNo--;
            $gy += 100 * intdiv($gDayNo, 36524);
            $gDayNo %= 36524;

            if ($gDayNo >= 365) {
                $gDayNo++;
            } else {
                $leap = false;
            }
        }

        $gy += 4 * intdiv($gDayNo, 1461);
        $gDayNo %= 1461;

        if ($gDayNo >= 366) {
            $leap = false;
            $gDayNo--;
            $gy += intdiv($gDayNo, 365);
            $gDayNo %= 365;
        }

        $gd = $gDayNo + 1;

        $gMonthDays = [0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $gm = 1;
        while ($gm <= 12 && $gd > $gMonthDays[$gm]) {
            $gd -= $gMonthDays[$gm];
            $gm++;
        }

        return [$gy, $gm, $gd];
    }

    public static function gregorianToJalali(int $year, int $month, int $day): array
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            throw new InvalidArgumentException('Invalid Gregorian date provided.');
        }

        $gMonthDays = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = $month > 2 ? $year + 1 : $year;
        $dayCount = 355666 + (365 * $year)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            + $day
            + $gMonthDays[$month - 1];

        $jy = -1595 + 33 * intdiv($dayCount, 12053);
        $dayCount %= 12053;

        $jy += 4 * intdiv($dayCount, 1461);
        $dayCount %= 1461;

        if ($dayCount > 365) {
            $jy += intdiv($dayCount - 1, 365);
            $dayCount = ($dayCount - 1) % 365;
        }

        if ($dayCount < 186) {
            $jm = 1 + intdiv($dayCount, 31);
            $jd = 1 + $dayCount % 31;
        } else {
            $jm = 7 + intdiv($dayCount - 186, 30);
            $jd = 1 + ($dayCount - 186) % 30;
        }

        return [$jy, $jm, $jd];
    }

    public static function isLeapJalaliYear(int $year): bool
    {
        $mod = $year % 33;
        return in_array($mod, [1, 5, 9, 13, 17, 22, 26, 30], true);
    }

    protected static function normalizeTimezone(DateTimeZone|string|null $timezone): DateTimeZone|string|null
    {
        if ($timezone === null || $timezone instanceof DateTimeZone) {
            return $timezone;
        }

        return new DateTimeZone($timezone);
    }

    /**
     * @return array{year:int, month:int, day:int}
     */
    protected function getJalaliDateParts(): array
    {
        [$year, $month, $day] = static::gregorianToJalali(
            (int) $this->datetime->format('Y'),
            (int) $this->datetime->format('n'),
            (int) $this->datetime->format('j')
        );

        return [
            'year'  => $year,
            'month' => $month,
            'day'   => $day,
        ];
    }

    protected function formatToken(string $token, array $jalali): string
    {
        return match ($token) {
            'Y' => str_pad((string) $jalali['year'], 4, '0', STR_PAD_LEFT),
            'y' => substr(str_pad((string) $jalali['year'], 4, '0', STR_PAD_LEFT), -2),
            'm' => str_pad((string) $jalali['month'], 2, '0', STR_PAD_LEFT),
            'n' => (string) $jalali['month'],
            'd' => str_pad((string) $jalali['day'], 2, '0', STR_PAD_LEFT),
            'j' => (string) $jalali['day'],
            'F' => self::MONTH_NAMES[$jalali['month']] ?? '',
            'M' => self::SHORT_MONTH_NAMES[$jalali['month']] ?? '',
            't' => (string) $this->jalaliMonthLength($jalali['year'], $jalali['month']),
            'L' => static::isLeapJalaliYear($jalali['year']) ? '1' : '0',
            'w' => (string) $this->datetime->dayOfWeek,
            'N' => (string) ($this->datetime->dayOfWeek === 0 ? 7 : $this->datetime->dayOfWeek),
            'D' => self::SHORT_DAY_NAMES[$this->datetime->dayOfWeek] ?? '',
            'l' => self::DAY_NAMES[$this->datetime->dayOfWeek] ?? '',
            'W' => $this->datetime->format('W'),
            'z' => (string) $this->jalaliDayOfYear($jalali['month'], $jalali['day']),
            'a' => strtolower($this->datetime->format('A')),
            'A' => $this->datetime->format('A'),
            'g' => $this->datetime->format('g'),
            'G' => $this->datetime->format('G'),
            'h' => $this->datetime->format('h'),
            'H' => $this->datetime->format('H'),
            'i' => $this->datetime->format('i'),
            's' => $this->datetime->format('s'),
            'u' => $this->datetime->format('u'),
            'U' => $this->datetime->format('U'),
            'O' => $this->datetime->format('O'),
            'P' => $this->datetime->format('P'),
            'T' => $this->datetime->format('T'),
            'c' => $this->datetime->format('c'),
            'r' => $this->datetime->format('r'),
            default => $this->datetime->format($token),
        };
    }

    protected function jalaliMonthLength(int $year, int $month): int
    {
        if ($month <= 6) {
            return 31;
        }

        if ($month <= 11) {
            return 30;
        }

        return static::isLeapJalaliYear($year) ? 30 : 29;
    }

    protected function jalaliDayOfYear(int $month, int $day): int
    {
        if ($month <= 6) {
            return ($month - 1) * 31 + ($day - 1);
        }

        return 6 * 31 + ($month - 7) * 30 + ($day - 1);
    }

    /**
     * @param  Carbon|DateTimeInterface|int|string|array<int|string, mixed>|self|null  $datetime
     */
    protected static function parseDateTime(
        Carbon|DateTimeInterface|int|string|array|self|null $datetime,
        DateTimeZone|string|null $timezone
    ): Carbon {
        $tz = static::normalizeTimezone($timezone);

        if ($datetime instanceof self) {
            $carbon = $datetime->toCarbon();
            return $tz === null ? $carbon : $carbon->setTimezone($tz);
        }

        if ($datetime instanceof Carbon) {
            $carbon = $datetime->copy();
            return $tz === null ? $carbon : $carbon->setTimezone($tz);
        }

        if ($datetime instanceof DateTimeInterface) {
            $carbon = Carbon::instance($datetime);
            return $tz === null ? $carbon : $carbon->setTimezone($tz);
        }

        if ($datetime === null) {
            return Carbon::now($tz);
        }

        if (is_int($datetime) || (is_string($datetime) && is_numeric($datetime))) {
            return Carbon::createFromTimestamp((int) $datetime, $tz);
        }

        if (is_array($datetime)) {
            return static::createFromJalaliArray($datetime, $tz);
        }

        if (is_string($datetime)) {
            $normalized = static::latinNumbers($datetime);

            if ($parsed = static::tryParseJalaliString($normalized, $tz)) {
                return $parsed;
            }

            return Carbon::parse($normalized, $tz);
        }

        return Carbon::parse((string) $datetime, $tz);
    }

    /**
     * @param  array<int|string, mixed>  $input
     */
    protected static function createFromJalaliArray(array $input, DateTimeZone|string|null $timezone): Carbon
    {
        $year = $input['year'] ?? $input['y'] ?? $input[0] ?? null;
        $month = $input['month'] ?? $input['m'] ?? $input[1] ?? 1;
        $day = $input['day'] ?? $input['d'] ?? $input[2] ?? 1;
        $hour = $input['hour'] ?? $input['h'] ?? $input[3] ?? 0;
        $minute = $input['minute'] ?? $input['i'] ?? $input[4] ?? 0;
        $second = $input['second'] ?? $input['s'] ?? $input[5] ?? 0;

        if ($year === null) {
            throw new InvalidArgumentException('Year is required when creating a Jalali date array.');
        }

        $year = (int) static::latinNumbers((string) $year);
        $month = (int) static::latinNumbers((string) $month);
        $day = (int) static::latinNumbers((string) $day);
        $hour = (int) static::latinNumbers((string) $hour);
        $minute = (int) static::latinNumbers((string) $minute);
        $second = (int) static::latinNumbers((string) $second);

        [$gy, $gm, $gd] = static::jalaliToGregorian($year, $month, $day);

        return Carbon::create($gy, $gm, $gd, $hour, $minute, $second, $timezone);
    }

    protected static function tryParseJalaliString(string $value, DateTimeZone|string|null $timezone): ?Carbon
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $pattern = '/^(?<year>\d{3,4})[\/\-.](?<month>\d{1,2})[\/\-.](?<day>\d{1,2})(?:[T\s]+(?<hour>\d{1,2})(?::(?<minute>\d{1,2})(?::(?<second>\d{1,2}))?)?(?:\s*(?<meridian>am|pm))?)?$/iu';

        if (!preg_match($pattern, $value, $matches)) {
            return null;
        }

        $year = (int) $matches['year'];

        // Gregorian years will be >= 1700 for most use cases; treat smaller years as Jalali.
        if ($year >= 1700) {
            return null;
        }

        $month = (int) $matches['month'];
        $day = (int) $matches['day'];
        $hour = isset($matches['hour']) ? (int) $matches['hour'] : 0;
        $minute = isset($matches['minute']) ? (int) $matches['minute'] : 0;
        $second = isset($matches['second']) ? (int) $matches['second'] : 0;

        if (!empty($matches['meridian'])) {
            $meridian = strtolower($matches['meridian']);

            if ($meridian === 'pm' && $hour < 12) {
                $hour += 12;
            }

            if ($meridian === 'am' && $hour === 12) {
                $hour = 0;
            }
        }

        [$gy, $gm, $gd] = static::jalaliToGregorian($year, $month, $day);

        return Carbon::create($gy, $gm, $gd, $hour, $minute, $second, $timezone);
    }
}
