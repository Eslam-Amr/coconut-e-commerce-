<?php

namespace App\Http\Requests\App\Client\Cart;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Validation\Rule;

class CalculateCartTotalRequest extends MasterRequest
{
    

    public function rules(): array
    {
        return [
            'address_id' => [
                'nullable', 'integer',
                Rule::exists('addresses', 'id')->where(fn($q) => $q->where('user_id', optional($this->user())->id)),
            ],
        ];
    }
    
    public function withValidator($validator): void
    {
        // Could add any extra cross-field checks later
    }
}


