<?php

namespace App\DTO\Reports;

use App\Services\Reports\Enums\ReportType;

class ReportOptionsDTO
{
    public function __construct(
        public readonly ?ReportType $reportType = null,
        public readonly ?array $marketIds = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $format = null,
    ) {}
}
