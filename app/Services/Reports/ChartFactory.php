<?php

namespace App\Services\Reports;

use App\Services\Reports\Charts\Adapters\ConversionFunnelAdapter;
use App\Services\Reports\Charts\Adapters\JobBookingsAdapter;
use App\Services\Reports\Contracts\ChartDataAdapterInterface;
use App\Services\Reports\Enums\ReportType;
use Exception;

class ChartFactory
{
    public static function make(ReportType $reportType): ChartDataAdapterInterface
    {
        return match ($reportType) {
            ReportType::JobBookings => new JobBookingsAdapter(),
            ReportType::ConversionFunnel => new ConversionFunnelAdapter(),
            default => throw new Exception("No chart adapter found for report: {$reportType->value}"),
        };
    }
}
