<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTimeInterface;
use Efati\ModuleGenerator\Support\Goli;
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
     * Format date fields into a consistent structure using the built-in Goli helper.
     * Falls back gracefully if parsing fails.
     */
    public static function formatDates(
        Carbon|DateTimeInterface|Goli|int|string|null $datetime
    ): ?array {
        if ($datetime === null) {
            return null;
        }

        if (is_string($datetime) && trim($datetime) === '') {
            return null;
        }

        try {
            $jalali = $datetime instanceof Goli ? $datetime : Goli::instance($datetime);
            $carbon = $jalali->toCarbon();
        } catch (\Throwable $e) {
            try {
                if ($datetime instanceof Carbon) {
                    $carbon = $datetime->copy();
                } elseif ($datetime instanceof DateTimeInterface) {
                    $carbon = Carbon::instance($datetime);
                } elseif (is_int($datetime)) {
                    $carbon = Carbon::createFromTimestamp($datetime);
                } else {
                    $carbon = Carbon::parse((string) $datetime);
                }

                $jalali = Goli::instance($carbon);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return [
            'date'    => $carbon->toDateString(),
            'time'    => $carbon->toTimeString(),
            'fa_date' => $jalali->format('Y-m-d', true),
            'iso'     => $carbon->toIso8601String(),
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
