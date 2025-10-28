<?php

namespace App\Services\Reports;

use App\DTO\Reports\JobBookingsDataDTO;
use App\Models\LogServiceTitanJob;
use App\Services\Reports\Contracts\ReportDataInterface;
use Illuminate\Support\Facades\DB;

class JobBookingsReport extends AbstractReport
{
    protected function generateData(): ReportDataInterface
    {
        // For regular getData() calls (JSON API, Charts)
        $bookings = $this->buildQuery()->get();

        return new JobBookingsDataDTO($bookings, $this);
    }

    public function getData(): ReportDataInterface
    {
        return $this->generateData();
    }

    /**
     * Build the optimized query with database-level aggregation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildQuery()
    {
        $query = LogServiceTitanJob::query()
            ->join('markets', 'log_service_titan_jobs.market_id', '=', 'markets.id')
            ->selectRaw('
                markets.name as market,
                DATE(log_service_titan_jobs.start) as date,
                COUNT(*) as bookings
            ')
            ->whereNull('log_service_titan_jobs.deleted_at')
            ->groupBy('markets.name', DB::raw('DATE(log_service_titan_jobs.start)'))
            ->orderBy('markets.name')
            ->orderBy('date');

        // Filter by market if provided
        if ($this->options->marketIds) {
            $query->whereIn('log_service_titan_jobs.market_id', $this->options->marketIds);
        }

        // Apply date range filters on start column
        if ($this->options->startDate) {
            $query->whereDate('log_service_titan_jobs.start', '>=', $this->options->startDate);
        }

        if ($this->options->endDate) {
            $query->whereDate('log_service_titan_jobs.start', '<=', $this->options->endDate);
        }

        return $query;
    }

    /**
     * Process data in chunks for memory-efficient exports
     * This avoids loading millions of rows into memory at once
     *
     * @param callable $callback Function to process each chunk
     * @param int $chunkSize Number of rows per chunk
     * @return void
     */
    public function processInChunks(callable $callback, int $chunkSize = 1000): void
    {
        $this->buildQuery()->chunk($chunkSize, function ($bookings) use ($callback) {
            $formatted = $bookings->map(function ($booking) {
                return [
                    'market' => $booking->market,
                    'date' => $booking->date,
                    'bookings' => (int) $booking->bookings,
                ];
            })->toArray();

            $callback($formatted);
        });
    }
}
