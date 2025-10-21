<?php

namespace App\Http\Requests\Api\Dashboard\FlashSale;

use App\Http\Requests\Api\MasterRequest;


class FlashSaleRequest extends MasterRequest
{
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $flashSale = $this->route('flash_sale') ?? $this->route('flashSale') ?? $this->route('flashsale');

        return [
            'title' => [$requireOrSometimes, 'string', 'max:255'],
            'flashable_id' => [
                $requireOrSometimes, 
                'integer', 
                'min:1',
                // function ($attribute, $value, $fail) {
                //     $flashableType = $this->input('flashable_type');
                //     if ($flashableType && $value) {
                //         if (!class_exists($flashableType)) {
                //             $fail('Invalid flashable_type class.');
                //             return;
                //         }
                //         if (!$flashableType::find($value)) {
                //             $fail('The referenced ' . class_basename($flashableType) . ' does not exist.');
                //         }
                //     }
                // }
            ],
            'flashable_type' => [$requireOrSometimes, 'in:App\\Models\\Product,App\\Models\\Category'],
            'discount' => [$requireOrSometimes, 'numeric', 'min:0', 'max:100'],
            'max_limit' => ['nullable', 'integer', 'min:1'],
            'count' => ['sometimes', 'integer', 'min:0'],
            'start_date' => [$requireOrSometimes, 'date'],
            'end_date' => [$requireOrSometimes, 'date', 'after:start_date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    
}


