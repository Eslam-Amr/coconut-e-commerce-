<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Api\App\Client\Order\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentGatewayTimeoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(OrderService $orderService): void
    {
        $order = Order::find($this->orderId);
        
        if (!$order) {
            Log::warning('PaymentGatewayTimeoutJob: Order not found', ['order_id' => $this->orderId]);
            return;
        }

        // Check if order is still pending payment
        if ($order->payment_status !== 'pending') {
            Log::info('PaymentGatewayTimeoutJob: Order payment already processed', [
                'order_id' => $this->orderId,
                'payment_status' => $order->payment_status
            ]);
            return;
        }

        // Check if order is still in pending status

        Log::info('PaymentGatewayTimeoutJob: Processing timeout for order', [
            'order_id' => $this->orderId,
            'order_number' => $order->order_number
        ]);

        // Handle order failure - this will restore stock, flash sale counts, voucher usage, etc.
        $result = $orderService->handleOrderFailure($this->orderId, 'Payment gateway timeout after 1 hour');
        $order->payment_status = 'failed';
        $order->save();
       
    }

}
