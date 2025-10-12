<?php

namespace App\Http\Requests\Api\Dashboard\Admin;

use App\Http\Requests\Api\MasterRequest;
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
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'phone.required' => 'The phone field is required.',
            'phone.string' => 'The phone must be a string.',
            'phone.unique' => 'The phone has already been taken.',
            'phone.max' => 'The phone may not be greater than 20 characters.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password_confirmation.required' => 'The password confirmation field is required.',
            'password_confirmation.string' => 'The password confirmation must be a string.',
            'password_confirmation.min' => 'The password confirmation must be at least 8 characters.',
            'locale.string' => 'The locale must be a string.',
            'locale.in' => 'The locale must be either en or ar.',
            'notification_status.boolean' => 'The notification status must be true or false.',
            'roles.array' => 'The roles must be an array.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.'
        ];
    }
}
