<?php

namespace App\Http\Requests\Api\Dashboard\Role;

use App\Http\Requests\Api\MasterRequest;


class AssignPermissionRequest extends MasterRequest
{
    public function rules(): array
    {
        return [
            'permission_id' => ['required', 'integer', 'exists:permissions,id']
        ];
    }

    
}


