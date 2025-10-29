<?php
namespace App\Services\Reports\JobBookings;

use Illuminate\Support\Collection;

class JobBookingsExportAdapter
{
    /**
     * Transform raw DB data into CSV row format
     *
     * Input: Collection of {market_name, date, bookings_count}
     * Output: Array of arrays (rows for CSV)
     */
    public function transform(iterable $data): \Generator
    {
        foreach ($data as $row) {
            yield [
                'Market' => $row['market_name'],
                'Date' => $row['date'],
                'Bookings' => $row['bookings_count'],
            ];
        }
    }
}
