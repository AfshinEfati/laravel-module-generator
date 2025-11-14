<?php

namespace Efati\ModuleGenerator\Enums\Concerns;

use BackedEnum;

trait EnumHelperTrait
{
    public static function toList(): array
    {
        return array_map(fn(BackedEnum $case) => [
            'name'    => $case->name,
            'fa_name' => method_exists($case, 'faName') ? $case->faName() : null,
            'code'    => $case->value,
        ], self::cases());
    }

    public static function toMap(): array
    {
        $result = [];

        foreach (self::cases() as $case) {
            $result[$case->value] = [
                'name'    => $case->name,
                'fa_name' => method_exists($case, 'faName') ? $case->faName() : null,
                'code'    => $case->value,
            ];
        }

        return $result;
    }

    /**
     * Accepts:
     *   - int|string
     *   - BackedEnum (auto converts to ->value)
     */
    public static function findByValue(int|string|BackedEnum $value): ?array
    {
        // If input is enum object, convert to raw value
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        foreach (self::cases() as $case) {
            if ($case->value == $value) {
                return [
                    'name'    => $case->name,
                    'fa_name' => method_exists($case, 'faName') ? $case->faName() : null,
                    'code'    => $case->value,
                ];
            }
        }

        return null;
    }

    public static function findByName(string $name): ?array
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return [
                    'name'    => $case->name,
                    'fa_name' => method_exists($case, 'faName') ? $case->faName() : null,
                    'code'    => $case->value,
                ];
            }
        }

        return null;
    }
}
