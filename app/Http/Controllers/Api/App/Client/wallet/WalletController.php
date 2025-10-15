<?php

namespace App\Http\Controllers\Api\App\client\wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;
    public static function middleware(): array
    {
        return [
            'client'
        ];
    }

    public function createWallet()
    {
        try {
            $userId = Auth::id();

            // Check if wallet already exists
            $existingWallet = Wallet::where('user_id', $userId)->first();
            if ($existingWallet) {
                return $this->successResponse([
                    'message' => 'Wallet already exists.',
                    'wallet' => $existingWallet
                ]);
            }

            // Create new wallet
            $wallet = Wallet::create([
                'user_id' => $userId,
            ]);

            return $this->successResponse([
                'message' => 'Wallet created successfully.',
                'wallet' => $wallet
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create wallet', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
