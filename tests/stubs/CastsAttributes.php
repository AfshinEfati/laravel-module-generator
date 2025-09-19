<?php

namespace Illuminate\Contracts\Database\Eloquent;

if (! interface_exists(CastsAttributes::class, false)) {
    interface CastsAttributes
    {
        /**
         * @param  mixed  $value
         * @return mixed
         */
        public function get($model, string $key, $value, array $attributes);

        /**
         * @param  mixed  $value
         * @return mixed
         */
        public function set($model, string $key, $value, array $attributes);
    }
}
