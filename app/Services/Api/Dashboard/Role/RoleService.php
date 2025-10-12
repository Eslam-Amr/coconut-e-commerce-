<?php

namespace App\Services\Api\Dashboard\Role;

use App\Models\Role;
use App\Models\Permission;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class RoleService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Role::with(['permissions']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            $perPage = $request->integer('per_page', 15);
            $roles = $query->paginate($perPage);

            return $this->successResponse($roles, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve roles', ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $role = Role::create($data);
            $role->permissions()->sync($data['permissions']);
            $role->load(['permissions']);

            return $this->successResponse($role, 'Role created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create role', ['error' => $e->getMessage()]);
        }
    }

    public function show(Role $role)
    {
        try {
            $role->load(['permissions']);
            return $this->successResponse($role, 'Role retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve role', ['error' => $e->getMessage()]);
        }
    }

    public function update(Role $role, array $data)
    {
        try {
            $role->update($data);
            $role->permissions()->sync($data['permissions']);
            $role->load(['permissions']);

            return $this->successResponse($role, 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update role', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Role $role)
    {
        try {
            // Prevent deleting super_admin role
            if ($role->name === 'super_admin') {
                return $this->errorResponse('Cannot delete super admin role', 422);
            }

            // Check if role is assigned to any users
            if ($role->users()->count() > 0) {
                return $this->errorResponse('Cannot delete role that is assigned to users', 422);
            }

            $role->delete();
            return $this->successResponse(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete role', ['error' => $e->getMessage()]);
        }
    }

    public function assignPermission(Role $role, $permissionId)
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            $role->givePermissionTo($permission->name);
            $role->load(['permissions']);

            return $this->successResponse($role, 'Permission assigned successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to assign permission', ['error' => $e->getMessage()]);
        }
    }

    public function removePermission(Role $role, $permissionId)
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            $role->revokePermissionTo($permission->name);
            $role->load(['permissions']);

            return $this->successResponse($role, 'Permission removed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove permission', ['error' => $e->getMessage()]);
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
