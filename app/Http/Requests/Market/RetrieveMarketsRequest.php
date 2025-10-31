<?php

namespace App\Http\Requests\Market;

use Illuminate\Foundation\Http\FormRequest;

class RetrieveMarketsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is handled by MarketPolicy in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Convert comma-separated strings to arrays.
     */
    protected function prepareForValidation(): void
    {
        // Convert market_ids
        if ($this->has('market_ids') && is_string($this->market_ids)) {
            $this->merge([
                'market_ids' => array_filter(
                    array_map('intval', explode(',', $this->market_ids)),
                    fn($id) => $id > 0
                )
            ]);
        }

        // Convert user_ids
        if ($this->has('user_ids') && is_string($this->user_ids)) {
            $this->merge([
                'user_ids' => array_filter(
                    array_map('intval', explode(',', $this->user_ids)),
                    fn($id) => $id > 0
                )
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * NOTE: user_ids is only available for Super Admin users to filter markets by specific users
     * NOTE: market_ids is received as comma-separated string but converted to array in prepareForValidation
     * NOTE: user_ids is received as comma-separated string but converted to array in prepareForValidation
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paginate' => 'nullable|boolean',
            'market_ids' => 'nullable|array',
            'market_ids.*' => 'integer|exists:markets,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'load' => 'nullable|string',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'order_by' => 'nullable|string',
            'direction' => 'nullable|string|in:asc,desc',
        ];
    }
}
