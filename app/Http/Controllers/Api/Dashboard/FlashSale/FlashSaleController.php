<?php

namespace App\Http\Controllers\Api\Dashboard\FlashSale;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\FlashSale\FlashSaleRequest;
use App\Models\FlashSale;
use App\Services\Api\Dashboard\FlashSale\FlashSaleService;
use Illuminate\Routing\Controllers\HasMiddleware;

class FlashSaleController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'flash_sales.view',
        'show' => 'flash_sales.view',
        'store' => 'flash_sales.create',
        'update' => 'flash_sales.update',
        'destroy' => 'flash_sales.delete',
        'toggleActive' => 'flash_sales.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(FlashSaleService $service)
    {
        parent::__construct(
			$service,
			FlashSaleRequest::class,
			FlashSale::class
        );
    }
}


