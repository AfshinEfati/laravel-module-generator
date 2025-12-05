<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /**
     * @return iterable<TModel>
     */
    public function getAll(): iterable;

    /**
     * @param int|string $id
     * @return TModel|null
     */
    public function find(int|string $id): ?Model;

    /**
     * @return TModel|null
     */
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
    ): ?Model;

    /**
     * @return Collection<int, TModel>
     */
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
    ): Collection;

    /**
     * @param array $data
     * @return TModel
     */
    public function store(array $data): Model;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;

    public function getCriteria(): array;

    public function pushCriteria(mixed $criteria): static;

    public function popCriteria(mixed $criteria): static;

    public function skipCriteria(bool $status = true): static;

    public function applyCriteria(Builder $query): Builder;
}
