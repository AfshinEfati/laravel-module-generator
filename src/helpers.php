<?php

use Efati\ModuleGenerator\Support\Verta;
use Carbon\Carbon;

if (!function_exists('verta')) {
    /**
     * Create a new Verta instance using the package implementation.
     *
     * @param  Verta|Carbon|\DateTimeInterface|int|string|array<int|string, mixed>|null  $datetime
     */
    function verta(
        Verta|Carbon|\DateTimeInterface|int|string|array|null $datetime = null,
        \DateTimeZone|string|null $timezone = null
    ): Verta {
        if ($datetime instanceof Verta && $timezone === null) {
            return $datetime;
        }

        return Verta::instance($datetime, $timezone);
    }
}
