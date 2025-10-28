<?php

namespace App\Exports\Reports;

use App\Services\Reports\Contracts\ChunkableDataInterface;
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

        $response = new StreamedResponse(function () use ($data, $headers) {
            // Open output stream directly
            $handle = fopen('php://output', 'w');
            $csv = Writer::createFromStream($handle);

            // Write BOM for Excel compatibility (optional, but recommended)
            $csv->setOutputBOM(Writer::BOM_UTF8);

            // Write headers
            $csv->insertOne($headers);

            // Use chunked streaming if available
            if ($data instanceof ReportDataInterface) {
                $data->chunk(function ($rows) use ($csv) {
                    $csv->insertAll($rows);
                }, 1000);
            } else {
                // Fallback to array chunking for non-streamable data
                $dataArray = $data->toArray();
                foreach (array_chunk($dataArray, 1000) as $chunk) {
                    $csv->insertAll($chunk);
                }
            }

            fclose($handle);
        });

        // Set proper headers for CSV download
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
