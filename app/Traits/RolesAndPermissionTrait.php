<?php

namespace App\Traits;

use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait RolesAndPermissionTrait
{
    use ApiResponseTrait;
    // ... existing code ...

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
            ->withPivot('granted')
            ->withTimestamps();
    }

    // Assign role to user
    public function assignRole($role)
    {
        $roleModel = Role::where('name', $role)->first();

        if ($roleModel) {
            $this->roles()->syncWithoutDetaching($roleModel->id);

            // Optional: Remove any revoked permissions that are now granted through this role
            $this->cleanupRevokedPermissionsAfterRoleAssignment($roleModel);
        }
    }

    // Remove role from user
    public function removeRole($role)
    {
        $roleModel = Role::where('name', $role)->first();

        if ($roleModel) {
            // Get all permissions that were part of this role
            $rolePermissions = $roleModel->permissions()->pluck('name')->toArray();

            // Remove the role first
            $this->roles()->detach($roleModel->id);

            // Clean up permissions that were granted through this role
            if (!empty($rolePermissions)) {
                $this->cleanupPermissionsAfterRoleRemoval($rolePermissions, $roleModel);
            }
        }
    }

    // Helper: Clean up revoked permissions when assigning a role
    private function cleanupRevokedPermissionsAfterRoleAssignment(Role $assignedRole)
    {
        $rolePermissions = $assignedRole->permissions()->pluck('name')->toArray();

        foreach ($rolePermissions as $permissionName) {
            // Find if this permission was explicitly revoked
            $directPermission = $this->permissions()
                ->where('name', $permissionName)
                ->first();

            // If it was revoked (granted = false), remove the revocation
            // since the user now has this permission through the role
            if ($directPermission && !$directPermission->pivot->granted) {
                $this->removeDirectPermission($permissionName);
            }
        }
    }

    // Helper: Clean up granted permissions when removing a role
    private function cleanupPermissionsAfterRoleRemoval(array $rolePermissions, Role $removedRole)
    {
        foreach ($rolePermissions as $permissionName) {
            // Check if user still has this permission via another role
            $hasPermissionThroughOtherRoles = $this->roles()
                ->whereKeyNot($removedRole->id)
                ->whereHas('permissions', fn($q) => $q->where('name', $permissionName))
                ->exists();

            // Skip if permission still exists through other roles
            if ($hasPermissionThroughOtherRoles) {
                continue;
            }

            // Find direct permission link (if any)
            $directPermission = $this->permissions()
                ->where('name', $permissionName)
                ->first();

            // If directly granted, remove it
            if ($directPermission && $directPermission->pivot->granted) {
                $this->removeDirectPermission($permissionName);
            }
        }
    }

    // Check if user has role
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return $this->roles()->whereIn('name', $role)->exists();
        }

        return $this->roles()->where('name', $role)->exists();
    }

    // Check if user has any of the roles
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    // Check if user has all roles
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    // Check if user has permission (considering direct grants/revokes)
    public function hasPermission($permission): bool
    {
        // Check for explicit revocation
        $directPermission = $this->permissions()
            ->where('name', $permission)
            ->first();

        if ($directPermission && !$directPermission->pivot->granted) {
            return false; // Explicitly revoked
        }

        // Check for explicit grant
        if ($directPermission && $directPermission->pivot->granted) {
            return true; // Explicitly granted
        }

        // Check role permissions
        return $this->roles()->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        })->exists();
    }

    // Check if user has any of the permissions
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    // Check if user has all permissions
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    // Grant permission directly to user
    public function grantPermission($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();

        if (!$permissionModel) {
            return [
                'ok' => false,
                'code' => 404,
                'message' => 'Permission not found',
            ];
        }

        // If permission is already provided via any role, do not attach direct grant
        $hasViaRole = $this->roles()->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        })->exists();
        if ($hasViaRole) {
            // Ensure no stale direct record remains
            $this->removeDirectPermission($permission);
            return [
                'ok' => false,
                'code' => 409,
                'message' => 'Permission already provided via role',
            ];
        }

        // Check existing direct pivot state
        $direct = $this->permissions()->where('permission_id', $permissionModel->id)->first();
        if ($direct && $direct->pivot && $direct->pivot->granted) {
            return [
                'ok' => false,
                'code' => 409,
                'message' => 'Permission already granted',
            ];
        }

        // Grant or re-grant
        $this->permissions()->syncWithoutDetaching([
            $permissionModel->id => ['granted' => true]
        ]);

        return [
            'ok' => true,
            'code' => 200,
            'message' => $direct ? 'Permission re-granted' : 'Permission granted',
        ];
    }

    // Revoke permission from user (overrides role permissions)
    public function revokePermission($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        $id = $permissionModel->id;
        $exists = $this->roles()->whereHas('permissions', function ($q) use ($id) {
            $q->where('permission_id', $id);
        })->exists();
        if (!$exists) {
            /*
            if its not inside the role so its alredy revoked 
            */
            return $this->errorResponse('Permission not found to this role', 404);
        }

        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['granted' => false]
            ]);
        }
    }

    // Remove direct permission (reset to role-based only)
    public function removeDirectPermission($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();

        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }
    }

    // Get all effective permissions
    public function getAllPermissions()
    {
        // Get permissions from roles
        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique();

        // Get explicitly revoked permissions
        $revokedPermissions = $this->permissions()
            ->wherePivot('granted', false)
            ->pluck('name');

        // Get explicitly granted permissions
        $grantedPermissions = $this->permissions()
            ->wherePivot('granted', true)
            ->pluck('name');

        // Merge and filter
        return $rolePermissions
            ->merge($grantedPermissions)
            ->diff($revokedPermissions)
            ->unique()
            ->values();
    }
}
