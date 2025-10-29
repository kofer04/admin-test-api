<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Spatie permission check
        // return $this->user()->can('view_reports');
        return true;
    }

    public function rules(): array
    {
        return [
            'market_ids' => ['nullable', 'array'],
            'market_ids.*' => ['integer', 'exists:markets,id'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert comma-separated string to array if needed
        if ($this->has('market_ids') && is_string($this->market_ids)) {
            $this->merge(input: [
                'market_ids' => explode(',', $this->market_ids)
            ]);
        }
    }
}
