<?php

namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

interface CriteriaInterface
{
    /**
     * Apply the criteria to the given query builder.
     *
     * @param Builder $model
     * @return Builder
     */
    public function apply(Builder $model): Builder;
}
