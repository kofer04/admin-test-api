<?php

namespace App\Repositories;

use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class MarketRepository extends Repository
{
    protected string $model = Market::class;


    /**
     * Paginate markets with user-based filtering applied
     *
     * SECURITY: User filtering is automatically applied based on authenticated user.
     * No need to pass user_id - it's retrieved from auth()->user().
     *
     * Usage:
     * - $markets = $marketRepository->paginate(['per_page' => 10])
     * - $markets = $marketRepository->paginate(['per_page' => 20, 'page' => 2])
     */
    public function paginate(array $params = []): EloquentCollection | LengthAwarePaginator
    {
        return parent::paginate($params);
    }


    /**
     * Get markets with user-based filtering applied
     *
     * SECURITY: User filtering is automatically applied based on authenticated user.
     * No need to pass user_id - it's retrieved from auth()->user().
     *
     * Usage:
     * - $markets = $marketRepository->get()
     * - $markets = $marketRepository->get(['load' => 'users'])
     */
    public function get(array $params = []): EloquentCollection
    {
        return $this->query($params)->get();
    }

    /**
     * Override query method to apply user-based filtering, search, and optional filters
     */
    public function query(array $params = []): Builder
    {
        $query = parent::query($params);

        // SECURITY: Apply user-based market filtering first (unbypassable for regular users)
        $this->applyUserFilter($query, $params);

        // Apply search filter if provided
        $this->applySearchFilter($query, $params);

        // OPTIONAL: Apply market IDs filter (within user's accessible markets)
        $this->applyMarketIdsFilter($query, $params);

        // OPTIONAL: Apply specific user filter (SuperAdmin only)
        $this->applySpecificUserFilter($query, $params);

        return $query;
    }


    /**
     * Apply user-based market filtering to query
     * Super Admin sees all markets, regular users see only assigned markets
     *
     * CRITICAL SECURITY: This method enforces user filtering by getting the authenticated user directly.
     * It CANNOT be bypassed by passing user_id in params - it always uses auth()->user().
     */
    protected function applyUserFilter(Builder $query, array $params): Builder
    {
        // SECURITY: Always get user from authentication, NEVER trust params
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            // No authenticated user - return empty result
            // This prevents unauthorized access when user context is missing
            return $query->whereRaw('1 = 0');
        }

        // Super Admin sees all markets (no filter needed)
        if ($user->isAdmin()) {
            return $query;
        }

        // Regular users see only their assigned markets
        $query->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });

        return $query;
    }

    /**
     * Apply market IDs filtering
     * This filters to specific market IDs, but ONLY within the user's accessible markets
     *
     * NOTE: market_ids comes as a comma-separated string from the frontend,
     * but RetrieveMarketsRequest converts it to an array before it reaches here
     */
    protected function applyMarketIdsFilter(Builder $query, array $params): Builder
    {
        $marketIds = data_get($params, 'market_ids');

        if (!empty($marketIds) && is_array($marketIds)) {
            $query->whereIn('markets.id', $marketIds);
        }

        return $query;
    }

    /**
     * Apply search filter to query
     * Searches across market name, domain, and path
     */
    protected function applySearchFilter(Builder $query, array $params): Builder
    {
        $search = data_get($params, 'search');

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('markets.name', 'like', $searchTerm)
                  ->orWhere('markets.domain', 'like', $searchTerm)
                  ->orWhere('markets.path', 'like', $searchTerm);
            });
        }

        return $query;
    }

    /**
     * Apply specific user filter (SuperAdmin only)
     * This allows SuperAdmin to filter markets by specific users' access
     *
     * SECURITY: This filter only works if authenticated user is SuperAdmin
     */
    protected function applySpecificUserFilter(Builder $query, array $params): Builder
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        // Only allow this filter for SuperAdmin
        if (!$authUser || !$authUser->isAdmin()) {
            return $query;
        }

        $userIds = data_get($params, 'user_ids');

        if (!empty($userIds) && is_array($userIds)) {
            $query->whereHas('users', function ($q) use ($userIds) {
                $q->whereIn('users.id', $userIds);
            });
        }

        return $query;
    }
}
