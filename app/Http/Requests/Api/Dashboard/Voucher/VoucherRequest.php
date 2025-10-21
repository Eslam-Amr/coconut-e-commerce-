<?php

namespace App\Http\Requests\Api\Dashboard\Voucher;

use App\Http\Requests\Api\MasterRequest;


class VoucherRequest extends MasterRequest
{
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $voucher = $this->route('voucher');

        $voucherId = null;
        if (is_object($voucher) && isset($voucher->id)) {
            $voucherId = $voucher->id;
        } elseif (is_string($voucher) || is_numeric($voucher)) {
            $voucherId = $voucher;
        }

        return [
            'code' => [
                $requireOrSometimes,
                'string',
                'max:255',
                'unique:vouchers,code' . ($voucherId ? ',' . $voucherId : ''),
            ],
            'discount' => [$requireOrSometimes, 'numeric', 'min:0', 'max:100'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['sometimes', 'integer', 'min:1'],
            'used_count' => ['sometimes', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    
}


