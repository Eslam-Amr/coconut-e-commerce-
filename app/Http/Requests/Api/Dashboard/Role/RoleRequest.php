<?php

namespace App\Http\Requests\Api\Dashboard\Role;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RoleRequest extends MasterRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
 
    {
        Log::info('RoleRequest');
        $role = $this->route('role');
        $requireOrSometimes = $this->getRequireOrSometimes();
        return [
            'name' => [$requireOrSometimes,'string','unique:roles,name,' . $role  ,'max:255'],
            'description' => [$requireOrSometimes,'string','max:500'],
            'permissions' => [$requireOrSometimes,'array'],
            'permissions.*' => [$requireOrSometimes,'exists:permissions,id'],
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
            'description.max' => 'The description may not be greater than 500 characters.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.'
        ];
    }
}
