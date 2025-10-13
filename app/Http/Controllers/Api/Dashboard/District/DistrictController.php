<?php

namespace App\Http\Controllers\Api\Dashboard\District;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\District\DistrictRequest;
use App\Models\District;
use App\Services\Api\Dashboard\District\DistrictService;
use Illuminate\Routing\Controllers\HasMiddleware;

class DistrictController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'districts.view',
        'show' => 'districts.view',
        'store' => 'districts.create',
        'update' => 'districts.update',
        'destroy' => 'districts.delete',
        'toggleActive' => 'districts.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(DistrictService $service)
    {
        parent::__construct(
			$service,
			DistrictRequest::class,
			District::class
        );
    }
}
