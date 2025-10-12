<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\MediaTrait;
use App\Traits\RolesAndPermissionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MediaTrait,RolesAndPermissionTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'user_type',
        'email_verified_at',
        'locale',
        'notification_status',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope('admin', function (Builder $builder) {
            $builder->where('user_type', 'admin');
        });

    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }





    // ... existing code ...

    // public function roles(): BelongsToMany
    // {
    //     return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
    //         ->withTimestamps();
    // }

    // public function permissions(): BelongsToMany
    // {
    //     return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id')
    //         ->withPivot('granted')
    //         ->withTimestamps();
    // }

    // // Assign role to user
    // public function assignRole($role)
    // {
    //     $roleModel = Role::where('name', $role)->first();
    //     // dd($roleModel);
    //     if ($roleModel) {
    //         $this->roles()->syncWithoutDetaching($roleModel->id);
    //         $this->cleanupPermissionsAfterRoleRemoval($roleModel->permissions()->pluck('name')->toArray(), $roleModel);
    //     }
    // }

    // // Remove role from user
    // public function removeRole($role)
    // {
    //     $roleModel = Role::where('name', $role)->first();

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

    // // Check if user has role
    // public function hasRole($role): bool
    // {
    //     if (is_array($role)) {
    //         return $this->roles()->whereIn('name', $role)->exists();
    //     }

    //     return $this->roles()->where('name', $role)->exists();
    // }

    // // Check if user has any of the roles
    // public function hasAnyRole(array $roles): bool
    // {
    //     return $this->roles()->whereIn('name', $roles)->exists();
    // }

    // // Check if user has all roles
    // public function hasAllRoles(array $roles): bool
    // {
    //     foreach ($roles as $role) {
    //         if (!$this->hasRole($role)) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }

    // // Check if user has permission (considering direct grants/revokes)
    // public function hasPermission($permission): bool
    // {
    //     // Check for explicit revocation
    //     $directPermission = $this->permissions()
    //         ->where('name', $permission)
    //         ->first();

    //     if ($directPermission && !$directPermission->pivot->granted) {
    //         return false; // Explicitly revoked
    //     }

    //     // Check for explicit grant
    //     if ($directPermission && $directPermission->pivot->granted) {
    //         return true; // Explicitly granted
    //     }

    //     // Check role permissions
    //     return $this->roles()->whereHas('permissions', function ($q) use ($permission) {
    //         $q->where('name', $permission);
    //     })->exists();
    // }

    // // Check if user has any of the permissions
    // public function hasAnyPermission(array $permissions): bool
    // {
    //     foreach ($permissions as $permission) {
    //         if ($this->hasPermission($permission)) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // // Check if user has all permissions
    // public function hasAllPermissions(array $permissions): bool
    // {
    //     foreach ($permissions as $permission) {
    //         if (!$this->hasPermission($permission)) {
    //             return false;
    //         }
    //     }
    //     return true;
    // }

    // // Grant permission directly to user
    // public function grantPermission($permission)
    // {
    //     $permissionModel = Permission::where('name', $permission)->first();

    //     if ($permissionModel) {
    //         $this->permissions()->syncWithoutDetaching([
    //             $permissionModel->id => ['granted' => true]
    //         ]);
    //     }
    // }

    // // Revoke permission from user (overrides role permissions)
    // public function revokePermission($permission)
    // {
    //     $permissionModel = Permission::where('name', $permission)->first();

    //     if ($permissionModel) {
    //         // Check if admin has any roles that contain this permission
    //         $rolesWithPermission = $this->roles()->whereHas('permissions', function ($q) use ($permission) {
    //             $q->where('name', $permission);
    //         })->get();

    //         // If admin has roles with this permission, remove those roles
    //         if ($rolesWithPermission->count() > 0) {
    //             foreach ($rolesWithPermission as $role) {
    //                 $this->removeRole($role->name);
    //             }
    //         }

    //         $this->permissions()->syncWithoutDetaching([
    //             $permissionModel->id => ['granted' => false]
    //         ]);
    //     }
    // }

    // // Remove direct permission (reset to role-based only)
    // public function removeDirectPermission($permission)
    // {
    //     $permissionModel = Permission::where('name', $permission)->first();

    //     if ($permissionModel) {
    //         $this->permissions()->detach($permissionModel->id);
    //     }
    // }

    // // Get all effective permissions
    // public function getAllPermissions()
    // {
    //     // Get permissions from roles
    //     $rolePermissions = $this->roles()
    //         ->with('permissions')
    //         ->get()
    //         ->pluck('permissions')
    //         ->flatten()
    //         ->pluck('name')
    //         ->unique();

    //     // Get explicitly revoked permissions
    //     $revokedPermissions = $this->permissions()
    //         ->wherePivot('granted', false)
    //         ->pluck('name');

    //     // Get explicitly granted permissions
    //     $grantedPermissions = $this->permissions()
    //         ->wherePivot('granted', true)
    //         ->pluck('name');

    //     // Merge and filter
    //     return $rolePermissions
    //         ->merge($grantedPermissions)
    //         ->diff($revokedPermissions)
    //         ->unique()
    //         ->values();
    // }

    // private function cleanupPermissionsAfterRoleRemoval(array $rolePermissions, Role $removedRole)
    // {
    //     foreach ($rolePermissions as $permissionName) {
    //         // Check if admin still has this permission via another role
    //         $hasPermissionThroughOtherRoles = $this->roles()
    //             ->whereKeyNot($removedRole->id)
    //             ->whereHas('permissions', fn($q) => $q->where('name', $permissionName))
    //             ->exists();

    //         // Skip if permission still exists through other roles
    //         if ($hasPermissionThroughOtherRoles) {
    //             continue;
    //         }

    //         // Find direct permission link (if any)
    //         $directPermission = $this->permissions()
    //             ->where('name', $permissionName)
    //             ->first();

    //         // If directly granted, remove it
    //         if ($directPermission && $directPermission->pivot->granted) {
    //             $this->removeDirectPermission($permissionName);
    //         }
    //     }
    // }
}
