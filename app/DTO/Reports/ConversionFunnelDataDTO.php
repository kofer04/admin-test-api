<?php

namespace App\DTO\Reports;

use App\Services\Reports\Contracts\ReportDataInterface;
use Illuminate\Support\Collection;

class ConversionFunnelDataDTO implements ReportDataInterface
{
    /**
     * @param Collection<int, object{market: string, event: string, conversions_total: int, conversions_percentage: float}> $data
     */
    public function __construct(
        public readonly Collection $data
    ) {}

    /**
     * Returns the report data as a simple associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data->toArray();
    }
    public function chunk(callable $callback, int $chunkSize = 1000): void
    {
        $this->data->chunk($chunkSize)->each(function ($chunk) use ($callback) {
            $callback($chunk->toArray());
        });
    }
}
