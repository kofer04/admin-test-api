<?php

namespace App\Http\Requests\Reports;

use App\Enums\Permission;
use App\Services\Reports\Enums\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BaseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $reportType = $this->input('type');
        // Check for specific permissions based on the report type.
        return match ($reportType) {
            ReportType::JobBookings->value => $this->user()->can(Permission::ReadReportJobBookings->value),
            ReportType::ConversionFunnel->value => $this->user()->can(Permission::ReadReportConversionFunnel->value),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(ReportType::class)],
            'market_ids' => ['sometimes', 'nullable', 'array'],
            'market_ids.*' => ['sometimes', 'nullable', 'exists:markets,id'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
