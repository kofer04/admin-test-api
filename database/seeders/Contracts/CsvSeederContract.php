<?php

namespace Database\Seeders\Contracts;

interface CsvSeederContract
{
    /**
     * Get the database table name for importing.
     */
    public function getTableName(): string;

    /**
     * Get the CSV filename (without path).
     */
    public function getCsvFileName(): string;

    /**
     * Get the allowed columns to import from CSV.
     */
    public function getAllowedColumns(): array;

    /**
     * Transform a CSV row before inserting into database.
     * Useful for type casting, parsing dates, etc.
     */
    public function transformRow(array $row): array;
}
