<?php

namespace App\Http\Requests\Api\General\Settings;

use App\Http\Requests\Api\MasterRequest;

class ChangeLanguageRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language' => 'required|string|in:en,ar',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'language.required' => 'The language field is required.',
            'language.string' => 'The language must be a string.',
            'language.in' => 'The language must be either English (en) or Arabic (ar).',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'language' => 'language',
        ];
    }
}
