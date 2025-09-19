<?php

namespace Efati\ModuleGenerator\Casts;

use Efati\ModuleGenerator\Support\Goli;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class GoliDateCast implements CastsAttributes
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return Goli|null
     */
    public function get($model, string $key, $value, array $attributes): ?Goli
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Goli) {
            return $value;
        }

        return Goli::instance($value);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return array<string, mixed>|string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $instance = $value instanceof Goli ? $value : Goli::instance($value);

        $format = method_exists($model, 'getDateFormat') ? $model->getDateFormat() : 'Y-m-d H:i:s';

        return $instance->formatGregorian($format);
    }
}
