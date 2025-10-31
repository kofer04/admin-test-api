<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can update their own settings
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required'],
        ];

        // Special validation for specific setting keys
        if ($this->input('key') === 'selected_markets') {
            $rules['value'] = ['required', 'array'];
            $rules['value.*'] = ['integer', 'exists:markets,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Setting key is required.',
            'value.required' => 'Setting value is required.',
            'value.array' => 'Selected markets must be an array.',
            'value.*.integer' => 'Each market ID must be an integer.',
            'value.*.exists' => 'One or more selected markets do not exist.',
        ];
    }
}

