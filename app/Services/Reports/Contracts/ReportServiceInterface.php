<?php

namespace App\Services\Reports\Contracts;

use App\DTO\Reports\ReportFilterDTO;
use App\DTO\Reports\ChartDataDTO;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ReportServiceInterface
{
    public function getChartData(ReportFilterDTO $filters): ChartDataDTO;
    public function exportCsv(ReportFilterDTO $filters): StreamedResponse;
}
