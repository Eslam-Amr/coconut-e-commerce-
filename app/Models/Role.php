<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Role extends Model
{
    protected $fillable = ['name', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    // Assign permission to role
    public function givePermissionTo($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();

        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching($permissionModel->id);
        }
    }

    // Remove permission from role
    public function revokePermissionTo($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();

        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }
    }

    // Check if role has permission
    public function hasPermission($permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
