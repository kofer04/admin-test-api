<?php

namespace App\Services\Reports\Shared;

use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * Export data to CSV with streaming for memory efficiency
     *
     * @param string $filename The name of the CSV file to download
     * @param array $headers The column headers for the CSV
     * @param iterable $rows The data rows to export (can be a generator for large datasets)
     * @return StreamedResponse
     */
    public function export(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rows) {
            // Clear any existing output buffers to prevent memory issues
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Open output stream
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new \RuntimeException('Failed to open output stream for CSV export');
            }

            try {
                $csv = Writer::createFromStream($handle);

                // Insert header row
                $csv->insertOne($headers);

                // Stream data rows one by one (memory efficient)
                foreach ($rows as $row) {
                    $csv->insertOne($row);
                    flush(); // Send data immediately to browser
                }
            } finally {
                fclose($handle);
            }
        });

        // Set proper headers for CSV download
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
