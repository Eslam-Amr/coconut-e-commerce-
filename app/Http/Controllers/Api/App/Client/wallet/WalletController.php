<?php

namespace App\Http\Controllers\Api\App\client\wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Utilities\WalletPaymentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;
    
    protected $paymentService;
    
    public function __construct(WalletPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
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

    /**
     * Charge wallet using payment gateway
     */
    public function chargeWallet(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:1|max:10000',
                'currency' => 'required|string|in:USD,EUR,GBP'
            ]);

            $userId = Auth::id();
            $amount = $request->amount;
            $currency = $request->currency;

            // Get or create wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );

            // Create pending wallet transaction (transaction_id will be updated with payment gateway ID)
            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                // 'type' => 'credit',
                'user_id' => $userId,
                'amount' => $amount,
                'description' => 'Wallet top-up via payment gateway',
                'balance_after' => $wallet->balance,
                'transaction_id' => 'PENDING-' . $wallet->id . '-' . time(), // Temporary ID
                'payment_method' => 'credit_card',
                'status' => 'pending'
            ]);

            // Prepare payment request
            $paymentRequest = new Request([
                'amount' => $amount,
                'currency' => $currency,
                'wallet_transaction_id' => $walletTransaction->id
            ]);

            // Process payment through gateway
            $paymentResult = $this->paymentService->sendPayment($paymentRequest);
// dd($paymentResult);
            if ($paymentResult['success']) {
                // Log the payment initiation for debugging
                Log::info('Wallet payment initiated', [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'wallet_transaction_id' => $walletTransaction->id,
                    'payment_url' => $paymentResult['url']
                ]);

                return $this->successResponse('Payment initiated successfully', [
                    'payment_url' => $paymentResult['url'],
                    'wallet_transaction_id' => $walletTransaction->id
                ]);
            } else {
                // Update transaction status to failed
                $walletTransaction->update(['status' => 'failed']);
                
                Log::error('Wallet payment initiation failed', [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'wallet_transaction_id' => $walletTransaction->id,
                    'payment_result' => $paymentResult
                ]);
                
                return $this->errorResponse('Payment initiation failed', [
                    'wallet_transaction_id' => $walletTransaction->id
                ], 400);
            }

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to charge wallet', [
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Get wallet balance and transaction history
     */
    public function getWalletInfo(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $wallet = Wallet::where('user_id', $userId)->first();
            if (!$wallet) {
                return $this->errorResponse('Wallet not found', [], 404);
            }

            $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return $this->successResponse('Wallet information retrieved successfully', [
                'wallet' => [
                    'id' => $wallet->id,
                    'balance' => $wallet->balance,
                    'created_at' => $wallet->created_at
                ],
                'transactions' => $transactions
            ]);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve wallet information', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get specific transaction details
     */
    public function getTransactionDetails(Request $request, $transactionId)
    {
        try {
            $userId = Auth::id();
            
            $wallet = Wallet::where('user_id', $userId)->first();
            if (!$wallet) {
                return $this->errorResponse('Wallet not found', [], 404);
            }

            $transaction = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('transaction_id', $transactionId)
                ->first();

            if (!$transaction) {
                return $this->errorResponse('Transaction not found', [], 404);
            }

            return $this->successResponse('Transaction details retrieved successfully', $transaction);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve transaction details', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
