<?php

namespace App\Http\Requests\Api\App\Client\Wallet;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;

class ChargeWalletRequest extends MasterRequest
{
    use BilingualValidationTrait;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1|max:10000',
            'currency' => 'required|string|in:USD,EUR,GBP'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => __('validation.required', ['attribute' => __('messages.amount')]),
            'amount.numeric' => __('validation.numeric', ['attribute' => __('messages.amount')]),
            'amount.min' => __('validation.min.numeric', ['attribute' => __('messages.amount'), 'min' => 1]),
            'amount.max' => __('validation.max.numeric', ['attribute' => __('messages.amount'), 'max' => 10000]),
            'currency.required' => __('validation.required', ['attribute' => __('messages.currency')]),
            'currency.string' => __('validation.string', ['attribute' => __('messages.currency')]),
            'currency.in' => __('validation.in', ['attribute' => __('messages.currency')]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'amount' => __('messages.amount'),
            'currency' => __('messages.currency'),
        ];
    }
}
