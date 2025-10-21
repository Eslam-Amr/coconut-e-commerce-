<?php

namespace App\Http\Requests\Api\Dashboard\Role;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
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
    
}
