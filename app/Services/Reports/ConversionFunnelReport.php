<?php

namespace App\Services\Reports;

use App\DTO\Reports\ConversionFunnelDataDTO;
use App\Models\LogEvent;
use App\Services\Reports\Contracts\ReportDataInterface;

class ConversionFunnelReport extends AbstractReport
{
    protected function generateData(): ReportDataInterface
    {
        // Complex logic to calculate conversion funnel steps from log_events
        // ...
        $steps = LogEvent::whereBetween('created_at', [$this->options->startDate, $this->options->endDate])
            ->select('market', 'event')
            ->groupBy('market', 'event')
            ->get();

        // Calculate conversions_total and conversions_percentage
        // ...

        return new ConversionFunnelDataDTO($steps->toArray());
    }

    public function getData(): ReportDataInterface
    {
        return $this->generateData();
    }
}
