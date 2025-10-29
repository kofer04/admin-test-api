<?php

namespace App\Services\Reports\JobBookings;

use App\DTO\Reports\ChartDataDTO;
use Illuminate\Support\Collection;

class JobBookingsChartAdapter
{
    /**
     * Transform raw DB data into line chart format
     *
     * Input: Collection of {market_name, date, bookings_count}
     * Output: ChartDataDTO with labels (dates) and datasets (one per market)
     */
    public function transform(Collection $data): ChartDataDTO
    {
        // Get unique dates for X-axis
        $labels = $data->pluck('date')->unique()->sort()->values()->toArray();

        // Group by market to create separate lines
        $groupedByMarket = $data->groupBy('market_name');

        // Create dataset for each market
        $datasets = $groupedByMarket->map(function ($marketData, $marketName) use ($labels) {
            // Map bookings to dates (fill missing dates with 0)
            $bookingsByDate = $marketData->pluck('bookings_count', 'date')->toArray();

            $dataPoints = array_map(
                fn($date) => $bookingsByDate[$date] ?? 0,
                $labels
            );

            return [
                'label' => $marketName,
                'data' => $dataPoints,
                'borderColor' => $this->getColorForMarket($marketName),
                'backgroundColor' => $this->getColorForMarket($marketName, 0.1),
                'tension' => 0.4, // Smooth line
            ];
        })->values()->toArray();

        return new ChartDataDTO(
            type: 'line',
            labels: $labels,
            datasets: $datasets,
            options: [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['position' => 'top'],
                    'title' => ['display' => true, 'text' => 'Job Bookings Over Time'],
                ],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                ],
            ]
        );
    }

    private function getColorForMarket(string $marketName, float $alpha = 1): string
    {
        // Simple color assignment (can be improved with hash-based colors)
        $colors = [
            'rgba(255, 99, 132, %s)',   // Red
            'rgba(54, 162, 235, %s)',   // Blue
            'rgba(255, 206, 86, %s)',   // Yellow
            'rgba(75, 192, 192, %s)',   // Green
            'rgba(153, 102, 255, %s)',  // Purple
        ];

        $index = crc32($marketName) % count($colors);

        return sprintf($colors[$index], $alpha);
    }
}
