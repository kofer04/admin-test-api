<?php

namespace App\Services\Reports\JobBookings;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class JobBookingsChartAdapter
{
    /**
     * Transform raw DB data into flat array format for flexible chart rendering
     *
     * Input: Collection of {market_name, date, bookings_count}
     * Output: Array of [{date, market-name: count, ...}]
     *
     * Example: [
     *   ['date' => 'Jan 1', 'pd-houston' => 5, 'pd-long-island' => 8],
     *   ['date' => 'Jan 2', 'pd-houston' => 3, 'pd-long-island' => 12],
     * ]
     */
    public function transform(Collection $data): array
    {
        // Get unique dates for rows
        $dates = $data->pluck('date')->unique()->sort()->values();

        // Get unique markets (already in slug format)
        $markets = $data->pluck('market_name')->unique();

        // Build lookup: [market_name][date] => bookings_count
        $lookup = [];
        foreach ($data as $row) {
            $lookup[$row['market_name']][$row['date']] = $row['bookings_count'];
        }

        // Build flat array: each date is a row with market columns
        return $dates->map(function ($date) use ($markets, $lookup) {
            $row = ['date' => Carbon::parse($date)->format('M d')];

            foreach ($markets as $marketName) {
                $row[$marketName] = $lookup[$marketName][$date] ?? 0;
            }

            return $row;
        })->values()->toArray();
    }
}
