<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\BaseRepositoryInterface;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll(): iterable
    {
        return $this->model->query()->latest()->get();
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findDynamic(
        array $where = [],
        array $with = [],
        array $whereNot = [],
        array $whereIn = [],
        array $whereNotIn = [],
        array $whereBetween = [],
        array $whereNotBetween = [],
        array $whereNull = [],
        array $whereNotNull = [],
        array $orWhere = [],
        array $orWhereIn = [],
        array $orWhereNotIn = [],
        array $orWhereBetween = [],
        array $orWhereNotBetween = [],
        array $orWhereNull = [],
        array $orWhereNotNull = [],
        array $whereRaw = [],
        array $orWhereRaw = []
    ): ?Model {
        return $this->buildDynamicQuery(
            $where,
            $with,
            $whereNot,
            $whereIn,
            $whereNotIn,
            $whereBetween,
            $whereNotBetween,
            $whereNull,
            $whereNotNull,
            $orWhere,
            $orWhereIn,
            $orWhereNotIn,
            $orWhereBetween,
            $orWhereNotBetween,
            $orWhereNull,
            $orWhereNotNull,
            $whereRaw,
            $orWhereRaw,
        )->first();
    }

    public function getByDynamic(
        array $where = [],
        array $with = [],
        array $whereNot = [],
        array $whereIn = [],
        array $whereNotIn = [],
        array $whereBetween = [],
        array $whereNotBetween = [],
        array $whereNull = [],
        array $whereNotNull = [],
        array $orWhere = [],
        array $orWhereIn = [],
        array $orWhereNotIn = [],
        array $orWhereBetween = [],
        array $orWhereNotBetween = [],
        array $orWhereNull = [],
        array $orWhereNotNull = [],
        array $whereRaw = [],
        array $orWhereRaw = []
    ): Collection {
        return $this->buildDynamicQuery(
            $where,
            $with,
            $whereNot,
            $whereIn,
            $whereNotIn,
            $whereBetween,
            $whereNotBetween,
            $whereNull,
            $whereNotNull,
            $orWhere,
            $orWhereIn,
            $orWhereNotIn,
            $orWhereBetween,
            $orWhereNotBetween,
            $orWhereNull,
            $orWhereNotNull,
            $whereRaw,
            $orWhereRaw,
        )->get();
    }

    public function store(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $record = $this->find($id);

        return $record ? $record->update($data) : false;
    }

    public function delete(int|string $id): bool
    {
        $record = $this->find($id);

        return $record ? (bool) $record->delete() : false;
    }

    protected function applyCondition(Builder $query, string $method, array $arguments): void
    {
        if ($arguments === []) {
            return;
        }

        if (!array_is_list($arguments)) {
            $query->{$method}($arguments);

            return;
        }

        if (isset($arguments[0]) && is_array($arguments[0])) {
            foreach ($arguments as $segment) {
                if (!is_array($segment) || $segment === []) {
                    continue;
                }

                if (!array_is_list($segment)) {
                    $query->{$method}($segment);
                    continue;
                }

                $query->{$method}(...$segment);
            }

            return;
        }

        $query->{$method}(...$arguments);
    }

    protected function buildDynamicQuery(
        array $where = [],
        array $with = [],
        array $whereNot = [],
        array $whereIn = [],
        array $whereNotIn = [],
        array $whereBetween = [],
        array $whereNotBetween = [],
        array $whereNull = [],
        array $whereNotNull = [],
        array $orWhere = [],
        array $orWhereIn = [],
        array $orWhereNotIn = [],
        array $orWhereBetween = [],
        array $orWhereNotBetween = [],
        array $orWhereNull = [],
        array $orWhereNotNull = [],
        array $whereRaw = [],
        array $orWhereRaw = []
    ): Builder {
        $query = $this->model->newQuery();

        if ($with !== []) {
            $query->with($with);
        }

        $this->applyCondition($query, 'where', $where);
        $this->applyCondition($query, 'whereNot', $whereNot);
        $this->applyCondition($query, 'whereIn', $whereIn);
        $this->applyCondition($query, 'whereNotIn', $whereNotIn);
        $this->applyCondition($query, 'whereBetween', $whereBetween);
        $this->applyCondition($query, 'whereNotBetween', $whereNotBetween);
        $this->applyCondition($query, 'whereNull', $whereNull);
        $this->applyCondition($query, 'whereNotNull', $whereNotNull);

        $this->applyCondition($query, 'orWhere', $orWhere);
        $this->applyCondition($query, 'orWhereIn', $orWhereIn);
        $this->applyCondition($query, 'orWhereNotIn', $orWhereNotIn);
        $this->applyCondition($query, 'orWhereBetween', $orWhereBetween);
        $this->applyCondition($query, 'orWhereNotBetween', $orWhereNotBetween);
        $this->applyCondition($query, 'orWhereNull', $orWhereNull);
        $this->applyCondition($query, 'orWhereNotNull', $orWhereNotNull);

        $this->applyCondition($query, 'whereRaw', $whereRaw);
        $this->applyCondition($query, 'orWhereRaw', $orWhereRaw);

        return $query;
    }
}
