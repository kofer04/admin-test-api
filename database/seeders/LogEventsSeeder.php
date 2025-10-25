<?php

namespace Database\Seeders;

class LogEventsSeeder extends BaseCsvSeeder
{
    /**
     * Get the database table name.
     */
    public function getTableName(): string
    {
        return 'log_events';
    }

    /**
     * Get the CSV filename.
     */
    public function getCsvFileName(): string
    {
        return 'log_events.csv';
    }

    /**
     * Get the allowed columns to import.
     */
    public function getAllowedColumns(): array
    {
        return [
            'id',
            'market_id',
            'event_name_id',
            'session_id',
            'data',
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
            'event_name_id' => $row['event_name_id'] ?? null,
            'session_id' => $row['session_id'] ?? null,
            'data' => $this->parseJson($row['data'] ?? null),
            'created_at' => $this->parseTimestamp($row['created_at'] ?? null),
            'updated_at' => $this->parseTimestamp($row['updated_at'] ?? null),
            'deleted_at' => $this->parseTimestamp($row['deleted_at'] ?? null),
        ];
    }
}
