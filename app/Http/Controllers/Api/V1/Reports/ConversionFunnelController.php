<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\DTO\Reports\ReportFilterDTO;
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
     * Get chart data for Conversion Funnel report
     */
    public function index(ReportFilterRequest $request): JsonResponse
    {
        $filters = ReportFilterDTO::fromRequest($request);
        $chartData = $this->service->getChartData($filters);

        return response()->json(
            ConversionFunnelChartResource::make($chartData)
        );
    }

    /**
     * Export Conversion Funnel report as CSV
     */
    public function export(ReportFilterRequest $request): StreamedResponse
    {
        $filters = ReportFilterDTO::fromRequest($request);

        return $this->service->exportCsv($filters);
    }
}
