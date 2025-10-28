<?php

namespace App\Services\Reports;

use App\Services\Reports\Contracts\ExportInterface;
use App\Services\Reports\Enums\ReportFormat;
use App\Exports\Reports\CsvExport;
use Exception;

class ExportFactory
{
    public static function make(ReportFormat $format): ExportInterface
    {
        return match ($format) {
            ReportFormat::Csv => new CsvExport(),
            // Add new export formats here, eg. ReportFormat::Pdf => new PdfExport(),
            default => throw new Exception("Unsupported export format: {$format->value}"),
        };
    }
}
