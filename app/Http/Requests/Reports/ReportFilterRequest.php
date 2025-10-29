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
            $this->merge([
                'market_ids' => explode(',', $this->market_ids)
            ]);
        }

        // Apply market access control for Market Users
        $user = $this->user();

        if ($user && !$user->isAdmin()) {
            $accessibleMarketIds = $user->accessibleMarketIds();
            $requestedMarketIds = $this->input('market_ids', null);

            if ($requestedMarketIds === null) {
                // No market_ids provided - default to user's accessible markets
                $this->merge(['market_ids' => $accessibleMarketIds]);
            } elseif (is_array($requestedMarketIds) && count($requestedMarketIds) > 0) {
                // market_ids provided - intersect with accessible markets
                $filteredIds = array_intersect($requestedMarketIds, $accessibleMarketIds);
                $this->merge(['market_ids' => array_values($filteredIds)]);
            }
            // else: empty array remains empty
        }
    }
}
