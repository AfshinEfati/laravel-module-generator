<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    public function getAll(): iterable;

    public function find(int|string $id): mixed;

    public function store(array $data): mixed;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
