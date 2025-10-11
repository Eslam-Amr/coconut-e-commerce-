<?php

namespace App\Http\Controllers\Api\Dashboard\Brand;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Brand\BrandRequest;
use App\Models\Brand;
use App\Services\Api\Dashboard\Brand\BrandService;

class BrandController extends GenericCrudController
{
    public function __construct(BrandService $brandService)
    {
        parent::__construct(
            $brandService,
            BrandRequest::class,
            Brand::class
        );
    }
}


