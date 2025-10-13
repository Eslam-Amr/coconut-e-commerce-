<?php

namespace App\Http\Controllers\Api\Dashboard\City;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\City\CityRequest;
use App\Models\City;
use App\Services\Api\Dashboard\City\CityService;
use Illuminate\Routing\Controllers\HasMiddleware;

class CityController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'cities.view',
        'show' => 'cities.view',
        'store' => 'cities.create',
        'update' => 'cities.update',
        'destroy' => 'cities.delete',
        'toggleActive' => 'cities.toggle_active',
    ];
    protected static $middleware = ['admin'];

    public function __construct(CityService $service)
    {
        parent::__construct(
            $service,
            CityRequest::class,
            City::class
        );
    }
}
