<?php

namespace App\Http\Controllers\Api\Dashboard\Order;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Models\Order;
use App\Services\Api\Dashboard\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OrderController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'orders.view',
        'show' => 'orders.view',
        'changeStatus' => 'orders.update',
        'getStats' => 'orders.view',
        'getStatuses' => 'orders.view',
    ];

    protected static $middleware = ['admin'];

    public function __construct(OrderService $orderService)
    {
        parent::__construct(
            $orderService,
            null, // No specific request class for orders
            Order::class
        );
    }

    // /**
    //  * Get paginated list of orders
    //  */
    // public function index(Request $request)
    // {
    //     return $this->service->index($request);
    // }

    // /**
    //  * Get single order details
    //  */
    // public function show($id)
    // {
    //     $order = Order::findOrFail($id);
    //     return $this->service->show($order);
    // }

    /**
     * Change order status
     */
    public function changeStatus(Order $order, Request $request)
    {
        return $this->service->changeStatus($order, $request);
    }

    /**
     * Get order statistics
     */
    public function getStats()
    {
        return $this->service->getStats();
    }

    /**
     * Get all possible order statuses
     */
    public function getStatuses()
    {
        return $this->service->getStatuses();
    }
}
