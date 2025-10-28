<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Services\Api\Dashboard\Admin\AdminService;
use App\Http\Requests\Api\Dashboard\Admin\AdminRequest;
use App\Http\Requests\Api\Dashboard\Admin\UpdateAdminRequest;
use App\Http\Requests\Api\Dashboard\Admin\AssignRoleRequest;
use App\Http\Requests\Api\Dashboard\Admin\RemoveRoleRequest;
use App\Http\Requests\Api\Dashboard\Admin\GrantPermissionRequest;
use App\Http\Requests\Api\Dashboard\Admin\RevokePermissionRequest;
use App\Http\Requests\Api\Dashboard\Admin\RemoveDirectPermissionRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class AdminController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'admins.view',
        'show' => 'admins.view',
        'store' => 'admins.create',
        'update' => 'admins.update',
        'destroy' => 'admins.delete',
        'toggleActive' => 'admins.toggle_active',
        'assignRole' => 'admins.assign_role',
        'removeRole' => 'admins.remove_role',
        'grantPermission' => 'admins.grant_permission',
        'revokePermission' => 'admins.revoke_permission',
        'removeDirectPermission' => 'admins.remove_direct_permission',
        'getAvailableRoles' => 'admins.get_available_roles',
        'getAvailablePermissions' => 'admins.get_available_permissions',
        // 'global' => [
        // 	'admin'
        // ]
    ];
    protected static $middleware = ['admin'];



    // protected static $middleware = ['role:super_admin'];

    public function __construct(AdminService $adminService)
    {
        parent::__construct(
            $adminService,
            AdminRequest::class,
            Admin::class
        );
    }

    /**
     * Assign role to admin
     */
    public function assignRole(AssignRoleRequest $request, Admin $admin)
    {
        return $this->service->assignRole($admin, $request->role_id);
    }

    /**
     * Remove role from admin
     */
    public function removeRole(RemoveRoleRequest $request, Admin $admin)
    {
        return $this->service->removeRole($admin, $request->role_id);
    }

    /**
     * Grant permission to admin
     */
    public function grantPermission(GrantPermissionRequest $request, Admin $admin)
    {
        return $this->service->grantPermission($admin, $request->permission_id);
    }

    /**
     * Revoke permission from admin
     */
    public function revokePermission(RevokePermissionRequest $request, Admin $admin)
    {
        return $this->service->revokePermission($admin, $request->permission_id);
    }

    /**
     * Remove direct permission from admin
     */
    public function removeDirectPermission(RemoveDirectPermissionRequest $request, Admin $admin)
    {
        return $this->service->removeDirectPermission($admin, $request->permission_id);
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles()
    {
        return $this->service->getAvailableRoles();
    }

    /**
     * Get available permissions
     */
    public function getAvailablePermissions()
    {
        return $this->service->getAvailablePermissions();
    }

    /**
     * Toggle admin active status
     * Override to use service method with self-deactivation protection
     */
    public function toggleActive($model)
    {
        $serviceResponse = $this->service->toggleActive($model);
        if ($serviceResponse !== null)
            return $serviceResponse;
        return parent::toggleActive($model);
    }
}
