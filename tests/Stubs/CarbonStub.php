<?php

namespace Carbon;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use JsonSerializable;

class Carbon extends DateTime implements JsonSerializable
{
    private static ?self $testNow = null;

    public static function setTestNow(DateTimeInterface|self|null $testNow = null): void
    {
        if ($testNow === null) {
            self::$testNow = null;

            return;
        }

        if ($testNow instanceof self) {
            self::$testNow = $testNow->copy();

            return;
        }

        self::$testNow = self::instance($testNow);
    }

    public static function now(DateTimeZone|string|null $timezone = null): static
    {
        $timezone = self::normalizeTimezone($timezone);

        if (self::$testNow !== null) {
            $now = self::$testNow->copy();

            if ($timezone !== null) {
                $now->setTimezone($timezone);
            }

            return $now;
        }

        return new static('now', $timezone);
    }

    public static function parse(string $time = 'now', DateTimeZone|string|null $timezone = null): static
    {
        return new static($time, self::normalizeTimezone($timezone));
    }

    public static function create(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        DateTimeZone|string|null $timezone = null
    ): static {
        $timezone = self::normalizeTimezone($timezone);
        $date = new static('now', $timezone);
        $date->setDate($year, $month, $day);
        $date->setTime($hour, $minute, $second);

        return $date;
    }

    public static function createFromTimestamp(int $timestamp, DateTimeZone|string|null $timezone = null): static
    {
        $timezone = self::normalizeTimezone($timezone);
        $date = new static('@' . $timestamp);

        if ($timezone !== null) {
            $date->setTimezone($timezone);
        }

        return $date;
    }

    public static function instance(DateTimeInterface $dateTime): static
    {
        $instance = new static('@' . $dateTime->getTimestamp());
        $instance->setTimezone($dateTime->getTimezone());
        $instance->setTime(
            (int) $dateTime->format('H'),
            (int) $dateTime->format('i'),
            (int) $dateTime->format('s')
        );

        return $instance;
    }

    public function copy(): static
    {
        return clone $this;
    }

    public function setTimezone($timezone): static
    {
        parent::setTimezone(self::normalizeTimezone($timezone));

        return $this;
    }

    public function setTimestamp($timestamp): static
    {
        parent::setTimestamp($timestamp);

        return $this;
    }

    public function addDays(int $days): static
    {
        if ($days !== 0) {
            $this->modify(($days > 0 ? '+' : '') . $days . ' day');
        }

        return $this;
    }

    public function subDays(int $days): static
    {
        return $this->addDays(-$days);
    }

    public function diffInYears(DateTimeInterface $date, bool $absolute = true): int
    {
        $interval = $this->diff($date, false);
        $years = $interval->y;

        return $absolute ? $years : ($interval->invert === 1 ? -$years : $years);
    }

    public function diffInMonths(DateTimeInterface $date, bool $absolute = true): int
    {
        $interval = $this->diff($date, false);
        $months = $interval->y * 12 + $interval->m;

        return $absolute ? $months : ($interval->invert === 1 ? -$months : $months);
    }

    public function diffInDays(DateTimeInterface $date, bool $absolute = true): int
    {
        $days = (int) $this->diff($date, false)->format('%r%a');

        return $absolute ? abs($days) : $days;
    }

    public function diffInHours(DateTimeInterface $date, bool $absolute = true): int
    {
        $seconds = $this->diffInSeconds($date, false);
        $hours = (int) floor(abs($seconds) / 3600);

        return $absolute ? $hours : ($seconds >= 0 ? $hours : -$hours);
    }

    public function diffInMinutes(DateTimeInterface $date, bool $absolute = true): int
    {
        $seconds = $this->diffInSeconds($date, false);
        $minutes = (int) floor(abs($seconds) / 60);

        return $absolute ? $minutes : ($seconds >= 0 ? $minutes : -$minutes);
    }

    public function diffInSeconds(DateTimeInterface $date, bool $absolute = true): int
    {
        $seconds = $this->getTimestamp() - $date->getTimestamp();

        return $absolute ? abs($seconds) : $seconds;
    }

    public function toDateTimeString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function toIso8601String(): string
    {
        return $this->format('c');
    }

    public function jsonSerialize(): mixed
    {
        return $this->format(DateTimeInterface::ATOM);
    }

    public function __get(string $name): mixed
    {
        if ($name === 'dayOfWeek') {
            return (int) $this->format('w');
        }

        throw new \RuntimeException("Property {$name} is not supported in the Carbon stub.");
    }

    private static function normalizeTimezone(DateTimeZone|string|null $timezone): ?DateTimeZone
    {
        if ($timezone === null || $timezone instanceof DateTimeZone) {
            return $timezone;
        }

        return new DateTimeZone((string) $timezone);
    }
}
