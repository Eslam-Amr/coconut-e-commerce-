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

            return $this->successResponse($admins, 'Admins retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve admins', ['error' => $e->getMessage()]);
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

            return $this->successResponse($admin, 'Admin created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create admin', ['error' => $e->getMessage()]);
        }
    }

    public function show(Admin $admin)
    {
        try {
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, 'Admin retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve admin', ['error' => $e->getMessage()]);
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

            return $this->successResponse($admin, 'Admin updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update admin', ['error' => $e->getMessage()]);
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
                return $this->errorResponse('Cannot delete the last super admin', 422);
            }

            $admin->delete();
            return $this->successResponse(null, 'Admin deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete admin', ['error' => $e->getMessage()]);
        }
    }

    public function assignRole(Admin $admin, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $result = $admin->assignRole($role->name);
            if (!$result) {
                return $this->errorResponse('Role not found', 404);
            }
            
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, 'Role assigned successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to assign role', ['error' => $e->getMessage()]);
        }
    }

    public function removeRole(Admin $admin, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $result = $admin->removeRole();
            
            if (!$result) {
                return $this->errorResponse('User has no role assigned or role not found', 404);
            }
            
            $admin->load(['role', 'permissions']);
            return $this->successResponse($admin, 'Role removed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove role', ['error' => $e->getMessage()]);
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
            return $this->successResponse($admin, 'Permission granted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to grant permission', ['error' => $e->getMessage()]);
        }
    }

    public function revokePermission(Admin $admin, $permissionId)
    {

        try {
            // Check if admin has a role
            if (!$admin->role) {
                return $this->errorResponse('Admin has no role assigned', 404);
            }

            // Check if the role has this permission
            $permission = Permission::findOrFail($permissionId);
            $hasPermission = $admin->role->permissions()->where('permission_id', $permissionId)->exists();

            if (!$hasPermission) {
                return $this->errorResponse('Permission not found in admin\'s role', 404);
            }

            // Revoke the permission directly
            $admin->revokePermission($permission->name);
            $admin->load(['role', 'permissions']);

            $message = 'Permission revoked successfully';

            return $this->successResponse($admin, $message);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to revoke permission', ['error' => $e->getMessage()]);
        }
    }

    public function removeDirectPermission(Admin $admin, $permissionId)
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            $admin->removeDirectPermission($permission->name);
            $admin->load(['role', 'permissions']);

            return $this->successResponse($admin, 'Direct permission removed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove direct permission', ['error' => $e->getMessage()]);
        }
    }

    public function getAvailableRoles()
    {
        try {
            $roles = Role::all();
            return $this->successResponse($roles, 'Available roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve roles', ['error' => $e->getMessage()]);
        }
    }

    public function getAvailablePermissions()
    {
        try {
            $permissions = Permission::all();
            return $this->successResponse($permissions, 'Available permissions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve permissions', ['error' => $e->getMessage()]);
        }
    }
}
