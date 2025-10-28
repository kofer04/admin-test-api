<?php

namespace App\Services\Reports;

use App\DTO\Reports\ReportOptionsDTO;
use App\Services\Reports\Enums\ReportFormat;
use App\Services\Reports\Enums\ReportType;
use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
  /**
     * Get the raw report data for a JSON response.
     */
    public function getReportData(ReportOptionsDTO $options): array
    {
        $reportInstance = $this->resolveReportInstance($options);
        return $reportInstance->getData()->toArray();
    }

    /**
     * Get the formatted data for a chart.
     */
    public function getChartData(ReportOptionsDTO $options): array
    {
        $reportInstance = $this->resolveReportInstance($options);
        $data = $reportInstance->getData();
        $reportType = ReportType::from($options->reportType?->value);

        $chartFormatter = ChartFactory::make($reportType);

        return $chartFormatter->adapt($data);
    }

    /**
     * Export a report to the specified format.
     */
    public function exportReport(ReportOptionsDTO $options): StreamedResponse
    {
        $reportInstance = $this->resolveReportInstance($options);
        $data = $reportInstance->getData();

        $format = ReportFormat::from($options->format);
        $reportType = ReportType::from($options->reportType?->value);

        $exporter = ExportFactory::make($format);

        return $exporter->export($data, $reportType);
    }

    private function resolveReportInstance(ReportOptionsDTO $options): AbstractReport
    {
        return match ($options->reportType?->value) {
            ReportType::JobBookings->value => new JobBookingsReport($options),
            ReportType::ConversionFunnel->value => new ConversionFunnelReport($options),
            default => throw new Exception("Unknown report type."),
        };
    }
}
