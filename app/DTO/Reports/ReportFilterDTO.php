<?php

namespace App\DTO\Reports;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportFilterDTO
{
    public function __construct(
        public readonly array $marketIds,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $startDate = data_get($request, 'start_date', null) ?? data_get($request, 'start', null) ?? now()->subDays(30);
        $endDate = data_get($request, 'end_date', null) ?? data_get($request, 'end', null) ?? now();

        return new self(
            marketIds: $request->input('market_ids', []),
            startDate: Carbon::parse($startDate),
            endDate: Carbon::parse($endDate),
        );
    }

    public function cacheKey(): string
    {
        return md5(json_encode([
            'market_ids' => $this->marketIds,
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
        ]));
    }
}
