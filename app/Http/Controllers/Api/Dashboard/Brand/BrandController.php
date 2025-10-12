<?php

namespace App\Http\Controllers\Api\Dashboard\Brand;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Brand\BrandRequest;
use App\Models\Brand;
use App\Services\Api\Dashboard\Brand\BrandService;
use Illuminate\Routing\Controllers\HasMiddleware;

class BrandController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'brands.view',
        'show' => 'brands.view',
        'store' => 'brands.create',
        'update' => 'brands.update',
        'destroy' => 'brands.delete',
        'toggleActive' => 'brands.toggle_active',
        // 'global' => [
        //     'admin'
        // ]
    ];
    protected static $middleware = ['admin'];

    public function __construct(BrandService $brandService)
    {
        parent::__construct(
            $brandService,
            BrandRequest::class,
            Brand::class
        );
    }
}


