<?php

namespace App\Http\Requests\Api\App\Client\MoneyTransfer;

use App\Http\Requests\Api\MasterRequest;

class MoneyTransferRequest extends MasterRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'iban' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bank_name.required' => 'Bank name is required.',
            'account_number.required' => 'Account number is required.',
            'iban.required' => 'IBAN is required.',
            'amount.required' => 'Transfer amount is required.',
            'amount.numeric' => 'Transfer amount must be a valid number.',
            'amount.min' => 'Transfer amount must be at least 0.01.',
            'amount.max' => 'Transfer amount cannot exceed 999,999.99.',
        ];
    }
}
