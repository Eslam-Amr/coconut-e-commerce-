<?php

namespace App\Http\Requests\Api\Dashboard\Admin;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
use Illuminate\Support\Facades\Log;

    

class AdminRequest extends MasterRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
Log::info('AdminRequest');
        $adminId = $this->route('admin');
        $requireOrSometimes = $this->getRequireOrSometimes();
        return [
            'name' => [$requireOrSometimes, 'string', 'max:255'],
            'email' => [$requireOrSometimes, 'email', 'unique:users,email,' . $adminId, 'max:255'],
            'phone' => [$requireOrSometimes, 'string', 'unique:users,phone,' . $adminId, 'max:20'],
            'password' => [$requireOrSometimes, 'string', 'min:8', 'confirmed'],
            'password_confirmation' => [$requireOrSometimes, 'string', 'min:8'],
            'language' => [$requireOrSometimes, 'string', 'in:en,ar'],
            // 'notification_status' => 'nullable|boolean',
            // 'roles' => [$requireOrSometimes, 'array'],
            // 'roles.*' => [$requireOrSometimes, 'exists:roles,id'],
            // 'permissions' => [$requireOrSometimes, 'array'],
            // 'permissions.*' => [$requireOrSometimes, 'exists:permissions,id']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    
}
