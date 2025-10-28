<?php

namespace App\Services\Reports\Charts\Adapters;

use App\DTO\Reports\ConversionFunnelDataDTO;
use App\Services\Reports\Contracts\ChartDataAdapterInterface;
use App\Services\Reports\Contracts\ReportDataInterface;

class ConversionFunnelAdapter implements ChartDataAdapterInterface
{
    public function adapt(ReportDataInterface $data): array
    {
        /** @var ConversionFunnelDataDTO $data */
        $labels = $data->data->pluck('date')->unique()->values();
        $datasets = $data->data->groupBy('market')->map(function ($marketData, $market) {
            return [
                'label' => $market,
                'data' => $marketData->pluck('bookings'),
            ];
        })->values();

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
}
