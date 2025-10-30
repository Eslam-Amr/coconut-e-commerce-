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

            return $this->successResponse($roles, __('messages.roles_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $role = Role::create($data);
            $role->permissions()->sync($data['permissions']);
            $role->load(['permissions']);

            return $this->successResponse($role, __('messages.role_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(Role $role)
    {
        try {
            $role->load(['permissions']);
            return $this->successResponse($role, __('messages.role_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(Role $role, array $data)
    {
        try {
            $role->update($data);
            $role->permissions()->sync($data['permissions']);
            $role->load(['permissions']);

            return $this->successResponse($role, __('messages.role_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Role $role)
    {
        try {
            // Prevent deleting super_admin role
            if ($role->name === 'super_admin') {
                return $this->errorResponse(__('messages.cannot_delete_super_admin_role'), 422);
            }

            // Check if role is assigned to any users
            if ($role->users()->count() > 0) {
                return $this->errorResponse(__('messages.cannot_delete_role_assigned_to_users'), 422);
            }

            $role->delete();
            return $this->successResponse(null, __('messages.role_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function assignPermission(Role $role, $permissionId)
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            $role->givePermissionTo($permission->name);
            $role->load(['permissions']);

            return $this->successResponse($role, __('messages.permission_assigned_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_assign_permission'), ['error' => $e->getMessage()]);
        }
    }

    public function removePermission(Role $role, $permissionId)
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            $role->revokePermissionTo($permission->name);
            $role->load(['permissions']);

            return $this->successResponse($role, __('messages.permission_removed_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_remove_permission'), ['error' => $e->getMessage()]);
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
