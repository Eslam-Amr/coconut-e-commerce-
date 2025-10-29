<?php

namespace App\Http\Requests\Api\Dashboard\MoneyTransfer;

use App\Http\Requests\Api\MasterRequest;

class UpdateMoneyTransferStatusRequest extends MasterRequest
{

    public function rules(): array
    {
        return [
            'status' => 'required|in:completed,failed,cancelled',
        ];
    }
}


