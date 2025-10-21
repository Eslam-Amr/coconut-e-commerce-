<?php

namespace App\Http\Requests\Api\App\Client\Product;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

    

class ProductReviewRequest extends MasterRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $reviewId = $this->route('reviewId');
        
        return [
            'product_id' => [
                $requireOrSometimes,
                'integer',
                'exists:products,id'
            ],
            'rating' => [
                $requireOrSometimes,
                'integer',
                'min:1',
                'max:5'
            ],
            'comment' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateUserCanReview($validator);
        });
    }

    /**
     * Validate that user can review the product
     */
    private function validateUserCanReview(Validator $validator): void
    {
        $user = $this->user();
        $productId = $this->input('product_id');

        if (!$user || !$productId) {
            return;
        }

        // Check if user has completed an order containing this product
        $hasCompletedOrder = \App\Models\Order::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        if (!$hasCompletedOrder) {
            
            $validator->errors()->add('product_id', 'You can only review products that you have purchased and received (completed orders)');
        }

        // Check if user already reviewed this product (only for create, not update)
        $reviewId = $this->route('reviewId');
        if (!$reviewId) {
            $existingReview = \App\Models\ProductReview::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if ($existingReview) {
                $validator->errors()->add('product_id', 'You have already reviewed this product');
            }
        }
    }
}
