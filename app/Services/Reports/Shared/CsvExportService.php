<?php

namespace App\Services\Reports\Shared;

use Illuminate\Support\Facades\Response;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function export(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($filename, $headers, $rows) {
            if (ob_get_level()) ob_end_clean();

            $handle = fopen('php://output', 'w');
            $csv = Writer::createFromStream($handle);

            $csv->insertOne($headers);

            foreach ($rows as $row) {
                $csv->insertOne($row);
                flush();
            }

            fclose($handle);
        });
    }
}
