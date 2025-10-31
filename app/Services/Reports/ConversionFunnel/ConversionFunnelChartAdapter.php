<?php

namespace App\Services\Reports\ConversionFunnel;

use Illuminate\Support\Collection;

class ConversionFunnelChartAdapter
{
    /**
     * Transform raw DB data into flat array format for flexible chart rendering
     *
     * Input: Collection of {market_name, event_name, step_number, conversions_total, conversions_percentage}
     * Output: Array of [{event, step, market-name: total, market-name-percentage: percentage, ...}]
     *
     * Example: [
     *   ['event' => 'Page View', 'step' => 1, 'pd-houston' => 1000, 'pd-houston-percentage' => 100.0, ...],
     *   ['event' => 'Click CTA', 'step' => 2, 'pd-houston' => 500, 'pd-houston-percentage' => 50.0, ...],
     * ]
     */
    public function transform(Collection $data): array
    {
        // Get unique events (in step order for horizontal bar chart display)
        // Sort by step number ascending so Step 1 appears at top
        $events = $data->sortBy('step_number')
            ->unique(fn($row) => $row['event_name'])
            ->map(fn($row) => [
                'event_name' => $row['event_name'],
                'step_number' => $row['step_number']
            ])
            ->values();

        // Get unique markets (already in slug format)
        $markets = $data->pluck('market_name')->unique();

        // Build lookup: [event_name][market_name] => {conversions_total, conversions_percentage}
        $lookup = [];
        foreach ($data as $row) {
            $lookup[$row['event_name']][$row['market_name']] = [
                'total' => $row['conversions_total'],
                'percentage' => $row['conversions_percentage'],
            ];
        }

        // Build flat array: each event is a row with market columns (both total and percentage)
        return $events->map(function ($eventData) use ($markets, $lookup) {
            $row = [
                'event' => $eventData['event_name'],
                'step' => $eventData['step_number'],
            ];

            foreach ($markets as $marketName) {
                $data = $lookup[$eventData['event_name']][$marketName] ?? ['total' => 0, 'percentage' => 0];

                // Add both total and percentage for each market
                $row[$marketName] = $data['total'];
                $row["{$marketName}-percentage"] = $data['percentage'];
            }

            return $row;
        })->values()->toArray();
    }
}
