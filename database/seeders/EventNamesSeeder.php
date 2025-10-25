<?php

namespace Database\Seeders;

class EventNamesSeeder extends BaseCsvSeeder
{
    /**
     * Get the database table name.
     */
    public function getTableName(): string
    {
        return 'event_names';
    }

    /**
     * Get the CSV filename.
     */
    public function getCsvFileName(): string
    {
        return 'event_names.csv';
    }

    /**
     * Get the allowed columns to import.
     */
    public function getAllowedColumns(): array
    {
        return [
            'id',
            'name',
            'display_on_client',
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
            'name' => $row['name'] ?? null,
            'display_on_client' => $this->parseBoolean($row['display_on_client'] ?? null),
            'created_at' => $this->parseTimestamp($row['created_at'] ?? null),
            'updated_at' => $this->parseTimestamp($row['updated_at'] ?? null),
            'deleted_at' => $this->parseTimestamp($row['deleted_at'] ?? null),
        ];
    }
}
