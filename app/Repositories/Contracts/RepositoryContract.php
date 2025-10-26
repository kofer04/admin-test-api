<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RepositoryContract
{
    /**
     * Apply filters to the query builder.
     */
    public function filter(Builder $query, array $params = []): self;

    /**
     * Get all records (optionally filtered).
     */
    public function get(array $params = []): EloquentCollection;

    /**
     * Paginate results (supports per_page = -1 to return all).
     */
    public function paginate(array $params = []): EloquentCollection|LengthAwarePaginator;

    /**
     * Return a base query builder (optionally filtered).
     */
    public function query(array $params = []): Builder;

    /**
     * Find a model by ID or instance.
     */
    public function find(Model|int|string|null $id, array $params = []): ?Model;

    /**
     * Create a new model.
     */
    public function create(array $data): Model;

    /**
     * Update a model by ID or instance.
     */
    public function update(Model|int|string|null $id, array $data): Model;

    /**
     * Delete a model by ID or instance.
     */
    public function delete(Model|int|string|null $id): void;

    /**
     * Update existing record or create a new one.
     */
    public function updateOrCreate(array $attributes, array $data = []): Model;

    /**
     * Return first matching model or create one.
     */
    public function firstOrCreate(array $attributes, array $data = []): Model;
}
