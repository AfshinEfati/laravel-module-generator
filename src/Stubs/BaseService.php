<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\Contracts\BaseServiceInterface;

class BaseService implements BaseServiceInterface
{
    public function __construct(protected BaseRepositoryInterface $repository) {}

    public function all(): iterable
    {
        return $this->repository->all();
    }

    public function find(int|string $id): mixed
    {
        return $this->repository->find($id);
    }

    public function create(array $data): mixed
    {
        return $this->repository->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->delete($id);
    }
}
