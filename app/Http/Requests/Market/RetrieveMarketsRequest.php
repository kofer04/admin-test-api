<?php

namespace App\Http\Requests\Market;

use Illuminate\Foundation\Http\FormRequest;

class RetrieveMarketsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'market_ids' => 'nullable|string|exists:markets,id',
            'load' => 'nullable|string',
            'search' => 'nullable|string',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'sort' => 'nullable|string',
            'sort_direction' => 'nullable|string',
        ];
    }
}
