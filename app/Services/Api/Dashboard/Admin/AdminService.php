<?php

namespace App\Services\Api\Dashboard\Admin;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Permission;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Admin::with(['role', 'permissions']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('role')) {
                $query->whereHas('role', function ($q) use ($request) {
                    $q->where('name', $request->get('role'));
                });
            }

            $perPage = $request->integer('per_page', 15);
            $admins = $query->paginate($perPage);

            return $this->successResponse($admins, __('messages.admins_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $admin = Admin::create($data);
            $admin->load(['role', 'permissions']);

            return $this->successResponse($admin, __('messages.admin_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(Admin $admin)
    {
        try {
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, __('messages.admin_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(Admin $admin, array $data)
    {
        try {
            // Get the currently authenticated admin
            $currentAdmin = auth('admin')->user();

            // Prevent admin from deactivating themselves
            if ($currentAdmin && $currentAdmin->id === $admin->id && isset($data['active']) && $data['active'] == false) {
                return $this->errorResponse(__('messages.cannot_deactivate_self'), 422);
            }

            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $admin->update($data);
            $admin->load(['role', 'permissions']);

            return $this->successResponse($admin, __('messages.admin_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle admin active status with self-deactivation protection
     */
    /**
     * Check if admin can toggle active status
     * Returns error response if not allowed, null if allowed
     */
    public function toggleActive($model)
    {
        if (is_string($model)) {
            $model = Admin::findOrFail($model);
        }
        
        // Get the currently authenticated admin
        $currentAdmin = auth('admin')->user();

        // Prevent admin from deactivating themselves
        if ($currentAdmin && $currentAdmin->id === $model->id && $model->active) {
            return response()->json([
                'success' => false,
                'message' => __('messages.cannot_deactivate_self'),
                'details' => [
                    'error_type' => 'validation_error',
                    'error_code' => 'SELF_DEACTIVATION_NOT_ALLOWED'
                ]
            ], 422);
        }
        
        // If allowed, return null to indicate proceed
        return null;
    }

    public function destroy(Admin $admin)
    {
        try {
            // Prevent deleting the last super admin
            if ($admin->hasRole('super_admin') && Admin::whereHas('role', function ($q) {
                $q->where('name', 'super_admin');
            })->count() <= 1) {
                return $this->errorResponse(__('messages.cannot_delete_last_super_admin'), 422);
            }

            $admin->delete();
            return $this->successResponse(null, __('messages.admin_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function assignRole(Admin $admin, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $result = $admin->assignRole($role->name);
            if (!$result) {
                return $this->errorResponse(__('messages.not_found'), 404);
            }
            
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, __('messages.role_assigned'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_assign_role'), ['error' => $e->getMessage()]);
        }
    }

    public function removeRole(Admin $admin, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $result = $admin->removeRole();
            
            if (!$result) {
                return $this->errorResponse(__('messages.not_found'), 404);
            }
            
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, __('messages.role_removed_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_remove_role'), ['error' => $e->getMessage()]);
        }
    }

    public function grantPermission(Admin $admin, $permissionId)
    {
        try {
            // $exists = $admin->roles()->whereHas('permissions', function ($q) use ($permissionId) {
            //     $q->where('permission_id', $permissionId);
            // })->exists();
            // if ($exists) {
            //     $permission = Permission::findOrFail($permissionId);
            //     $admin->removeDirectPermission($permissionId);
            //     return $this->errorResponse('Permission found to this role', 400);
            // }
            $permission = Permission::findOrFail($permissionId);
            $result = $admin->grantPermission($permission->name);

            // If trait returns structured array
            if (is_array($result)) {
                if ($result['ok'] === true) {
                    $admin->load(['role', 'permissions']);
                    return $this->successResponse($admin, $result['message']);
                }
                // dd($result);
                return $this->errorResponse($result['message'], code: $result['code'] ?? 400);
            }

            // Fallback: assume success
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, __('messages.permission_granted'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function revokePermission(Admin $admin, $permissionId)
    {

        try {
            // Check if admin has a role
            if (!$admin->role) {
                return $this->errorResponse(__('messages.admin_has_no_role_assigned'), 404);
            }

            // Check if the role has this permission
            $permission = Permission::findOrFail($permissionId);
            $hasPermission = $admin->role->permissions()->where('permission_id', $permissionId)->exists();

            if (!$hasPermission) {
                return $this->errorResponse(__('messages.permission_not_found_in_admin_role'), 404);
            }

            // Revoke the permission directly
            $admin->revokePermission($permission->name);
            $admin->load(['role', 'permissions']);

            $message = __('messages.permission_revoked');

            return $this->successResponse($admin, $message);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_revoke_permission'), ['error' => $e->getMessage()]);
        }
    }

    public function removeDirectPermission(Admin $admin, $permissionId)
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            $admin->removeDirectPermission($permission->name);
            $admin->load(['role', 'permissions']);

            return $this->successResponse($admin, __('messages.direct_permission_removed_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_remove_direct_permission'), ['error' => $e->getMessage()]);
        }
    }

    public function getAvailableRoles()
    {
        try {
            $roles = Role::all();
            return $this->successResponse($roles, __('messages.available_roles_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function getAvailablePermissions()
    {
        try {
            $permissions = Permission::all();
            return $this->successResponse($permissions, __('messages.available_permissions_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }
}
