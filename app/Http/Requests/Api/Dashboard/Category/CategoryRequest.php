<?php

namespace App\Http\Requests\Api\Dashboard\Category;

use App\Http\Requests\Api\MasterRequest;
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
    public function messages(): array
    {
        return [
            'ar.name.required' => 'The Arabic category name is required.',
            'ar.name.string' => 'The Arabic category name must be a string.',
            'ar.name.max' => 'The Arabic category name may not be greater than 255 characters.',
            'ar.name.unique' => 'The Arabic category name has already been taken.',
            'en.name.required' => 'The English category name is required.',
            'en.name.string' => 'The English category name must be a string.',
            'en.name.max' => 'The English category name may not be greater than 255 characters.',
            'en.name.unique' => 'The English category name has already been taken.',
            'icon.string' => 'The icon must be a string.',
            'icon.max' => 'The icon may not be greater than 255 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'active.boolean' => 'The active field must be true or false.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ar.name' => 'Arabic category name',
            'en.name' => 'English category name',
            'icon' => 'category icon',
            'parent_id' => 'parent category',
            'active' => 'active status',
        ];
    }
}
