<?php

namespace App\Http\Controllers\Api\Dashboard\Banner;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Banner\BannerRequest;
use App\Models\Banner;
use App\Services\Api\Dashboard\Banner\BannerService;

class BannerController extends GenericCrudController
{
	public function __construct(BannerService $bannerService)
    {
        parent::__construct(
			$bannerService,
			BannerRequest::class,
			Banner::class
        );
    }
}
