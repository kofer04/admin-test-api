<?php

namespace App\Services;

use App\Repositories\MarketRepository;
use App\Services\Reports\Shared\CsvExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketExportService
{
    public function __construct(
        private readonly MarketRepository $repository,
        private readonly CsvExportService $csvExporter,
    ) {}

    /**
     * Export markets to CSV
     * Exports all markets based on user permissions and applied filters
     */
    public function exportCsv(array $params = []): StreamedResponse
    {
        // Get all markets (respecting user permissions and filters from repository)
        $markets = $this->repository->query($params)->get();

        // Transform to CSV rows
        $rows = $this->transformToCsvRows($markets);

        // Generate CSV file
        return $this->csvExporter->export(
            filename: 'markets-' . now()->format('Y-m-d') . '.csv',
            headers: ['ID', 'Name', 'Domain', 'Path', 'Status', 'Users Count', 'Created At'],
            rows: $rows
        );
    }

    /**
     * Transform markets to CSV rows
     */
    private function transformToCsvRows($markets): \Generator
    {
        foreach ($markets as $market) {
            yield [
                $market->id,
                $market->name,
                $market->domain,
                $market->path,
                $market->status,
                $market->users()->count(),
                $market->created_at?->format('Y-m-d H:i:s') ?? '',
            ];
        }
    }
}

