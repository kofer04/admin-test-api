<?php

namespace App\Repositories\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Models\LogServiceTitanJob;
use App\Models\User;
use App\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

        $cachedData = Cache::remember($cacheKey, now()->addHour(), function () use ($filters) {
            $marketIds = $this->getAccessibleMarketIds($filters->marketIds);

            $query = LogServiceTitanJob::query()
                ->select([
                    'markets.name as market_name',
                    'markets.id as market_id',
                    DB::raw('DATE(log_service_titan_jobs.start) as date'),
                    DB::raw('COUNT(*) as bookings_count'),
                ])
                ->join('markets', 'log_service_titan_jobs.market_id', '=', 'markets.id')
                ->whereIn('log_service_titan_jobs.market_id', $marketIds)
                ->whereBetween('log_service_titan_jobs.start', [
                    $filters->startDate,
                    $filters->endDate,
                ])
                ->groupBy('markets.id', 'markets.name', 'date')
                ->orderBy('date')
                ->orderBy('markets.name');


            $results = $query->get()->toArray();


            return $results; // Convert to plain array for efficient caching
        });


        // Return as Collection for consistent interface
        return collect($cachedData);
    }

    /**
     * Stream version – optimized for CSV export
     */
    public function streamData(ReportFilterDTO $filters): \Generator
    {
        $marketIds = $this->getAccessibleMarketIds($filters->marketIds);

        $query = LogServiceTitanJob::query()
            ->select([
                'markets.name as market_name',
                'markets.id as market_id',
                DB::raw('DATE(log_service_titan_jobs.start) as date'),
                DB::raw('COUNT(*) as bookings_count'),
            ])
            ->join('markets', 'log_service_titan_jobs.market_id', '=', 'markets.id')
            ->whereIn('log_service_titan_jobs.market_id', $marketIds)
            ->whereBetween('log_service_titan_jobs.start', [
                $filters->startDate,
                $filters->endDate,
            ])
            ->groupBy('markets.id', 'markets.name', 'date')
            ->orderBy('date')
            ->orderBy('markets.name');

        foreach ($query->cursor() as $row) {
            yield [
                'market_name' => $row->market_name,
                'date' => $row->date,
                'bookings_count' => $row->bookings_count,
            ];
        }
    }

    /**
     * Get accessible market IDs based on user role
     * - If marketIds provided: use them (already filtered by request)
     * - If empty: get from authenticated user's accessible markets
     */
    private function getAccessibleMarketIds(array $marketIds): array
    {
        if (!empty($marketIds)) {
            return $marketIds;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        return $user->accessibleMarketIds();
    }
}
