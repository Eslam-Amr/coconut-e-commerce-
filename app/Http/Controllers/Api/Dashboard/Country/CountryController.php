<?php

namespace App\Http\Controllers\Api\Dashboard\Country;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Country\CountryRequest;
use App\Models\Country;
use App\Services\Api\Dashboard\Country\CountryService;

class CountryController extends GenericCrudController
{
	public function __construct(CountryService $service)
    {
        parent::__construct(
			$service,
			CountryRequest::class,
			Country::class
        );
    }
}
