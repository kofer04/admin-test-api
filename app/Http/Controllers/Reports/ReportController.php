<?php

namespace App\Http\Controllers\Reports;

use App\DTO\Reports\ReportOptionsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportRequest;
use App\Services\Reports\Enums\ReportType;
use App\Services\Reports\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function __invoke(ReportRequest $request): JsonResponse
    {
        $options = new ReportOptionsDTO(
            reportType: ReportType::from($request->validated('type')),
            marketIds: $request->validated('market_ids'),
            startDate: $request->validated(key: 'start_date'),
            endDate: $request->validated('end_date'),
        );

        // $reportData = $this->rseportService->getReportData($options);
        $chartData = $this->reportService->getChartData($options);

        return response()->json([
            // 'reportData' => $reportData,
            'chartData' => $chartData,
        ]);
    }
}
