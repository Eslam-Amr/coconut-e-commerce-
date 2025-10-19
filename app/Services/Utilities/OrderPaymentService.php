<?php

namespace App\Services\Utilities;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    protected mixed $api_key;
    
    public function __construct()
    {
        $this->base_url = env("STRIPE_BASE_URL");
        $this->api_key = env("STRIPE_SECRET_KEY");
        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $this->api_key,
        ];
    }

    public function sendPayment(Request $request): array
    {
        $data = $this->formatData($request);
        $response = $this->buildRequest('POST', '/v1/checkout/sessions', $data, 'form_params');
        
        if ($response->getData(true)['success']) {
            return ['success' => true, 'url' => $response->getData(true)['data']['url']];
        }
        
        return ['success' => false, 'url' => route('order.payment.failed')];
    }

    public function callBack(Request $request): bool
    {
        $session_id = $request->get('session_id');
        $response = $this->buildRequest('GET', '/v1/checkout/sessions/' . $session_id);
        
        $responseData = $response->getData(true);
        
        Storage::put('order_stripe.json', json_encode([
            'callback_response' => $request->all(),
            'response' => $responseData,
        ]));
        
        Log::info('Order payment verification', [
            'session_id' => $session_id,
            'response_success' => $responseData['success'] ?? false,
            'payment_status' => $responseData['data']['payment_status'] ?? 'unknown',
            'full_response' => $responseData
        ]);
        
        if ($responseData['success'] && $responseData['data']['payment_status'] === 'paid') {
            Log::info('Order payment verification successful');
            return true;
        }
        
        Log::error('Order payment verification failed', [
            'success' => $responseData['success'] ?? false,
            'payment_status' => $responseData['data']['payment_status'] ?? 'unknown'
        ]);
        
        return false;
    }

    public function formatData($request): array
    {
        $orderId = $request->input('order_id');
        $orderNumber = $request->input('order_number');
        
        return [
            "success_url" => env('BASE_PAYMENT_URL') . "/order/payment/success?session_id={CHECKOUT_SESSION_ID}&order_id={$orderId}",
            "cancel_url" => env('BASE_PAYMENT_URL') . "/order/payment/failed?session_id={CHECKOUT_SESSION_ID}&order_id={$orderId}",
            "line_items" => [
                [
                    "price_data" => [
                        "unit_amount" => $request->input('amount') * 100,
                        "currency" => $request->input("currency"),
                        "product_data" => [
                            "name" => "Order Payment",
                            "description" => "Payment for order #{$orderNumber}"
                        ],
                    ],
                    "quantity" => 1,
                ],
            ],
            "mode" => "payment",
        ];
    }
}
