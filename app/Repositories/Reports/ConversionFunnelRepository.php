<?php

namespace App\Repositories\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Models\EventName;
use App\Models\LogEvent;
use App\Models\User;
use App\Repositories\Repository;
use App\Repositories\SettingRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConversionFunnelRepository extends Repository
{
    public function __construct(
        private readonly SettingRepository $settingRepository
    ) {}

    /**
     * Chart version – in-memory aggregation
     * Returns conversion funnel data grouped by market and step
     */
    public function getData(ReportFilterDTO $filters): Collection
    {
        $cacheKey = "conversion-funnel:{$filters->cacheKey()}";

        $cachedData = Cache::remember($cacheKey, now()->addHour(), function () use ($filters) {
            // Get accessible market IDs based on user role
            $marketIds = $this->getAccessibleMarketIds($filters->marketIds);

            // Get funnel step event IDs from settings (in order)
            $funnelStepIds = $this->getFunnelStepIds();

            // Get event names for the funnel steps
            $eventNames = EventName::whereIn('id', $funnelStepIds)
                ->get()
                ->keyBy('id');

            // Get unique session counts for each market and event
            $rawData = LogEvent::query()
                ->select([
                    'markets.id as market_id',
                    'markets.name as market_name',
                    'log_events.event_name_id',
                    DB::raw('COUNT(DISTINCT log_events.session_id) as unique_sessions')
                ])
                ->join('markets', 'log_events.market_id', '=', 'markets.id')
                ->whereIn('log_events.event_name_id', $funnelStepIds)
                ->whereIn('log_events.market_id', $marketIds)
                ->whereBetween('log_events.created_at', [
                    $filters->startDate,
                    $filters->endDate,
                ])
                ->groupBy('markets.id', 'markets.name', 'log_events.event_name_id')
                ->orderBy('markets.name')
                ->get();

            // Transform into structured funnel data with conversion rates
            return $this->calculateConversionRates($rawData, $funnelStepIds, $eventNames)
                ->toArray(); // Convert to plain array for efficient caching
        });

        // Return as Collection for consistent interface
        return collect($cachedData);
    }

    /**
     * Stream version – optimized for CSV export
     */
    public function streamData(ReportFilterDTO $filters): \Generator
    {
        // For CSV export, we can reuse the cached getData since conversion
        // calculation requires all steps to be in memory anyway
        $data = $this->getData($filters);

        foreach ($data as $row) {
            yield [
                'market_name' => $row['market_name'],
                'event_name' => $row['event_name'],
                'conversions_total' => $row['conversions_total'],
                'conversions_percentage' => $row['conversions_percentage'],
            ];
        }
    }

    /**
     * Get funnel step IDs from settings in order
     */
    private function getFunnelStepIds(): array
    {
        $settings = $this->settingRepository->getGroup('conversion_funnel_step_');

        // Convert to array and sort by key to maintain order (step_1, step_2, etc.)
        $settingsArray = $settings->toArray();
        ksort($settingsArray);

        return array_values($settingsArray);
    }

    /**
     * Calculate conversion rates for each step relative to previous step
     * Step 1 is always 100% (base), subsequent steps show % of previous step that converted
     */
    private function calculateConversionRates(Collection $rawData, array $funnelStepIds, Collection $eventNames): Collection
    {
        $groupedByMarket = $rawData->groupBy('market_id');
        $results = collect();

        foreach ($groupedByMarket as $marketId => $marketData) {
            $marketName = $marketData->first()->market_name;
            $previousStepCount = null;

            foreach ($funnelStepIds as $index => $eventNameId) {
                $stepData = $marketData->firstWhere('event_name_id', $eventNameId);
                $currentCount = $stepData?->unique_sessions ?? 0;

                // Get event name from EventName model
                $eventName = $eventNames->get($eventNameId);
                $eventDisplayName = $eventName?->name ?? 'Unknown Event';

                // Calculate conversion percentage
                if ($index === 0) {
                    // First step is always 100%
                    $percentage = 100;
                } else {
                    // Subsequent steps: percentage of previous step that converted
                    $percentage = $previousStepCount > 0
                        ? round(($currentCount / $previousStepCount) * 100, 2)
                        : 0;
                }

                $results->push([
                    'market_id' => $marketId,
                    'market_name' => $marketName,
                    'event_name_id' => $eventNameId,
                    'event_name' => $eventDisplayName,
                    'step_number' => $index + 1,
                    'conversions_total' => $currentCount,
                    'conversions_percentage' => $percentage,
                ]);

                $previousStepCount = $currentCount;
            }
        }

        return $results;
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
