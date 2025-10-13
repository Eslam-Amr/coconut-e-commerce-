<?php

namespace App\Http\Controllers\Api\Dashboard\Permission;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Services\Api\Dashboard\Permission\PermissionService;
use App\Http\Requests\Api\Dashboard\Permission\PermissionRequest;
use App\Http\Requests\Api\Dashboard\Permission\UpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends GenericCrudController
{
    protected static $middleware = ['role:super_admin'];

    public function __construct(PermissionService $permissionService)
    {
        parent::__construct(
            $permissionService,
            PermissionRequest::class,
            Permission::class
        );
    }
}
