<?php

namespace App\Http\Requests\Api\Dashboard\Permission;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends MasterRequest
{
   

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
   
        $requireOrSometimes = $this->getRequireOrSometimes();
        $permission = $this->route('permission');
        return [
            'name' => [$requireOrSometimes,'string','unique:permissions,name,' . $permission->id , 'max:255'],
            'description' => [$requireOrSometimes,'string','max:500'],
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
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.unique' => 'The name has already been taken.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 500 characters.'
        ];
    }
}
