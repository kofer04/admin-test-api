<?php

namespace App\Services\Reports\Contracts;

interface ReportDataInterface
{
    public function toArray(): array;

    /**
     * Process data in chunks for memory-efficient operations
     *
     * @param callable $callback Function to process each chunk
     * @param int $chunkSize Number of rows per chunk
     * @return void
     */
    public function chunk(callable $callback, int $chunkSize = 1000): void;
}
