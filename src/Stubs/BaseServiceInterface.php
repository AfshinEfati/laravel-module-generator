<?php

namespace App\Services\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;

interface BaseServiceInterface
{
    public function repository(): BaseRepositoryInterface;

    public function index(): mixed;

    public function show(int|string $id): mixed;

    public function store(mixed $payload): mixed;

    public function update(int|string $id, mixed $payload): bool;

    public function destroy(int|string $id): bool;
}
