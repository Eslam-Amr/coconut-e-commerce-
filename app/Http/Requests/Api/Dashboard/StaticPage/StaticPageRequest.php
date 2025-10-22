<?php

namespace App\Http\Requests\Api\Dashboard\StaticPage;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaticPageRequest extends MasterRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $staticPageId = $this->route('static_page') ? $this->route('static_page') : null;
        // dd($staticPageId);
        return [

            'en.title' => [$requireOrSometimes, 'string', 'max:255',     Rule::unique('static_page_translations', 'title')
                ->ignore($staticPageId, 'static_page_id')],
            'ar.title' => [$requireOrSometimes, 'string', 'max:255',     Rule::unique('static_page_translations', 'title')
                ->ignore($staticPageId, 'static_page_id'),],
            'en.content' => [$requireOrSometimes, 'string'],
            'ar.content' => [$requireOrSometimes, 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.en.required' => 'The English title is required.',
            'title.ar.required' => 'The Arabic title is required.',
            'content.required' => 'The content field is required.',
            'content.en.required' => 'The English content is required.',
            'content.ar.required' => 'The Arabic content is required.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'slug' => 'slug',
            'title.en' => 'English title',
            'title.ar' => 'Arabic title',
            'content.en' => 'English content',
            'content.ar' => 'Arabic content',
        ];
    }
}
