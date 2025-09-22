<?php

namespace Efati\ModuleGenerator\Support;

use Efati\ModuleGenerator\Casts\GoliDateCast;

trait HasGoliDates
{
    /**
     * Register the configured Jalali date casts on model initialisation.
     */
    protected function initializeHasGoliDates(): void
    {
        foreach ($this->goliDateCastAttributes() as $attribute) {
            $this->casts[$attribute] = GoliDateCast::class;
        }
    }



    /**
     * Resolve the list of attributes that should be converted into Goli instances.
     *
     * @return array<int, string>
     */
    protected function goliDateCastAttributes(): array
    {
        $attributes = [];

        if (property_exists($this, 'goliDates')) {
            $configured = $this->goliDates;

            if (is_array($configured)) {
                $attributes = $configured;
            } elseif (is_string($configured) && $configured !== '') {
                $attributes = [$configured];
            }
        }

        $attributes = array_filter($attributes, static fn ($value) => is_string($value) && $value !== '');

        return array_values(array_unique($attributes));
    }

    /**
     * Merge new attributes into the cast list at runtime.
     */
    public function addGoliDateCast(string ...$attributes): void
    {
        foreach ($attributes as $attribute) {
            if ($attribute === '') {
                continue;
            }

            $this->casts[$attribute] = GoliDateCast::class;
        }
    }
}
