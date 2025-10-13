<?php

namespace App\Http\Controllers\Api\Dashboard\Voucher;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Voucher\VoucherRequest;
use App\Models\Voucher;
use App\Services\Api\Dashboard\Voucher\VoucherService;
use Illuminate\Routing\Controllers\HasMiddleware;

class VoucherController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'vouchers.view',
        'show' => 'vouchers.view',
        'store' => 'vouchers.create',
        'update' => 'vouchers.update',
        'destroy' => 'vouchers.delete',
        'toggleActive' => 'vouchers.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(VoucherService $voucherService)
    {
        parent::__construct(
			$voucherService,
			VoucherRequest::class,
			Voucher::class
        );
    }
}


