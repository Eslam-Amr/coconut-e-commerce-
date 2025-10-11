<?php

namespace App\Http\Controllers\Api\Dashboard\City;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\City\CityRequest;
use App\Models\City;
use App\Services\Api\Dashboard\City\CityService;

class CityController extends GenericCrudController
{
    public function __construct(CityService $service)
    {
        parent::__construct(
            $service,
            CityRequest::class,
            City::class
        );
    }
}
