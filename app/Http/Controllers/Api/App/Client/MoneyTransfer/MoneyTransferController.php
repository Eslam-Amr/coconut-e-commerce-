<?php

namespace App\Http\Controllers\Api\App\Client\MoneyTransfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Client\MoneyTransfer\MoneyTransferRequest;
use App\Services\Api\App\Client\MoneyTransfer\MoneyTransferService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class MoneyTransferController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;

    protected $moneyTransferService;

    public function __construct(MoneyTransferService $moneyTransferService)
    {
        $this->moneyTransferService = $moneyTransferService;
    }

    public static function middleware(): array
    {
        return [
            'client'
        ];
    }

    /**
     * Create a new money transfer request
     */
    public function createTransfer(MoneyTransferRequest $request)
    {
        return $this->moneyTransferService->createTransfer($request);
    }

    /**
     * Get all money transfers for the authenticated user
     */
    public function getAllTransfers(Request $request)
    {
        return $this->moneyTransferService->getAllTransfers($request);
    }

    /**
     * Get specific money transfer details
     */
    public function getTransferDetails(Request $request, $transferId)
    {
        return $this->moneyTransferService->getTransferDetails($request, $transferId);
    }

    /**
     * Cancel a pending money transfer
     */
    public function cancelTransfer(Request $request, $transferId)
    {
        return $this->moneyTransferService->cancelTransfer($request, $transferId);
    }
}
