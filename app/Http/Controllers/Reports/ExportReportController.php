<?php

namespace App\Http\Controllers\Reports;

use App\DTO\Reports\ReportOptionsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ExportReportRequest;
use App\Services\Reports\Enums\ReportType;
use App\Services\Reports\ReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function __invoke(ExportReportRequest $request): StreamedResponse
    {
        $options = new ReportOptionsDTO(
            reportType: ReportType::from($request->validated('type')),
            marketIds: $request->validated('market_ids'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            format: $request->validated('format'),
        );

        return $this->reportService->exportReport($options);
    }
}
