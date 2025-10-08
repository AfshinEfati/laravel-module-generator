<?php

namespace App\Services\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface BaseServiceInterface
{
    public function repository(): BaseRepositoryInterface;

    public function index(): mixed;

    public function show(int|string $id): mixed;

    public function store(mixed $payload): mixed;

    public function update(int|string $id, mixed $payload): bool;

    public function destroy(int|string $id): bool;

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
