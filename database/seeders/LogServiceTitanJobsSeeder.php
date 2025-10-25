<?php

namespace Database\Seeders;

class LogServiceTitanJobsSeeder extends BaseCsvSeeder
{
    /**
     * Get the database table name.
     */
    public function getTableName(): string
    {
        return 'log_service_titan_jobs';
    }

    /**
     * Get the CSV filename.
     */
    public function getCsvFileName(): string
    {
        return 'log_service_titan_jobs.csv';
    }

    /**
     * Get the allowed columns to import.
     */
    public function getAllowedColumns(): array
    {
        return [
            'id',
            'market_id',
            'service_titan_job_id',
            'start',
            'end',
            'job_status',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * Transform a CSV row before insertion.
     */
    public function transformRow(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            'market_id' => $row['market_id'] ?? null,
            'service_titan_job_id' => !empty($row['service_titan_job_id']) ? $row['service_titan_job_id'] : null,
            'start' => $this->parseTimestamp($row['start'] ?? null),
            'end' => $this->parseTimestamp($row['end'] ?? null),
            'job_status' => !empty($row['job_status']) ? $row['job_status'] : null,
            'created_at' => $this->parseTimestamp($row['created_at'] ?? null),
            'updated_at' => $this->parseTimestamp($row['updated_at'] ?? null),
            'deleted_at' => $this->parseTimestamp($row['deleted_at'] ?? null),
        ];
    }
}
