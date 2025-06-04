<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StatusHelper
{
    public static function getStatus(bool $status): array
    {
        return $status ? [
            'name' => 'active',
            'fa_name' => 'فعال',
            'code' => 1
        ] : [
            'name' => 'inActive',
            'fa_name' => 'غیرفعال',
            'code' => 0
        ];
    }

    public static function successResponse($data, $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    public static function errorResponse(string $message, int $code): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => $message
        ], $code);
    }

    public static function unauthorizedResponse(array $array, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $array
        ], Response::HTTP_UNAUTHORIZED);
    }

    public static function forbiddenResponse(array $array, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $array
        ], 403);
    }

    public static function notFoundResponse($data, string $message, int $code = 404): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    public static function formatDates(Carbon|string|null $date): array
    {
        if (!$date) return [];

        $date = Carbon::parse($date);
        return [
            'date' => $date->format('Y-m-d'),
            'time' => $date->format('H:i:s'),
            'fa_date' => verta($date)->format('Y-m-d'),
            'iso' => $date->toIso8601String(),
        ];
    }

    public static function getCabinType(string $cabinType): array
    {
        $cabinType = strtolower($cabinType);
        $cabinTypes = [
            'economy' => [
                'name' => 'economy',
                'fa_name' => 'اکونومی',
                'code' => 'Y',
                'number' => 1
            ],
            'premium economy' => [
                'name' => 'premium economy',
                'fa_name' => 'پریمیوم اکونومی',
                'code' => 'S',
                'number' => 2
            ],
            'business' => [
                'name' => 'business',
                'fa_name' => 'بیزینس',
                'code' => 'C',
                'number' => 3
            ],
            'premium business' => [
                'name' => 'premium business',
                'fa_name' => 'پریمیوم بیزینس',
                'code' => 'J',
                'number' => 4
            ],
            'first class' => [
                'name' => 'first class',
                'fa_name' => 'فرست کلس',
                'code' => 'F',
                'number' => 5
            ],
            'premium first class' => [
                'name' => 'premium first class',
                'fa_name' => 'پریمیوم فرست کلس',
                'code' => 'P',
                'number' => 6
            ],
        ];

        return $cabinTypes[$cabinType] ?? $cabinTypes['economy'];
    }
}
