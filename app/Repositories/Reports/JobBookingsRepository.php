<?php

namespace App\Repositories\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Models\LogServiceTitanJob;
use App\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JobBookingsRepository extends Repository
{
    /**
     * Chart version – in-memory aggregation
     */
    public function getData(ReportFilterDTO $filters): Collection
    {
        $cacheKey = "job-bookings:{$filters->cacheKey()}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($filters) {
            return LogServiceTitanJob::query()
                ->select([
                    'markets.name as market_name',
                    'markets.id as market_id',
                    DB::raw('DATE(log_service_titan_jobs.created_at) as date'),
                    DB::raw('COUNT(*) as bookings_count'),
                ])
                ->join('markets', 'log_service_titan_jobs.market_id', '=', 'markets.id')
                ->when(
                    !empty($filters->marketIds),
                    fn($q) => $q->whereIn('log_service_titan_jobs.market_id', $filters->marketIds)
                )
                ->whereBetween('log_service_titan_jobs.created_at', [
                    $filters->startDate,
                    $filters->endDate,
                ])
                ->groupBy('markets.id', 'markets.name', 'date')
                ->orderBy('date')
                ->orderBy('markets.name')
                ->get();
        });
    }

    /**
     * Stream version – optimized for CSV export
     */
    public function streamData(ReportFilterDTO $filters): \Generator
    {
        $query = LogServiceTitanJob::query()
            ->select([
                'markets.name as market_name',
                'markets.id as market_id',
                DB::raw('DATE(log_service_titan_jobs.created_at) as date'),
                DB::raw('COUNT(*) as bookings_count'),
            ])
            ->join('markets', 'log_service_titan_jobs.market_id', '=', 'markets.id')
            ->when(
                !empty($filters->marketIds),
                fn($q) => $q->whereIn('log_service_titan_jobs.market_id', $filters->marketIds)
            )
            ->whereBetween('log_service_titan_jobs.created_at', [
                $filters->startDate,
                $filters->endDate,
            ])
            ->groupBy('markets.id', 'markets.name', 'date')
            ->orderBy('date')
            ->orderBy('markets.name');

        foreach ($query->cursor() as $row) {
            yield [
                'market_name' => $row->markets_name,
                'date' => $row->date,
                'bookings_count' => $row->bookings_count,
            ];
        }
    }
}
