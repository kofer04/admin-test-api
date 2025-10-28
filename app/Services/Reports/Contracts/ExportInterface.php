<?php

namespace App\Services\Reports\Contracts;

use App\Services\Reports\Enums\ReportType;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExportInterface
{
    public function export(ReportDataInterface $data, ReportType $reportType): StreamedResponse;
}
