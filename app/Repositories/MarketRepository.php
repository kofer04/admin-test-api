<?php

namespace App\Repositories;

use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class MarketRepository extends Repository
{
    protected string $model = Market::class;

    /**
     * Get markets with optional user_id filtering
     *
     * Usage:
     * - $markets = $marketRepository->get(['user_id' => auth()->id()])
     * - $markets = $marketRepository->get() // returns all markets
     */
    public function get(array $params = []): EloquentCollection
    {
        $query = $this->query($params);

        // Get user_id from params
        $userId = data_get($params, 'user_id');

        if ($userId) {
            $user = User::find($userId);

            // If user not found, return empty collection
            if (!$user) {
                return new EloquentCollection();
            }

            // Super Admin sees all markets (no filter)
            if (!$user->hasRole('Super Admin')) {
                // Market User sees only assigned markets
                $query->whereHas('users', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
            }
        }

        return $query->get();
    }
}
