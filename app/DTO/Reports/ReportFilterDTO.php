<?php

namespace App\DTO\Reports;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportFilterDTO
{
    public function __construct(
        public readonly array $marketIds,
        public readonly string $startDate,
        public readonly string $endDate,
    ) {}

    public static function fromRequest(Request $request): self
    {
        // Get dates from request or use defaults (previous month - most likely to have data)
        $startDate = $request->input('start_date')
            ?? $request->input('start')
            ?? now()->startOfMonth()->format('Y-m-d');

        $endDate = $request->input('end_date')
            ?? $request->input('end')
            ?? now()->endOfMonth()->format('Y-m-d');

        return new self(
            marketIds: $request->input('market_ids', []),
            startDate: self::normalizeDate($startDate),
            endDate: self::normalizeDate($endDate),
        );
    }

    public function cacheKey(): string
    {
        return md5(json_encode([
            'market_ids' => $this->marketIds,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]));
    }

    /**
     * Normalize date to YYYY-MM-DD format
     */
    private static function normalizeDate(string $date): string
    {
        return Carbon::parse($date)->format('Y-m-d');
    }
}
