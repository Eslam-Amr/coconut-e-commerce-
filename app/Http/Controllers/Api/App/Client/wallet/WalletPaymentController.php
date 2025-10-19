<?php

namespace App\Http\Controllers\Api\App\Client\wallet;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\Utilities\WalletPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletPaymentController extends Controller
{
    protected $paymentService;
    
    public function __construct(WalletPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle successful wallet payment
     */
    public function success(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $walletTransactionId = $request->get('wallet_transaction_id');
            
            
            if (!$sessionId || !$walletTransactionId) {
                
                return view('payment-failed', [
                    'error' => 'Missing required parameters'
                ]);
            }

            // Get wallet transaction
            $walletTransaction = WalletTransaction::find($walletTransactionId);
            if (!$walletTransaction) {
                return view('payment-failed', [
                    'error' => 'Transaction not found'
                ]);
            }

            // Check if already processed
            if ($walletTransaction->status !== 'pending') {
                return view('payment-success', [
                    'message' => 'Transaction already processed',
                    'transaction_id' => $walletTransaction->transaction_id,
                    'amount' => $walletTransaction->amount
                ]);
            }

            // Verify payment with gateway
            $paymentRequest = new Request(['session_id' => $sessionId]);
            $paymentVerified = $this->paymentService->callBack($paymentRequest);
DB::beginTransaction();

            try {
                if ($paymentVerified) {
                    Log::info('Payment verified, updating wallet', [
                        'wallet_transaction_id' => $walletTransactionId,
                        'current_status' => $walletTransaction->status
                    ]);

                    // Payment successful - update wallet
                    $wallet = $walletTransaction->wallet;
                    $oldBalance = $wallet->balance;
                    $newBalance = $wallet->balance + $walletTransaction->amount;
                    
                    $wallet->update([
                        'balance' => $newBalance,
                        // 'last_change' => $walletTransaction->amount
                    ]);


                    // Update transaction status with actual payment gateway transaction ID
                    $walletTransaction->update([
                        'status' => 'completed',
                        'balance_after' => $newBalance,
                        'transaction_id' => $sessionId // Use the actual payment gateway session ID
                    ]);


                    DB::commit();


                    return view('payment-success', [
                        'message' => 'Wallet charged successfully!',
                        'transaction_id' => $sessionId, // Use the actual payment gateway session ID
                        'amount' => $walletTransaction->amount,
                        'new_balance' => $newBalance
                    ]);
                } else {

                    // Payment failed
                    $walletTransaction->update(['status' => 'failed']);
                    
                    DB::commit();

                    return view('payment-failed', [
                        'error' => 'Payment verification failed',
                        'transaction_id' => $sessionId // Use the actual payment gateway session ID
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return view('payment-failed', [
                'error' => 'Failed to process payment: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle failed wallet payment
     */
    public function failed(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $walletTransactionId = $request->get('wallet_transaction_id');
            
            if ($walletTransactionId) {
                $walletTransaction = WalletTransaction::find($walletTransactionId);
                if ($walletTransaction && $walletTransaction->status === 'pending') {
                    $walletTransaction->update(['status' => 'failed']);
                }
            }

            return view('payment-failed', [
                'error' => 'Payment was cancelled or failed',
                'transaction_id' => $sessionId ?? 'Unknown'
            ]);

        } catch (\Exception $e) {
            return view('payment-failed', [
                'error' => 'Failed to process payment failure: ' . $e->getMessage()
            ]);
        }
    }
}
