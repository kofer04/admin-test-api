<?php

namespace App\DTO\Reports;

use App\Services\Reports\Contracts\ReportDataInterface;
use App\Services\Reports\JobBookingsReport;
use Illuminate\Support\Collection;

class JobBookingsDataDTO implements ReportDataInterface
{
    private Collection $bookings;
    private ?JobBookingsReport $report;

    public function __construct(Collection $bookings, ?JobBookingsReport $report = null)
    {
        $this->bookings = $bookings;
        $this->report = $report;
    }

    public function toArray(): array
    {
        return $this->bookings->map(function ($booking) {
            return [
                'market' => $booking->market,
                'date' => $booking->date,
                'bookings' => (int) $booking->bookings,
            ];
        })->values()->toArray();
    }

    /**
     * Process data in chunks for memory-efficient exports
     */
    public function chunk(callable $callback, int $chunkSize = 1000): void
    {
        if ($this->report && method_exists($this->report, 'processInChunks')) {
            $this->report->processInChunks($callback, $chunkSize);
        } else {
            // Fallback: chunk the existing collection
            $this->bookings->chunk($chunkSize)->each(function ($chunk) use ($callback) {
                $formatted = $chunk->map(function ($booking) {
                    return [
                        'market' => $booking->market,
                        'date' => $booking->date,
                        'bookings' => (int) $booking->bookings,
                    ];
                })->values()->toArray();

                $callback($formatted);
            });
        }
    }
}
