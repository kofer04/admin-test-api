<?php

namespace App\Services\Reports\Charts\Adapters;

use App\DTO\Reports\JobBookingsDataDTO;
use App\Services\Reports\Contracts\ChartDataAdapterInterface;
use App\Services\Reports\Contracts\ReportDataInterface;

class JobBookingsAdapter implements ChartDataAdapterInterface
{
    public function adapt(ReportDataInterface $data): array
    {
        /** @var JobBookingsDataDTO $data */
        $labels = $data->bookings->pluck('date')->unique()->values();
        $datasets = $data->bookings->groupBy('market')->map(function ($marketData, $market) {
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
