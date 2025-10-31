<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\DTO\Reports\ReportFilterDTO;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Http\Resources\Reports\JobBookingsChartResource;
use App\Services\Reports\JobBookings\JobBookingsService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobBookingsController extends Controller
{
    public function __construct(
        private readonly JobBookingsService $service
    ) {}

    /**
     * Get chart data and analytics for Job Bookings report
     */
    public function index(ReportFilterRequest $request): JsonResponse
    {
        $this->authorize(Permission::ReadReportJobBookings->value);
        $filters = ReportFilterDTO::fromRequest($request);
        $result = $this->service->getChartData($filters);

        return response()->json([
            'data' => JobBookingsChartResource::collection($result['data']),
            'analytics' => $result['analytics'],
        ]);
    }

    /**
     * Export Job Bookings report as CSV
     */
    public function export(ReportFilterRequest $request): StreamedResponse
    {
        $this->authorize(Permission::ReadReportJobBookings->value);

        $filters = ReportFilterDTO::fromRequest($request);

        return $this->service->exportCsv($filters);
    }
}
