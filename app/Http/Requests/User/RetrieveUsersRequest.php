<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RetrieveUsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is handled by UserPolicy in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Convert comma-separated market_ids string to array.
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
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paginate' => 'nullable|boolean',
            'market_ids' => 'nullable|array',
            'market_ids.*' => 'integer|exists:markets,id',
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

