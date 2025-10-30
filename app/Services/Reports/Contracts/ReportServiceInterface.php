<?php

namespace App\Services\Reports\Contracts;

use App\DTO\Reports\ReportFilterDTO;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ReportServiceInterface
{
    public function getChartData(ReportFilterDTO $filters): array;
    public function exportCsv(ReportFilterDTO $filters): StreamedResponse;
}
