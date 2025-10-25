<?php

namespace Database\Seeders;

class MarketsSeeder extends BaseCsvSeeder
{
    /**
     * Get the database table name.
     */
    public function getTableName(): string
    {
        return 'markets';
    }

    /**
     * Get the CSV filename.
     */
    public function getCsvFileName(): string
    {
        return 'markets.csv';
    }

    /**
     * Get the allowed columns to import.
     */
    public function getAllowedColumns(): array
    {
        return [
            'id',
            'name',
            'domain',
            'path',
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
            'domain' => !empty($row['domain']) ? $row['domain'] : null,
            'path' => !empty($row['path']) ? $row['path'] : null,
            'created_at' => $this->parseTimestamp($row['created_at'] ?? null),
            'updated_at' => $this->parseTimestamp($row['updated_at'] ?? null),
            'deleted_at' => $this->parseTimestamp($row['deleted_at'] ?? null),
        ];
    }
}
