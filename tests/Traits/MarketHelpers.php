<?php

namespace Tests\Traits;

use App\Models\Market;
use App\Models\User;

trait MarketHelpers
{
    /**
     * Create a market
     */
    protected function createMarket(array $attributes = []): Market
    {
        return Market::factory()->create($attributes);
    }

    /**
     * Create multiple markets
     */
    protected function createMarkets(int $count, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return Market::factory()->count($count)->create($attributes);
    }

    /**
     * Assign markets to a user
     */
    protected function assignMarketsToUser(User $user, array $marketIds): void
    {
        $user->markets()->sync($marketIds);
    }

    /**
     * Create market and assign to user
     */
    protected function createMarketForUser(User $user, array $attributes = []): Market
    {
        $market = $this->createMarket($attributes);
        $this->assignMarketsToUser($user, [$market->id]);
        
        return $market;
    }
}

