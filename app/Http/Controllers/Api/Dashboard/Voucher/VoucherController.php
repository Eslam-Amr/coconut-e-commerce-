<?php

namespace App\Http\Controllers\Api\Dashboard\Voucher;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Voucher\VoucherRequest;
use App\Models\Voucher;
use App\Services\Api\Dashboard\Voucher\VoucherService;

class VoucherController extends GenericCrudController
{
	public function __construct(VoucherService $voucherService)
    {
        parent::__construct(
			$voucherService,
			VoucherRequest::class,
			Voucher::class
        );
    }
}


