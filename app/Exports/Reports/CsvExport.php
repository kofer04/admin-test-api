<?php

namespace App\Exports\Reports;

use App\Services\Reports\Contracts\ExportInterface;
use App\Services\Reports\Contracts\ReportDataInterface;
use App\Services\Reports\Enums\ReportType;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExport implements ExportInterface
{
    private array $headers = [
        ReportType::JobBookings->value => ['market', 'date', 'bookings'],
        ReportType::ConversionFunnel->value => ['market', 'event', 'conversions_total', 'conversions_percentage'],
    ];

    public function export(ReportDataInterface $data, ReportType $reportType): StreamedResponse
    {
        $headers = $this->headers[$reportType->value] ?? [];
        $timestamp = now()->format('Y-m-d_His');
        $filename = strtolower(str_replace(' ', '_', $reportType->value)) . "_{$timestamp}.csv";

        return new StreamedResponse(function () use ($data, $headers) {
            // Clear output buffers (avoids blank CSVs)
            if (ob_get_level()) {
                ob_end_clean();
            }

            $handle = fopen('php://output', 'w');
            $csv = Writer::createFromStream($handle);

            // Write CSV headers
            $csv->insertOne($headers);

            // --- ✅ Stream real query results ---
            if (method_exists($data, 'chunk')) {
                // For Eloquent-like data sources
                $data->chunk(function ($rows) use ($csv) {
                    foreach ($rows as $row) {
                        // Normalize row to array (in case it's an object or model)
                        $csv->insertOne((array) $row);
                    }
                    flush();
                });
            } else {
                // For array-based data sources
                $rows = $data->toArray();

                foreach (array_chunk($rows, 1000) as $chunk) {
                    foreach ($chunk as $row) {
                        $csv->insertOne((array) $row);
                    }
                    flush();
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
