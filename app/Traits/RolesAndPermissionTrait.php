<?php

namespace App\Traits;

use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

trait RolesAndPermissionTrait
{
    use ApiResponseTrait;
    // ... existing code ...

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
            ->withPivot('granted')
            ->withTimestamps();
    }

    // Assign role to user
    // public function assignRole($role)
    // {
    //     $roleModel = Role::where('name', $role)->first();

    //     if ($roleModel) {
    //         $this->roles()->syncWithoutDetaching($roleModel->id);

    //         // Optional: Remove any revoked permissions that are now granted through this role
    //         $this->cleanupRevokedPermissionsAfterRoleAssignment($roleModel);
    //     }
    // }
    public function assignRole(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) 
            return $this->errorResponse('Role not found', 404);
        

        $this->update(['role_id' => $role->id]);
        $this->cleanupRevokedPermissionsAfterRoleAssignment($role);
        return $this->successResponse(['message' => 'Role assigned successfully']);
    }

    // Remove role from user
    // public function removeRole($role)
    // {
    //     $roleModel = Role::where('name', $role)->first();
    //     $this->update(['role_id' => null]);

    //     if ($roleModel) {
    //         // Get all permissions that were part of this role
    //         $rolePermissions = $roleModel->permissions()->pluck('name')->toArray();

    //         // Remove the role first
    //         $this->roles()->detach($roleModel->id);

    //         // Clean up permissions that were granted through this role
    //         if (!empty($rolePermissions)) {
    //             $this->cleanupPermissionsAfterRoleRemoval($rolePermissions, $roleModel);
    //         }
    //     }
    // }
    public function removeRole()
    {
        if (!$this->role_id) {
            return $this->errorResponse('User has no role assigned', 404);
        }
        $roleModel = Role::find(Auth::user()->role_id);
        if (!$roleModel) {
            return $this->errorResponse('Role not found', 404);
        }
        $rolePermissions = $roleModel->permissions()->pluck('name')->toArray();
        $this->update(['role_id' => null]);

            // Clean up permissions that were granted through this role
            if (!empty($rolePermissions)) {
                $this->cleanupPermissionsAfterRoleRemoval($rolePermissions, $roleModel);
            }
        return $this->successResponse(['message' => 'Role removed successfully']);
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
            $hasPermissionThroughOtherRoles = $this->role()
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
        if (!$this->role) {
            return false;
        }

        if (is_array($role)) {
            return in_array($this->role->name, $role);
        }

        return $this->role->name === $role;
    }


    // Check if user has any of the roles
    public function hasAnyRole(array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        return in_array($this->role->name, $roles);
    }

    // Check if user has all roles
    public function hasAllRoles(array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        // In a single-role system, user can only have all roles if array contains exactly their role
        foreach ($roles as $roleName) {
            if ($this->role->name !== $roleName) {
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
        if ($this->role) {
            return $this->role->permissions()
                ->where('name', $permission)
                ->exists();
        }

        return false;
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
        if ($this->role) {
            $hasViaRole = $this->role->permissions()
                ->where('name', $permission)
                ->exists();

            if ($hasViaRole) {
                // Ensure no stale direct record remains
                $this->removeDirectPermission($permission);
                return [
                    'ok' => false,
                    'code' => 409,
                    'message' => 'Permission already provided via role',
                ];
            }
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
        $existsInRole = false;
        if ($this->role) {
            $existsInRole = $this->role->permissions()
                ->where('permission_id', $permissionModel->id)
                ->exists();
        }

        if (!$existsInRole) {
            return $this->errorResponse('Permission not found in user\'s role', 404);
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
        // Get permissions from role
        $rolePermissions = collect([]);
        if ($this->role) {
            $rolePermissions = $this->role->permissions()->pluck('name');
        }

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

    // Helper: Get permissions from role
    public function getRolePermissions()
    {
        return $this->role 
            ? $this->role->permissions()->pluck('name') 
            : collect([]);
    }
}
