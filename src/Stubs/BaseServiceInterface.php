<?php

namespace App\Services\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface BaseServiceInterface
{
    /**
     * @return BaseRepositoryInterface<TModel>
     */
    public function repository(): BaseRepositoryInterface;

    /**
     * @return iterable<TModel>
     */
    public function index(): mixed;

    /**
     * @param int|string $id
     * @return TModel|null
     */
    public function show(int|string $id): mixed;

    /**
     * @param mixed $payload
     * @return TModel
     */
    public function store(mixed $payload): mixed;

    public function update(int|string $id, mixed $payload): bool;

    public function destroy(int|string $id): bool;

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
    ): mixed;

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
}
