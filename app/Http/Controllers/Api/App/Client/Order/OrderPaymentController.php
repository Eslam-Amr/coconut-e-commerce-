<?php

namespace App\Http\Controllers\Api\App\Client\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Utilities\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentController extends Controller
{
    protected $paymentService;
    
    public function __construct(OrderPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle successful order payment
     */
    public function success(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $orderId = $request->get('order_id');
            
            // Log the callback attempt
            Log::info('Order payment callback received', [
                'session_id' => $sessionId,
                'order_id' => $orderId,
                'all_params' => $request->all()
            ]);
            
            if (!$sessionId || !$orderId) {
                Log::error('Order payment callback missing parameters', [
                    'session_id' => $sessionId,
                    'order_id' => $orderId
                ]);
                
                return view('payment-failed', [
                    'error' => 'Missing required parameters'
                ]);
            }

            // Get order
            $order = Order::find($orderId);
            if (!$order) {
                return view('payment-failed', [
                    'error' => 'Order not found'
                ]);
            }

            // Check if already processed
            $transaction = $order->transactions()->where('status', 'completed')->first();
            if (!$transaction) {
                return view('payment-success', [
                    'message' => 'Order already processed',
                    'order_number' => $order->order_number,
                    'transaction_id' => $sessionId
                ]);
            }

            // Verify payment with gateway
            $paymentRequest = new Request(['session_id' => $sessionId]);
            $paymentVerified = $this->paymentService->callBack($paymentRequest);

            DB::beginTransaction();

            try {
                if ($paymentVerified) {
                    // Payment successful - update transaction ID if needed
                    $transaction->update([
                        'transaction_id' => $sessionId // Store actual payment gateway transaction ID
                    ]);
                    
                    // Order is already completed, just confirm it
                    // $order->update([
                    //     'status' => 'confirmed'
                    // ]);

                    DB::commit();

                    return view('payment-success', [
                        'message' => 'Order payment completed successfully!',
                        'order_number' => $order->order_number,
                        'transaction_id' => $sessionId,
                        'amount' => $order->total
                    ]);
                } else {
                    // Payment failed
                    $order->update(['payment_status' => 'failed']);
                    $transaction->update(['status' => 'failed']);
                    
                    DB::commit();

                    return view('payment-failed', [
                        'error' => 'Payment verification failed',
                        'order_number' => $order->order_number
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
     * Handle failed order payment
     */
    public function failed(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $orderId = $request->get('order_id');
            
            // Log the failed callback attempt
            Log::info('Order payment failed callback received', [
                'session_id' => $sessionId,
                'order_id' => $orderId,
                'all_params' => $request->all()
            ]);
            
            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    // Use the order service to handle failure properly (restore stock)
                    app(\App\Services\Api\App\Client\Order\OrderService::class)
                        ->handleOrderFailure($orderId, 'Payment cancelled or failed');
                    
                    Log::info('Order payment status updated to failed', [
                        'order_id' => $orderId,
                        'order_number' => $order->order_number
                    ]);
                }
            }

            return view('payment-failed', [
                'error' => 'Payment was cancelled or failed',
                'order_number' => $order->order_number ?? 'Unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in order payment failed callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('payment-failed', [
                'error' => 'Failed to process payment failure: ' . $e->getMessage()
            ]);
        }
    }
}
