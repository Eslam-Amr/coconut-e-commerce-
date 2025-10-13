<?php

namespace App\Http\Controllers\Api\Dashboard\Country;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Country\CountryRequest;
use App\Models\Country;
use App\Services\Api\Dashboard\Country\CountryService;
use Illuminate\Routing\Controllers\HasMiddleware;

class CountryController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'countries.view',
        'show' => 'countries.view',
        'store' => 'countries.create',
        'update' => 'countries.update',
        'destroy' => 'countries.delete',
        'toggleActive' => 'countries.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(CountryService $service)
    {
        parent::__construct(
			$service,
			CountryRequest::class,
			Country::class
        );
    }
}
