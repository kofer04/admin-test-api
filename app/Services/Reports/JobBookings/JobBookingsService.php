<?php

namespace App\Services\Reports\JobBookings;

use App\DTO\Reports\ReportFilterDTO;
use App\Repositories\Reports\JobBookingsRepository;
use App\Services\Reports\Contracts\ReportServiceInterface;
use App\Services\Reports\Shared\CsvExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobBookingsService implements ReportServiceInterface
{
    public function __construct(
        private readonly JobBookingsRepository $repository,
        private readonly JobBookingsChartAdapter $chartAdapter,
        private readonly JobBookingsExportAdapter $exportAdapter,
        private readonly CsvExportService $csvExporter,
    ) {}

    /**
     * Get chart data and analytics for Job Bookings
     */
    public function getChartData(ReportFilterDTO $filters): array
    {
        // 1. Get raw data from repository (with caching)
        $data = $this->repository->getData($filters);

        // 2. Transform to flat array format
        $chartData = $this->chartAdapter->transform($data);

        // 3. Get analytics (will reuse cached data)
        $analytics = $this->repository->getAnalytics($filters);

        return [
            'data' => $chartData,
            'analytics' => $analytics,
        ];
    }

    /**
     * Export Job Bookings as CSV
     */
    public function exportCsv(ReportFilterDTO $filters): StreamedResponse
    {
        // 1. Get SAME raw data from repository (cache will hit!)
        $data = $this->repository->streamData($filters);

        // 2. Transform to CSV format
        $csvData = $this->exportAdapter->transform($data);

        // 3. Generate CSV file
        return $this->csvExporter->export(
            filename: 'job-bookings-' . now()->format(format: 'Y-m-d') . '.csv',
            headers: ['market', 'date', 'bookings'],
            rows: $csvData
        );
    }
}
