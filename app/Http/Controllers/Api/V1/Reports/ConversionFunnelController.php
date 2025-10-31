<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Http\Resources\Reports\ConversionFunnelChartResource;
use App\Services\Reports\ConversionFunnel\ConversionFunnelService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversionFunnelController extends Controller
{
    public function __construct(
        private readonly ConversionFunnelService $service
    ) {}

    /**
     * Get chart data and analytics for Conversion Funnel report
     */
    public function index(ReportFilterRequest $request): JsonResponse
    {
        $this->authorize(Permission::ReadReportConversionFunnel->value);
        $filters = ReportFilterDTO::fromRequest($request);
        $result = $this->service->getChartData($filters);

        return response()->json([
            'data' => ConversionFunnelChartResource::collection($result['data']),
            'analytics' => $result['analytics'],
        ]);
    }

    /**
     * Export Conversion Funnel report as CSV
     */
    public function export(ReportFilterRequest $request): StreamedResponse
    {
        $this->authorize(Permission::ExportReportConversionFunnel->value);

        $filters = ReportFilterDTO::fromRequest($request);

        return $this->service->exportCsv($filters);
    }
}
