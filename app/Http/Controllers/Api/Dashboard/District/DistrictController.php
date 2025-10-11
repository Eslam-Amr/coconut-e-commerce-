<?php

namespace App\Http\Controllers\Api\Dashboard\District;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\District\DistrictRequest;
use App\Models\District;
use App\Services\Api\Dashboard\District\DistrictService;

class DistrictController extends GenericCrudController
{
	public function __construct(DistrictService $service)
    {
        parent::__construct(
			$service,
			DistrictRequest::class,
			District::class
        );
    }
}
