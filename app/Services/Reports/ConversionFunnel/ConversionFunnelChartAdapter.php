<?php

namespace App\Services\Reports\ConversionFunnel;

use App\DTO\Reports\ChartDataDTO;
use Illuminate\Support\Collection;

class ConversionFunnelChartAdapter
{
    /**
     * Transform raw DB data into horizontal bar chart format for conversion funnel
     *
     * Input: Collection of {market_name, event_name, step_number, conversions_total, conversions_percentage}
     * Output: ChartDataDTO with labels (event names) and datasets (one per market)
     */
    public function transform(Collection $data): ChartDataDTO
    {
        // Get unique event names for Y-axis (in step order)
        $labels = $data->sortBy('step_number')
            ->pluck('event_name')
            ->unique()
            ->values()
            ->toArray();

        // Group by market to create separate bars
        $groupedByMarket = $data->groupBy('market_name');

        // Create dataset for each market with conversion labels
        $datasets = $groupedByMarket->map(function ($marketData, $marketName) use ($labels) {
            // Map conversions to events
            $conversionsByEvent = $marketData->keyBy('event_name');

            // Build data points with formatted labels showing percentage and count
            $dataPoints = [];
            $dataLabels = [];

            foreach ($labels as $eventName) {
                $stepData = $conversionsByEvent->get($eventName);
                if ($stepData) {
                    $dataPoints[] = $stepData['conversions_total'];
                    $dataLabels[] = sprintf(
                        '%s%% (%s)',
                        number_format($stepData['conversions_percentage'], 1),
                        number_format($stepData['conversions_total'])
                    );
                } else {
                    $dataPoints[] = 0;
                    $dataLabels[] = '0% (0)';
                }
            }

            return [
                'label' => $marketName,
                'data' => $dataPoints,
                'backgroundColor' => $this->getColorForMarket($marketName),
                'borderColor' => $this->getColorForMarket($marketName),
                'borderWidth' => 1,
                'dataLabels' => $dataLabels, // Custom labels for tooltips
            ];
        })->values()->toArray();

        return new ChartDataDTO(
            type: 'bar',
            labels: $labels,
            datasets: $datasets,
            options: [
                'indexAxis' => 'y', // Makes it horizontal
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                        'display' => true,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Conversion Funnel',
                        'font' => ['size' => 16],
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => '(context) => {
                                const dataset = context.dataset;
                                const dataIndex = context.dataIndex;
                                const label = dataset.label || "";
                                const value = dataset.dataLabels[dataIndex] || context.formattedValue;
                                return label + ": " + value;
                            }',
                        ],
                    ],
                ],
                'scales' => [
                    'x' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Number of Conversions',
                        ],
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Funnel Steps',
                        ],
                    ],
                ],
            ]
        );
    }

    private function getColorForMarket(string $marketName, float $alpha = 0.8): string
    {
        // Simple color assignment (can be improved with hash-based colors)
        $colors = [
            'rgba(255, 99, 132, %s)',   // Red
            'rgba(54, 162, 235, %s)',   // Blue
            'rgba(255, 206, 86, %s)',   // Yellow
            'rgba(75, 192, 192, %s)',   // Green
            'rgba(153, 102, 255, %s)',  // Purple
            'rgba(255, 159, 64, %s)',   // Orange
            'rgba(201, 203, 207, %s)',  // Gray
        ];

        $index = crc32($marketName) % count($colors);

        return sprintf($colors[$index], $alpha);
    }
}
