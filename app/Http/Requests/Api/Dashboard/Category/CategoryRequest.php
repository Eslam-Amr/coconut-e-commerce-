<?php

namespace App\Http\Requests\Api\Dashboard\Category;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
use Illuminate\Support\Facades\Log;

    

class CategoryRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('CategoryRequest');
        $requireOrSometimes = $this->getRequireOrSometimes();
        $category = $this->route('category');
        
        // Handle both model instance and string ID
        if (is_object($category) && isset($category->id)) {
            $categoryId = $category->id;
        } elseif (is_string($category) || is_numeric($category)) {
            $categoryId = $category;
        } else {
            $categoryId = null;
        }

        return [
            'ar.name' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($categoryId) {
                    $query = \App\Models\CategoryTranslation::where('name', $value)
                        ->where('locale', 'ar');
                    
                    if ($categoryId) {
                        $query->where('category_id', '!=', $categoryId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The Arabic category name has already been taken.');
                    }
                }
            ],
            'en.name' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($categoryId) {
                    $query = \App\Models\CategoryTranslation::where('name', $value)
                        ->where('locale', 'en');
                    
                    if ($categoryId) {
                        $query->where('category_id', '!=', $categoryId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The English category name has already been taken.');
                    }
                }
            ],
            'icon' => [$requireOrSometimes, 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    
}
