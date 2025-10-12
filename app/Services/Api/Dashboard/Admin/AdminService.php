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
            $query = Admin::with(['roles', 'permissions']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
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
            $admin->load(['roles', 'permissions']);

            return $this->successResponse($admin, 'Admin created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create admin', ['error' => $e->getMessage()]);
        }
    }

    public function show(Admin $admin)
    {
        try {
            $admin->load(['roles', 'permissions']);
            return $this->successResponse($admin, 'Admin retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve admin', ['error' => $e->getMessage()]);
        }
    }

    public function update(Admin $admin, array $data)
    {
        try {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $admin->update($data);
            $admin->load(['roles', 'permissions']);

            return $this->successResponse($admin, 'Admin updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update admin', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Admin $admin)
    {
        try {
            // Prevent deleting the last super admin
            if ($admin->hasRole('super_admin') && Admin::whereHas('roles', function ($q) {
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
            $admin->assignRole($role->name);
            $admin->load(['roles', 'permissions']);

            return $this->successResponse($admin, 'Role assigned successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to assign role', ['error' => $e->getMessage()]);
        }
    }

    public function removeRole(Admin $admin, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $admin->removeRole($role->name);
            $admin->load(['roles', 'permissions']);

            return $this->successResponse($admin, 'Role removed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove role', ['error' => $e->getMessage()]);
        }
    }

    public function grantPermission(Admin $admin, $permissionId)
    {
        try {
            $exists = $admin->roles()->whereHas('permissions', function ($q) use ($permissionId) {
                $q->where('permission_id', $permissionId);
            })->exists();
            if ($exists) {
                $permission = Permission::findOrFail($permissionId);
                $admin->removeDirectPermission($permissionId);
                return $this->errorResponse('Permission found to this role', 400);
            }
            $permission = Permission::findOrFail($permissionId);
            $admin->grantPermission($permission->name);
            $admin->load(['roles', 'permissions']);

            return $this->successResponse($admin, 'Permission granted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to grant permission', ['error' => $e->getMessage()]);
        }
    }

    public function revokePermission(Admin $admin, $permissionId)
    {

        try {
            $exists = $admin->roles()->whereHas('permissions', function ($q) use ($permissionId) {
                $q->where('permission_id', $permissionId);
            })->exists();
            if (!$exists) {
                return $this->errorResponse('Permission not found to this role', 404);
            }
            $permission = Permission::findOrFail($permissionId);

            // Get roles that contain this permission before revoking
            $rolesWithPermission = $admin->roles()->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission->name);
            })->get();

            // Revoke the permission directly (this will also remove roles with the permission)
            $admin->revokePermission($permission->name);
            $admin->load(['roles', 'permissions']);

            $message = 'Permission revoked successfully';
            if ($rolesWithPermission->count() > 0) {
                $message .= ' and ' . $rolesWithPermission->count() . ' related role(s) removed';
            }

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
            $admin->load(['roles', 'permissions']);

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
