<?php

namespace App\Services\Api\Dashboard\Permission;

use App\Models\Permission;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PermissionService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Permission::query();

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            $perPage = $request->integer('per_page', 15);
            $permissions = $query->paginate($perPage);

            return $this->successResponse($permissions, __('messages.permissions_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $permission = Permission::create($data);
            return $this->successResponse($permission, __('messages.permission_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(Permission $permission)
    {
        try {
            return $this->successResponse($permission, __('messages.permission_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(Permission $permission, array $data)
    {
        try {
            $permission->update($data);
            return $this->successResponse($permission, __('messages.permission_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return $this->errorResponse(__('messages.cannot_delete_permission_assigned_to_roles'), 422);
            }

            // Check if permission is assigned to any users
            if ($permission->users()->count() > 0) {
                return $this->errorResponse(__('messages.cannot_delete_permission_assigned_to_users'), 422);
            }

            $permission->delete();
            return $this->successResponse(null, __('messages.permission_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }
}
