<?php

namespace App\Http\Controllers\Api\Dashboard\FlashSale;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\FlashSale\FlashSaleRequest;
use App\Models\FlashSale;
use App\Services\Api\Dashboard\FlashSale\FlashSaleService;

class FlashSaleController extends GenericCrudController
{
	public function __construct(FlashSaleService $service)
    {
        parent::__construct(
			$service,
			FlashSaleRequest::class,
			FlashSale::class
        );
    }
}


