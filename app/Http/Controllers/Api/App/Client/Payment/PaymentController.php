<?php

namespace App\Http\Controllers\Api\App\Client\Payment;

use App\Http\Controllers\Controller;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\Utilities\StripePaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected  PaymentGatewayInterface $paymentGateway)
    {
    }


     public function paymentProcess(Request $request)
    {

        return $this->paymentGateway->sendPayment($request);
    }

    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    {
        $response = $this->paymentGateway->callBack($request);
        if ($response) {

            return redirect()->route('payment.success');
        }
        return redirect()->route('payment.failed');
    }



    public function success()
    {

        return view('payment-success');
    }
    public function failed()
    {

        return view('payment-failed');
    }


}
