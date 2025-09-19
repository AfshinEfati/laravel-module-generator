<?php

namespace App\Services;

use App\Services\Contracts\BaseServiceInterface;
use BadMethodCallException;

abstract class BaseService implements BaseServiceInterface
{
    /**
     * @param object $repository Concrete repository for the service.
     */
    public function __construct(protected object $repository) {}

    public function repository(): object
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

    public function __call(string $method, array $parameters): mixed
    {
        if (method_exists($this->repository, $method)) {
            return $this->repository->{$method}(...$parameters);
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }

    protected function callRepository(string $method, array $arguments = []): mixed
    {
        if (!method_exists($this->repository, $method)) {
            throw new BadMethodCallException(sprintf('Repository method %s::%s not found.', $this->repository::class, $method));
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
