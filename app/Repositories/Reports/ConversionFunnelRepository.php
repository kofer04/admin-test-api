<?php

namespace App\Repositories\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Enums\ConversionFunnelStep;
use App\Models\LogEvent;
use App\Repositories\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConversionFunnelRepository extends Repository
{
    /**
     * Chart version – in-memory aggregation
     * Returns conversion funnel data grouped by market and step
     */
    public function getData(ReportFilterDTO $filters): Collection
    {
        $cacheKey = "conversion-funnel:{$filters->cacheKey()}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($filters) {
            $funnelSteps = ConversionFunnelStep::allInOrder();
            $eventNameIds = ConversionFunnelStep::getEventNameIds();

            // Get unique session counts for each market and event, with event display names
            $rawData = LogEvent::query()
                ->select([
                    'markets.id as market_id',
                    'markets.name as market_name',
                    'log_events.event_name_id',
                    'event_names.name as event_display_name',
                    DB::raw('COUNT(DISTINCT log_events.session_id) as unique_sessions')
                ])
                ->join('markets', 'log_events.market_id', '=', 'markets.id')
                ->join('event_names', 'log_events.event_name_id', '=', 'event_names.id')
                ->whereIn('log_events.event_name_id', $eventNameIds)
                ->when(
                    !empty($filters->marketIds),
                    fn($q) => $q->whereIn('log_events.market_id', $filters->marketIds)
                )
                ->whereBetween('log_events.created_at', [
                    $filters->startDate,
                    $filters->endDate,
                ])
                ->groupBy('markets.id', 'markets.name', 'log_events.event_name_id', 'event_names.name')
                ->orderBy('markets.name')
                ->get();

            // Transform into structured funnel data with conversion rates
            return $this->calculateConversionRates($rawData, $funnelSteps);
        });
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
     * Calculate conversion rates for each step relative to previous step
     * Step 1 is always 100% (base), subsequent steps show % of previous step that converted
     */
    private function calculateConversionRates(Collection $rawData, array $funnelSteps): Collection
    {
        $groupedByMarket = $rawData->groupBy('market_id');
        $results = collect();

        foreach ($groupedByMarket as $marketId => $marketData) {
            $marketName = $marketData->first()->market_name;
            $previousStepCount = null;

            foreach ($funnelSteps as $index => $step) {
                $eventNameId = $step->value;
                $stepData = $marketData->firstWhere('event_name_id', $eventNameId);
                $currentCount = $stepData?->unique_sessions ?? 0;
                $eventDisplayName = $stepData?->event_display_name ?? 'Unknown Event';

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
                    'step_number' => $step->stepNumber(),
                    'conversions_total' => $currentCount,
                    'conversions_percentage' => $percentage,
                ]);

                $previousStepCount = $currentCount;
            }
        }

        return $results;
    }
}
