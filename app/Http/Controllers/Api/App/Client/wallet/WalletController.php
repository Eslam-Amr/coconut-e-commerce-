<?php

namespace App\Http\Controllers\Api\App\client\wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Client\Wallet\ChargeWalletRequest;
use App\Services\Api\App\Client\Wallet\WalletService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class WalletController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;

    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public static function middleware(): array
    {
        return [
            'client'
        ];
    }

    public function createWallet()
    {
        return $this->walletService->createWallet();
    }

    /**
     * Charge wallet using payment gateway
     */
    public function chargeWallet(ChargeWalletRequest $request)
    {
        return $this->walletService->chargeWallet($request);
    }


    /**
     * Get wallet balance and transaction history
     */
    public function getWalletInfo(Request $request)
    {
        return $this->walletService->getWalletInfo($request);
    }

    /**
     * Get specific transaction details
     */
    public function getTransactionDetails(Request $request, $transactionId)
    {
        return $this->walletService->getTransactionDetails($request, $transactionId);
    }
}
