<?php

namespace App\Repositories\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Models\LogServiceTitanJob;
use App\Models\User;
use App\Repositories\Repository;
use Carbon\Carbon;
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
     * Get analytics for Job Bookings report
     * Uses cached data from getData() for performance
     */
    public function getAnalytics(ReportFilterDTO $filters): array
    {
        $data = $this->getData($filters);

        if ($data->isEmpty()) {
            return [
                'total_bookings' => 0,
                'average_bookings' => 0,
                'highest_market' => null,
                'lowest_market' => null,
                'highest_date' => null,
            ];
        }

        // Calculate total bookings
        $totalBookings = $data->sum('bookings_count');

        // Calculate average bookings per day
        $uniqueDates = $data->pluck('date')->unique()->count();
        $averageBookings = $uniqueDates > 0 ? round($totalBookings / $uniqueDates, 2) : 0;

        // Calculate bookings by market
        $marketTotals = $data->groupBy('market_name')->map(function ($items) {
            return $items->sum('bookings_count');
        })->sortDesc();

        // Get highest and lowest markets
        $highestMarket = $marketTotals->keys()->first();
        $lowestMarket = $marketTotals->keys()->last();

        // Calculate bookings by date
        $dateTotals = $data->groupBy('date')->map(function ($items, $date) {
            return [
                'date' => $date,
                'count' => $items->sum('bookings_count'),
            ];
        })->sortByDesc('count')->values();

        // Get highest date with formatted display
        $highestDateData = $dateTotals->first();
        $highestDate = $highestDateData 
            ? Carbon::parse($highestDateData['date'])->format('M d, Y') . ' (' . $highestDateData['count'] . ')' 
            : null;

        return [
            'total_bookings' => $totalBookings,
            'average_bookings' => $averageBookings,
            'highest_market' => $highestMarket,
            'lowest_market' => $lowestMarket,
            'highest_date' => $highestDate,
        ];
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
