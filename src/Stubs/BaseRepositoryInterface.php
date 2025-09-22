<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function getAll(): iterable;

    public function find(int|string $id): ?Model;

    public function store(array $data): Model;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
