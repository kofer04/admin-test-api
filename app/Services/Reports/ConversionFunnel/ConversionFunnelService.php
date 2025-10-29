<?php

namespace App\Services\Reports\ConversionFunnel;

use App\DTO\Reports\ChartDataDTO;
use App\DTO\Reports\ReportFilterDTO;
use App\Repositories\Reports\ConversionFunnelRepository;
use App\Services\Reports\Contracts\ReportServiceInterface;
use App\Services\Reports\Shared\CsvExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversionFunnelService implements ReportServiceInterface
{
    public function __construct(
        private readonly ConversionFunnelRepository $repository,
        private readonly ConversionFunnelChartAdapter $chartAdapter,
        private readonly ConversionFunnelExportAdapter $exportAdapter,
        private readonly CsvExportService $csvExporter,
    ) {}

    /**
     * Get chart data for Conversion Funnel
     */
    public function getChartData(ReportFilterDTO $filters): ChartDataDTO
    {
        // 1. Get raw data from repository (with caching)
        $data = $this->repository->getData($filters);

        // 2. Transform to chart format
        return $this->chartAdapter->transform($data);
    }

    /**
     * Export Conversion Funnel report as CSV
     */
    public function exportCsv(ReportFilterDTO $filters): StreamedResponse
    {
        // 1. Get raw data from repository (cache will hit!)
        $data = $this->repository->streamData($filters);

        // 2. Transform to CSV format
        $csvData = $this->exportAdapter->transform($data);

        // 3. Generate CSV file
        return $this->csvExporter->export(
            filename: 'conversion-funnel-' . now()->format(format: 'Y-m-d') . '.csv',
            headers: ['market', 'event', 'conversions_total', 'conversions_percentage'],
            rows: $csvData
        );
    }
}
