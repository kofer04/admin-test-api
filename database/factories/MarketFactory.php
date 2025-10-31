<?php

namespace Database\Factories;

use App\Models\Market;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketFactory extends Factory
{
    protected $model = Market::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'domain' => fake()->domainName(),
            'path' => '/' . fake()->slug(),
        ];
    }
}
