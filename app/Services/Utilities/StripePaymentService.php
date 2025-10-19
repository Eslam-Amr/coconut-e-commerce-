<?php

namespace App\Services\Utilities;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Payment;
use App\Enums\PaymentStatusEnum;
use App\Enums\AppointmentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StripePaymentService extends BasePaymentService implements PaymentGatewayInterface
{

    protected mixed $api_key;
    public function __construct()
    {
        $this->base_url =env("STRIPE_BASE_URL");
        $this->api_key = env("STRIPE_SECRET_KEY");
        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' =>'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . $this->api_key,
        ];

    }

    public function sendPayment(Request $request): array
    {
        $data = $this->formatData($request);
        $response =$this->buildRequest('POST', '/v1/checkout/sessions', $data, 'form_params');
        // dd($response);
        if($response->getData(true)['success']) {

            return ['success' => true, 'url' => $response->getData(true)['data']['url']];
        }
        return ['success' => false,'url'=>route('payment.failed')];
    }

    public function callBack(Request $request): bool
    {
          $session_id = $request->get('session_id');
          $response=$this->buildRequest('GET','/v1/checkout/sessions/'.$session_id);
        Storage::put('stripe.json',json_encode([
            'callback_response'=>$request->all(),
            'response'=>$response,
        ]));
         if($response->getData(true)['success']&& $response->getData(true)['data']['payment_status']==='paid') {

             return true;
         }
        return false;

    }

    public function formatData($request): array
    {
        // http://127.0.0.1:8000
        // dd($request->getSchemeAndHttpHost().'/payment/success?session_id={CHECKOUT_SESSION_ID}',
        // $request->getSchemeAndHttpHost().'/payment/failed?session_id={CHECKOUT_SESSION_ID}');
        return [
            // "success_url" =>$request->getSchemeAndHttpHost().'/payment/success?session_id={CHECKOUT_SESSION_ID}',
            // "cancel_url" =>$request->getSchemeAndHttpHost().'/payment/failed?session_id={CHECKOUT_SESSION_ID}',
            "success_url" =>env('BASE_PAYMENT_URL').'/payment/success?session_id={CHECKOUT_SESSION_ID}',
            "cancel_url" =>env('BASE_PAYMENT_URL').'/payment/failed?session_id={CHECKOUT_SESSION_ID}',
            "line_items" => [
                [
                    "price_data"=>[
                        "unit_amount" => $request->input('amount')*100,
                        "currency" => $request->input("currency"),
                        "product_data" => [
                            "name" => "product name",
                            "description" => "description of product"
                        ],
                    ],
                    "quantity" => 1,
                ],
            ],
            "mode" => "payment",
        ];
    }

}