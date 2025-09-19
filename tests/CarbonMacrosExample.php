<?php

declare(strict_types=1);

use Carbon\Carbon;
use Efati\ModuleGenerator\ModuleGeneratorServiceProvider;

require __DIR__ . '/../vendor/autoload.php';

if (! class_exists(Carbon::class)) {
    fwrite(STDERR, "Carbon is not installed; skipping Jalali macro example.\n");
    return;
}

ModuleGeneratorServiceProvider::registerCarbonMacros();

$now = Carbon::create(2024, 3, 20, 12, 0, 0, 'UTC');
$jalali = $now->toJalali()->format('Y/m/d H:i:s');

if ($jalali !== '1403/01/01 12:00:00') {
    throw new RuntimeException('Unexpected Jalali conversion result: ' . $jalali);
}

$gregorian = Carbon::fromJalali('1403-01-01 12:00:00', 'UTC');
$formatted = $gregorian->format('Y-m-d H:i:s');

if ($formatted !== '2024-03-20 12:00:00') {
    throw new RuntimeException('Unexpected Gregorian conversion result: ' . $formatted);
}

echo "Carbon Jalali macros are working as expected.\n";
