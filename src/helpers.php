<?php

use Carbon\Carbon;
use Efati\ModuleGenerator\Support\Goli;

if (!function_exists('goli')) {
    /**
     * Create a new Goli instance using the package implementation.
     *
     * @param  Goli|Carbon|\DateTimeInterface|int|string|array<int|string, mixed>|null  $datetime
     */
    function goli(
        Goli|Carbon|\DateTimeInterface|int|string|array|null $datetime = null,
        \DateTimeZone|string|null $timezone = null
    ): Goli {
        if ($datetime instanceof Goli && $timezone === null) {
            return $datetime;
        }

        return Goli::instance($datetime, $timezone);
    }
}

if (!function_exists('goli_date')) {
    /**
     * Alias for goli() to improve readability when formatting dates.
     *
     * @param  Goli|Carbon|\DateTimeInterface|int|string|array<int|string, mixed>|null  $datetime
     */
    function goli_date(
        Goli|Carbon|\DateTimeInterface|int|string|array|null $datetime = null,
        \DateTimeZone|string|null $timezone = null
    ): Goli {
        return goli($datetime, $timezone);
    }
}
