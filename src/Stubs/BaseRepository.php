<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Criteria\CriteriaInterface;

/**
 * @template TModel of Model
 * @implements BaseRepositoryInterface<TModel>
 */
class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var TModel
     */
    protected Model $model;
    protected array $criteria = [];
    protected bool $skipCriteria = false;

    /**
     * @param TModel $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function pushCriteria(mixed $criteria): static
    {
        if (is_string($criteria)) {
            $criteria = new $criteria;
        }

        // Ideally enforce CriteriaInterface, but for flexibility mixed is used here
        // if (!$criteria instanceof CriteriaInterface) { ... }

        $this->criteria[] = $criteria;

        return $this;
    }

    public function popCriteria(mixed $criteria): static
    {
        $this->criteria = array_filter($this->criteria, function ($item) use ($criteria) {
            if (is_object($item) && is_string($criteria)) {
                return get_class($item) !== $criteria;
            }

            if (is_string($item) && is_object($criteria)) {
                return $item !== get_class($criteria);
            }

            return $item !== $criteria;
        });

        return $this;
    }

    public function skipCriteria(bool $status = true): static
    {
        $this->skipCriteria = $status;

        return $this;
    }

    public function applyCriteria(Builder $query): Builder
    {
        if ($this->skipCriteria) {
            return $query;
        }

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof CriteriaInterface) {
                $query = $criteria->apply($query);
            } elseif (method_exists($criteria, 'apply')) {
                $query = $criteria->apply($query);
            }
        }

        return $query;
    }

    /**
     * @return iterable<TModel>
     */
    public function getAll(): iterable
    {
        $query = $this->model->query();
        $this->applyCriteria($query);

        return $query->latest()->get();
    }

    /**
     * @param int|string $id
     * @return TModel|null
     */
    public function find(int|string $id): ?Model
    {
        $query = $this->model->query();
        $this->applyCriteria($query);

        return $query->find($id);
    }

    /**
     * @return TModel|null
     */
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
    ): ?Model {
        $query = $this->buildDynamicQuery(
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
        );

        $this->applyCriteria($query);

        return $query->first();
    }

    /**
     * @return Collection<int, TModel>
     */
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
        $query = $this->buildDynamicQuery(
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
        );

        $this->applyCriteria($query);

        return $query->get();
    }

    /**
     * @param array $data
     * @return TModel
     */
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

    protected function applyCondition(Builder $query, string $method, array $arguments): void
    {
        if ($arguments === []) {
            return;
        }

        if (!array_is_list($arguments)) {
            $query->{$method}($arguments);

            return;
        }

        if (isset($arguments[0]) && is_array($arguments[0])) {
            foreach ($arguments as $segment) {
                if (!is_array($segment) || $segment === []) {
                    continue;
                }

                if (!array_is_list($segment)) {
                    $query->{$method}($segment);
                    continue;
                }

                $query->{$method}(...$segment);
            }

            return;
        }

        $query->{$method}(...$arguments);
    }

    protected function buildDynamicQuery(
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
    ): Builder {
        $query = $this->model->newQuery();

        if ($with !== []) {
            $query->with($with);
        }

        $this->applyCondition($query, 'where', $where);
        $this->applyCondition($query, 'whereNot', $whereNot);
        $this->applyCondition($query, 'whereIn', $whereIn);
        $this->applyCondition($query, 'whereNotIn', $whereNotIn);
        $this->applyCondition($query, 'whereBetween', $whereBetween);
        $this->applyCondition($query, 'whereNotBetween', $whereNotBetween);
        $this->applyCondition($query, 'whereNull', $whereNull);
        $this->applyCondition($query, 'whereNotNull', $whereNotNull);

        $this->applyCondition($query, 'orWhere', $orWhere);
        $this->applyCondition($query, 'orWhereIn', $orWhereIn);
        $this->applyCondition($query, 'orWhereNotIn', $orWhereNotIn);
        $this->applyCondition($query, 'orWhereBetween', $orWhereBetween);
        $this->applyCondition($query, 'orWhereNotBetween', $orWhereNotBetween);
        $this->applyCondition($query, 'orWhereNull', $orWhereNull);
        $this->applyCondition($query, 'orWhereNotNull', $orWhereNotNull);

        $this->applyCondition($query, 'whereRaw', $whereRaw);
        $this->applyCondition($query, 'orWhereRaw', $orWhereRaw);

        return $query;
    }
}
