<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends Repository
{
    protected string $model = User::class;

    /**
     * Override query method to apply search and market filtering
     */
    public function query(array $params = []): Builder
    {
        $query = parent::query($params);

        // Apply search filter if provided
        $this->applySearchFilter($query, $params);

        // Apply market IDs filter if provided
        $this->applyMarketIdsFilter($query, $params);

        return $query;
    }

    /**
     * Apply search filter to query
     * Searches across user name and email
     */
    protected function applySearchFilter(Builder $query, array $params): Builder
    {
        $search = data_get($params, 'search');

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('users.name', 'like', $searchTerm)
                  ->orWhere('users.email', 'like', $searchTerm);
            });
        }

        return $query;
    }

    /**
     * Apply market IDs filtering
     * Filters users who belong to specific markets
     */
    protected function applyMarketIdsFilter(Builder $query, array $params): Builder
    {
        $marketIds = data_get($params, 'market_ids');

        if (!empty($marketIds) && is_array($marketIds)) {
            $query->whereHas('markets', function ($q) use ($marketIds) {
                $q->whereIn('markets.id', $marketIds);
            });
        }

        return $query;
    }
}
