<?php

namespace App\Services\Utilities;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WalletPaymentService extends BasePaymentService implements PaymentGatewayInterface
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
        
        return ['success' => false, 'url' => route('wallet.payment.failed')];
    }

    public function callBack(Request $request): bool
    {
        $session_id = $request->get('session_id');
        $response = $this->buildRequest('GET', '/v1/checkout/sessions/' . $session_id);
        
        $responseData = $response->getData(true);
        
        Storage::put('wallet_stripe.json', json_encode([
            'callback_response' => $request->all(),
            'response' => $responseData,
        ]));
        
        Log::info('Stripe callback verification', [
            'session_id' => $session_id,
            'response_success' => $responseData['success'] ?? false,
            'payment_status' => $responseData['data']['payment_status'] ?? 'unknown',
            'full_response' => $responseData
        ]);
        
        if ($responseData['success'] && $responseData['data']['payment_status'] === 'paid') {
            Log::info('Payment verification successful');
            return true;
        }
        
        Log::error('Payment verification failed', [
            'success' => $responseData['success'] ?? false,
            'payment_status' => $responseData['data']['payment_status'] ?? 'unknown'
        ]);
        
        return false;
    }

    public function formatData($request): array
    {
        $walletTransactionId = $request->input('wallet_transaction_id');
        
        return [
            "success_url" => env('BASE_PAYMENT_URL') . "/wallet/payment/success?session_id={CHECKOUT_SESSION_ID}&wallet_transaction_id={$walletTransactionId}",
            "cancel_url" => env('BASE_PAYMENT_URL') . "/wallet/payment/failed?session_id={CHECKOUT_SESSION_ID}&wallet_transaction_id={$walletTransactionId}",
            "line_items" => [
                [
                    "price_data" => [
                        "unit_amount" => $request->input('amount') * 100,
                        "currency" => $request->input("currency"),
                        "product_data" => [
                            "name" => "Wallet Top-up",
                            "description" => "Adding funds to your wallet"
                        ],
                    ],
                    "quantity" => 1,
                ],
            ],
            "mode" => "payment",
        ];
    }
}
