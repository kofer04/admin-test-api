<?php
namespace App\Services\Reports\ConversionFunnel;

class ConversionFunnelExportAdapter
{
    /**
     * Transform raw DB data into CSV row format
     *
     * Input: Iterable of {market_name, event_name, conversions_total, conversions_percentage}
     * Output: Generator yielding arrays (rows for CSV)
     */
    public function transform(iterable $data): \Generator
    {
        foreach ($data as $row) {
            yield [
                'market' => $row['market_name'],
                'event' => $row['event_name'],
                'conversions_total' => $row['conversions_total'],
                'conversions_percentage' => number_format($row['conversions_percentage'], 2) . '%',
            ];
        }
    }
}
