<?php

namespace App\Services\Contracts;

interface BaseServiceInterface
{
    public function repository(): object;

    public function index(): mixed;

    public function show(int|string $id): mixed;

    public function store(mixed $payload): mixed;

    public function update(int|string $id, mixed $payload): bool;

    public function destroy(int|string $id): bool;
}
