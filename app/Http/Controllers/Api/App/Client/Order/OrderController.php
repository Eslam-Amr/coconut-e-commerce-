<?php

namespace App\Http\Controllers\Api\App\Client\Order;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Client\Order\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'client'
        ];
    }

    use ApiResponseTrait;


    public function __construct(private OrderService $orderService) {}

    /**
     * Confirm order from cart
     */
    public function confirmOrder(Request $request)
    {
        return $this->orderService->confirmOrder($request);
    }

    /**
     * Get user orders
     */
    public function index(Request $request)
    {
        return $this->orderService->getUserOrders($request);
    }

    /**
     * Get specific order details
     */
    public function show(Request $request, $orderId)
    {
        return $this->orderService->getOrderDetails($request, $orderId);
    }
}
