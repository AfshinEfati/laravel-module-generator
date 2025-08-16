<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class StatusHelper
{
    public static function successResponse(mixed $data = null, string $message = 'success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function errorResponse(string $message = 'error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    public static function unauthorized(string $message = 'unauthorized'): JsonResponse
    {
        return self::errorResponse($message, 401);
    }

    public static function forbidden(string $message = 'forbidden'): JsonResponse
    {
        return self::errorResponse($message, 403);
    }

    public static function notFound(string $message = 'not found'): JsonResponse
    {
        return self::errorResponse($message, 404);
    }

    /**
     * Format date fields into a consistent structure.
     * Falls back gracefully if 'verta()' is not available.
     */
    public static function formatDates(?string $datetime): ?array
    {
        if (!$datetime) {
            return null;
        }

        try {
            $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        } catch (\Throwable $e) {
            return null;
        }

        $iso = $carbon->toIso8601String();
        $date = $carbon->toDateString();
        $time = $carbon->toTimeString();

        // Fallback when 'verta' is not installed
        $fa_date = $date;
        try {
            if (function_exists('verta')) {
                $fa_date = verta($carbon)->format('Y-m-d'); // customize if needed
            }
        } catch (\Throwable $e) {
            // keep fallback
        }

        return [
            'date'    => $date,
            'time'    => $time,
            'fa_date' => $fa_date,
            'iso'     => $iso,
        ];
    }

    /**
     * Return normalized status object for booleans.
     */
    public static function getStatus(bool $value): array
    {
        return [
            'name'    => $value ? 'active' : 'inactive',
            'fa_name' => $value ? 'فعال' : 'غیرفعال',
            'code'    => $value ? 1 : 0,
        ];
    }

    /**
     * Domain example: cabin type mapping.
     */
    public static function getCabinType(?string $type): ?array
    {
        if (!$type) return null;

        $map = [
            'Y' => ['name' => 'economy', 'fa_name' => 'اکونومی', 'code' => 'Y'],
            'W' => ['name' => 'premium_economy', 'fa_name' => 'اکونومی پریمیوم', 'code' => 'W'],
            'C' => ['name' => 'business', 'fa_name' => 'بیزینس', 'code' => 'C'],
            'F' => ['name' => 'first', 'fa_name' => 'فرست', 'code' => 'F'],
        ];

        return $map[$type] ?? ['name' => strtolower($type), 'fa_name' => $type, 'code' => $type];
    }
}
