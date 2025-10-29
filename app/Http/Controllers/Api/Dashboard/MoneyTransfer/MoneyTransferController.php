<?php

namespace App\Http\Controllers\Api\Dashboard\MoneyTransfer;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Controllers\Controller;
use App\Services\Api\Dashboard\MoneyTransfer\MoneyTransferService;
use App\Http\Requests\Api\Dashboard\MoneyTransfer\UpdateMoneyTransferStatusRequest;
use App\Models\MoneyTransfer;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class MoneyTransferController extends GenericCrudController implements HasMiddleware
{
    use ApiResponseTrait;

    protected static $permissionsList = [
        'index' => 'money_transfers.view',
        'show' => 'money_transfers.view',
        'update' => 'money_transfers.update_status',
    ];
    protected static $middleware = ['admin'];


	public function __construct(MoneyTransferService $service)
    {
        parent::__construct(
			$service,
			UpdateMoneyTransferStatusRequest::class,
			MoneyTransfer::class
        );
    }

    
}
// class MoneyTransferController extends Controller implements HasMiddleware
// {
//     use ApiResponseTrait;

//     protected static $permissionsList = [
//         'index' => 'money_transfers.view',
//         'show' => 'money_transfers.view',
//         'updateStatus' => 'money_transfers.update_status',
//     ];
//     protected static $middleware = ['admin'];


//     public function __construct(protected MoneyTransferService $service) {}

//     public static function middleware(): array
//     {
//         return ['admin'];
//     }

//     public function index(Request $request)
//     {
//         return $this->service->index($request);
//     }

//     public function show($transferId)
//     {
//         return $this->service->show((int) $transferId);
//     }

//     public function updateStatus(UpdateMoneyTransferStatusRequest $request, $transferId)
//     {
//         return $this->service->updateStatus((int) $transferId, (string) $request->input('status'));
//     }
// }
