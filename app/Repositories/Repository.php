<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class Repository implements RepositoryContract
{
    protected string $model;

    public function filter(Builder $query, array $params = []): self
    {
        $orderBy = data_get($params, 'order_by', null) ?? data_get($params, 'sort_by', null);

        $direction = data_get($params, 'direction', null) ?? data_get($params, 'sort_direction', null);

        $load = data_get($params, 'load', []);

        if(is_string($load)) {
            $load = explode(',', $load);
        } elseif (! is_array($load)) {
            $load = [];
        }

        if($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        if($load) {
            $query->with($load);
        }


        return $this;
    }

    public function get(array $params = []): EloquentCollection
    {
        return $this->query($params)->get();
    }

    public function paginate(array $params = []): EloquentCollection | LengthAwarePaginator
    {
        $perPage = data_get($params, 'per_page', 15);

        $paginate = filter_var(data_get($params, 'paginate', true), FILTER_VALIDATE_BOOLEAN);

        $perPageInt = (int) $perPage;

        $query = $this->query($params);

        if(false === $paginate || $perPageInt === -1) {
            return $query->get();
        }

        return $query->paginate($perPageInt);
    }

    public function query(array $params = []): Builder
    {
        $query = $this->model::query();

        $this->filter($query, $params);

        return $query;
    }

    public function find(Model|int|string|null $id, array $params = []): ?Model
    {
        if($id === null) {
            return null;
        }

       $model = null;

        if($id instanceof Model) {
            $model = $id;
        } else {
            $model = $this->model::find($id);
        }

        if(! $model) {
            throw new ModelNotFoundException($this->model . " not found");
        }

        $load = data_get($params, 'load', []);

        if(! empty($load)) {
            if(is_string($load)) {
                $model->load(explode(',', $load));
            } else {
                $model->load($load);
            }
        }

        return $model;
    }

    public function create(array $data): Model
    {
        $model = $this->model::create($data);

        $load = data_get($data, 'load', []);

        if(! empty($load)) {
            if(is_string($load)) {
                $model->load(explode(',', $load));
            } else {
                $model->load($load);
            }
        }

        return $model;
    }

    public function update(Model|int|string|null $id, array $data): Model
    {
        $model = $this->find($id);

        if(! $model) {
            throw new ModelNotFoundException($this->model . " not found");
        }

        $load = data_get($data, 'load', []);

        $model->update($data);

        if($load) {
            if(is_string($load)) {
                $load = explode(',', $load);
            }

            $model->load($load);
        }

        return $model;
    }

    public function delete(Model|int|string|null $id): void
    {
        $model = $this->find($id);

        if(! $model) {
            throw new ModelNotFoundException($this->model . " not found");
        }

        $model->delete();
    }

    public function updateOrCreate(array $attributes, array $data = []): Model
    {
        $model = $this->model::query()->where($attributes)->first();

        if(! $model) {
            return $this->update($model, $data);
        }

        return $this->create(array_merge($attributes, $data));
    }

    public function firstOrCreate(array $attributes, array $data = []): Model
    {
        $model = $this->model::query()->where($attributes)->first();

        if(! $model) {
            $find = $this->find($model);

            if($find) {
                return $find;
            }

        }

        return $this->create(array_merge($attributes, $data));
    }
}
