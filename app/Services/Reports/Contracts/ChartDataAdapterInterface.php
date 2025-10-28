<?php

namespace App\Services\Reports\Contracts;

interface ChartDataAdapterInterface
{
    public function adapt(ReportDataInterface $data): array;
}
