<?php

namespace App\Http\Controllers\Api\Dashboard\Banner;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Banner\BannerRequest;
use App\Models\Banner;
use App\Services\Api\Dashboard\Banner\BannerService;
use Illuminate\Routing\Controllers\HasMiddleware;

class BannerController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'banners.view',
        'show' => 'banners.view',
        'store' => 'banners.create',
        'update' => 'banners.update',
        'destroy' => 'banners.delete',
        'toggleActive' => 'banners.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(BannerService $bannerService)
    {
        parent::__construct(
			$bannerService,
			BannerRequest::class,
			Banner::class
        );
    }
}
