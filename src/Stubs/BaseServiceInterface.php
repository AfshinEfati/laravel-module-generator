<?php

namespace App\Services\Contracts;

interface BaseServiceInterface
{
    public function all(): iterable;
    public function find(int|string $id): mixed;
    public function create(array $data): mixed;
    public function update(int|string $id, array $data): bool;
    public function delete(int|string $id): bool;
}
