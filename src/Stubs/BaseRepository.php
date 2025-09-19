<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepositoryInterface;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll(): iterable
    {
        return $this->model->query()->latest()->get();
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function store(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        $record = $this->find($id);

        return $record ? $record->update($data) : false;
    }

    public function delete(int|string $id): bool
    {
        $record = $this->find($id);

        return $record ? (bool) $record->delete() : false;
    }
}
