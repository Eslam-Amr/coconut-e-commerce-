<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Determine if the field should be required or sometimes based on HTTP method
     */
    protected function getRequireOrSometimes(): string
    {
        return $this->isMethod('post') ? 'required' : 'sometimes';
    }
}
