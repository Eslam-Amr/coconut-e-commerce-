<?php

namespace App\Http\Controllers\Api\Dashboard\Role;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Services\Api\Dashboard\Role\RoleService;
use App\Http\Requests\Api\Dashboard\Role\RoleRequest;
use App\Http\Requests\Api\Dashboard\Role\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends GenericCrudController
{

	protected static $permissionsList = [
		'index' => 'roles.view',
		'show' => 'roles.view',
		'store' => 'roles.create',
		'update' => 'roles.update',
		'destroy' => 'roles.delete',
		'toggleActive' => 'roles.toggle_active',
		// 'global' => [
		// 	'admin'
		// ]
	];
    protected static $middleware = ['admin'];



    // protected static $middleware = ['role:super_admin'];

    public function __construct(RoleService $roleService)
    {
        parent::__construct(
            $roleService,
            RoleRequest::class,
            Role::class
        );
    }

    /**
     * Assign permission to role
     */
    public function assignPermission(Request $request, Role $role)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id'
        ]);

        return $this->service->assignPermission($role, $request->permission_id);
    }

    /**
     * Remove permission from role
     */
    public function removePermission(Request $request, Role $role)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id'
        ]);

        return $this->service->removePermission($role, $request->permission_id);
    }

    /**
     * Get available permissions
     */
    public function getAvailablePermissions()
    {
        return $this->service->getAvailablePermissions();
    }
}
