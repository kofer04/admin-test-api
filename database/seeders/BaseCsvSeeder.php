<?php

namespace Database\Seeders;

use Database\Seeders\Contracts\CsvSeederContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

abstract class BaseCsvSeeder extends Seeder implements CsvSeederContract
{
    /**
     * Number of CSV rows to read per chunk (I/O optimization).
     * Using 10,000 based on Laravel best practices and memory efficiency.
     */
    protected int $csvReadChunkSize = 10000;

    /**
     * Number of rows to insert per batch (MySQL packet size safety).
     * Using 1,000 to balance performance with packet size constraints.
     */
    protected int $insertBatchSize = 1000;

    /**
     * Progress reporting interval (rows).
     */
    protected int $progressInterval = 10000;

    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $startTime = microtime(true);
        $tableName = $this->getTableName();
        $csvFileName = $this->getCsvFileName();

        $this->command->info("Starting import: {$csvFileName} → {$tableName}");
        $this->command->info("CSV Read Chunk: {$this->csvReadChunkSize} | Insert Batch: {$this->insertBatchSize}");

        try {
            $csvPath = database_path("data/{$csvFileName}");

            if (!file_exists($csvPath)) {
                $this->command->error("CSV file not found: {$csvPath}");
                return;
            }

            $totalRows = $this->importCsvData($csvPath, $tableName);

            $duration = round(microtime(true) - $startTime, 2);
            $avgRate = $totalRows > 0 ? round($totalRows / $duration, 0) : 0;
            $this->command->info("✓ Imported {$totalRows} rows into {$tableName} in {$duration}s (~{$avgRate} rows/s)");

        } catch (\Exception $e) {
            $this->command->error("Failed to import {$csvFileName}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import CSV data into the database using DB::table()->insert().
     */
    protected function importCsvData(string $csvPath, string $tableName): int
    {
        // Disable query logging for performance
        DB::connection()->disableQueryLog();

        // Temporarily disable model events for performance
        $originalDispatcher = Model::getEventDispatcher();
        Model::unsetEventDispatcher();

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // First row contains headers

        $allowedColumns = $this->getAllowedColumns();
        $totalRows = 0;
        $csvBuffer = [];
        $insertBatch = [];

        try {
            // Read CSV in large chunks for I/O efficiency
            foreach ($csv->getRecords() as $record) {
                // Filter to only allowed columns
                $filteredRow = $this->filterColumns($record, $allowedColumns);

                // Transform the row (type casting, parsing, etc.)
                $transformedRow = $this->transformRow($filteredRow);

                $csvBuffer[] = $transformedRow;

                // When CSV buffer reaches read chunk size, process it
                if (count($csvBuffer) >= $this->csvReadChunkSize) {
                    $totalRows += $this->processCsvBuffer($csvBuffer, $tableName, $totalRows);
                    $csvBuffer = [];
                }
            }

            // Process remaining rows in buffer
            if (!empty($csvBuffer)) {
                $totalRows += $this->processCsvBuffer($csvBuffer, $tableName, $totalRows);
            }

            return $totalRows;

        } finally {
            // Re-enable model events
            Model::setEventDispatcher($originalDispatcher);
        }
    }

    /**
     * Process a CSV buffer by inserting in smaller batches.
     */
    protected function processCsvBuffer(array $buffer, string $tableName, int $currentTotal): int
    {
        $inserted = 0;
        $chunks = array_chunk($buffer, $this->insertBatchSize);

        foreach ($chunks as $chunk) {
            $this->insertBatch($chunk, $tableName);
            $inserted += count($chunk);

            // Report progress at intervals
            if (($currentTotal + $inserted) % $this->progressInterval === 0 || $inserted === count($buffer)) {
                $this->command->info("  Processed " . ($currentTotal + $inserted) . " rows...");
            }
        }

        return $inserted;
    }

    /**
     * Filter CSV row to only include allowed columns.
     */
    protected function filterColumns(array $row, array $allowedColumns): array
    {
        $filtered = [];
        foreach ($allowedColumns as $column) {
            // Use null for missing columns instead of skipping them
            $filtered[$column] = $row[$column] ?? null;
        }
        return $filtered;
    }

    /**
     * Insert a batch of rows using DB::table()->insert() with transaction.
     * Each batch gets its own transaction for better performance.
     */
    protected function insertBatch(array $batch, string $tableName): void
    {
        // Use transaction per batch (not one big transaction)
        DB::transaction(function () use ($batch, $tableName) {
            DB::table($tableName)->insert($batch);
        });
    }

    /**
     * Parse a timestamp string, returning null if empty.
     */
    protected function parseTimestamp(?string $value): ?string
    {
        if (empty($value) || $value === '' || $value === 'NULL') {
            return null;
        }

        try {
            return date('Y-m-d H:i:s', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse a boolean value from CSV.
     */
    protected function parseBoolean(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Parse JSON data from CSV.
     */
    protected function parseJson(?string $value): ?string
    {
        if (empty($value) || $value === '' || $value === 'NULL') {
            return null;
        }

        // If already valid JSON, return as-is
        if ($this->isValidJson($value)) {
            return $value;
        }

        // Otherwise return null
        return null;
    }

    /**
     * Check if a string is valid JSON.
     */
    protected function isValidJson(?string $string): bool
    {
        if (empty($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

