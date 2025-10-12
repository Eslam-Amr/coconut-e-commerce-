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

            return $this->successResponse($permissions, 'Permissions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve permissions', ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $permission = Permission::create($data);
            return $this->successResponse($permission, 'Permission created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create permission', ['error' => $e->getMessage()]);
        }
    }

    public function show(Permission $permission)
    {
        try {
            return $this->successResponse($permission, 'Permission retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve permission', ['error' => $e->getMessage()]);
        }
    }

    public function update(Permission $permission, array $data)
    {
        try {
            $permission->update($data);
            return $this->successResponse($permission, 'Permission updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update permission', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return $this->errorResponse('Cannot delete permission that is assigned to roles', 422);
            }

            // Check if permission is assigned to any users
            if ($permission->users()->count() > 0) {
                return $this->errorResponse('Cannot delete permission that is assigned to users', 422);
            }

            $permission->delete();
            return $this->successResponse(null, 'Permission deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete permission', ['error' => $e->getMessage()]);
        }
    }
}
