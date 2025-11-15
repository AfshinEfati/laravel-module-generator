<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\Contracts\BaseServiceInterface;
use BadMethodCallException;
use Illuminate\Support\Collection;

abstract class BaseService implements BaseServiceInterface
{
    /**
     * @param BaseRepositoryInterface $repository Concrete repository for the service.
     */
    public function __construct(protected BaseRepositoryInterface $repository) {}

    public function repository(): BaseRepositoryInterface
    {
        return $this->repository;
    }

    public function index(): mixed
    {
        return $this->callRepository('getAll');
    }

    public function show(int|string $id): mixed
    {
        return $this->callRepository('find', [$id]);
    }

    public function store(mixed $payload): mixed
    {
        $payload = $this->normalisePayload($payload);

        return $this->callRepository('store', [$payload]);
    }

    public function update(int|string $id, mixed $payload): bool
    {
        $payload = $this->normalisePayload($payload);

        return (bool) $this->callRepository('update', [$id, $payload]);
    }

    public function destroy(int|string $id): bool
    {
        return (bool) $this->callRepository('delete', [$id]);
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
    ): mixed {
        return $this->callRepository('findDynamic', [
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
        ]);
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
        /** @var Collection */
        return $this->callRepository('getByDynamic', [
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
        ]);
    }

    public function __call(string $method, array $parameters): mixed
    {
        if (!is_string($method) || $method === '') {
            throw new BadMethodCallException('Method name must be a non-empty string.');
        }

        if (!method_exists($this->repository, $method)) {
            throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
        }

        try {
            return $this->repository->{$method}(...$parameters);
        } catch (\Throwable $e) {
            throw new BadMethodCallException(sprintf('Error calling method %s::%s: %s', static::class, $method, $e->getMessage()), 0, $e);
        }
    }

    protected function callRepository(string $method, array $arguments = []): mixed
    {
        if (!is_string($method) || $method === '') {
            throw new BadMethodCallException('Repository method name must be a non-empty string.');
        }

        if (!method_exists($this->repository, $method)) {
            throw new BadMethodCallException(sprintf('Repository method %s::%s not found.', get_class($this->repository), $method));
        }

        return $this->repository->{$method}(...$arguments);
    }

    protected function normalisePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_object($payload) && method_exists($payload, 'toArray')) {
            return $payload->toArray();
        }

        return (array) $payload;
    }
}
