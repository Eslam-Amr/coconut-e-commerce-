<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Services\Api\Dashboard\Admin\AdminService;
use App\Http\Requests\Api\Dashboard\Admin\AdminRequest;
use App\Http\Requests\Api\Dashboard\Admin\UpdateAdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends GenericCrudController
{
    protected static $middleware = ['role:super_admin'];

    public function __construct()
    {
        parent::__construct(
            new AdminService(),
            AdminRequest::class,
            Admin::class
        );
    }

    /**
     * Assign role to admin
     */
    public function assignRole(Request $request, Admin $admin)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        return $this->service->assignRole($admin, $request->role_id);
    }

    /**
     * Remove role from admin
     */
    public function removeRole(Request $request, Admin $admin)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        return $this->service->removeRole($admin, $request->role_id);
    }

    /**
     * Grant permission to admin
     */
    public function grantPermission(Request $request, Admin $admin)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id'
        ]);

        return $this->service->grantPermission($admin, $request->permission_id);
    }

    /**
     * Revoke permission from admin
     */
    public function revokePermission(Request $request, Admin $admin)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id'
        ]);

        return $this->service->revokePermission($admin, $request->permission_id);
    }

    /**
     * Remove direct permission from admin
     */
    public function removeDirectPermission(Request $request, Admin $admin)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id'
        ]);

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
}
