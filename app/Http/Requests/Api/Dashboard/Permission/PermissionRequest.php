<?php

namespace App\Http\Requests\Api\Dashboard\Permission;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
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
    
}
