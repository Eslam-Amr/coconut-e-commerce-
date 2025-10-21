<?php

namespace App\Services\Api\Dashboard\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Order\OrderStateFactory;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderService
{
    use ApiResponseTrait;

    /**
     * Get paginated list of orders
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int)($request->query('per_page', 20));
            $perPage = $perPage > 0 ? $perPage : 20;

            $query = Order::with(['user', 'items.product', 'items.productVariant'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            $this->applyFilters($query, $request);

            $orders = $query->paginate($perPage);

            // Add status information to each order
            $orders->getCollection()->transform(function ($order) {
                $order->status_info = OrderStateFactory::getStatusInfo($order->status);
                return $order;
            });

            return $this->successResponse($orders, 'Orders retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve orders', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get single order details
     */
    public function show(Order $order)
    {
        try {
            $order->load([
                'user',
                'items.product.translations',
                'items.productVariant.attributeValues.attribute.translations',
                'items.productVariant.attributeValues.translations',
                'voucherUsages.voucher',
                'transactions'
            ]);

            $order->status_info = OrderStateFactory::getStatusInfo($order->status);

            return $this->successResponse($order, 'Order retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve order', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Change order status using state pattern
     */
    public function changeStatus(Order $order, Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:' . implode(',', OrderStatus::values())
            ]);

            $newStatus = $request->input('status');
            $currentState = OrderStateFactory::create($order->status);

            // Check if transition is possible
            if (!$currentState->changeStatus($order, $newStatus)) {
                return $this->errorResponse(
                    'Cannot change order status from ' . $order->status . ' to ' . $newStatus,
                    ['possible_statuses' => $currentState->getPossibleNextStatuses()],
                    422
                );
            }

            // Reload order with relationships
            $order->load(['user', 'items.product']);
            $order->status_info = OrderStateFactory::getStatusInfo($order->status);

            return $this->successResponse($order, 'Order status updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update order status', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get order statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', OrderStatus::PENDING->value)->count(),
                'processing_orders' => Order::where('status', OrderStatus::PROCESSING->value)->count(),
                'shipped_orders' => Order::where('status', OrderStatus::SHIPPED->value)->count(),
                'delivered_orders' => Order::where('status', OrderStatus::DELIVERED->value)->count(),
                'cancelled_orders' => Order::where('status', OrderStatus::CANCELLED->value)->count(),
                'refunded_orders' => Order::where('status', OrderStatus::REFUNDED->value)->count(),
                'total_revenue' => Order::whereIn('status', [
                    OrderStatus::DELIVERED->value,
                    OrderStatus::SHIPPED->value,
                    OrderStatus::PROCESSING->value,
                    OrderStatus::CONFIRMED->value
                ])->sum('total'),
            ];

            return $this->successResponse($stats, 'Order statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve order statistics', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get all possible order statuses
     */
    public function getStatuses()
    {
        try {
            $statuses = [];
            foreach (OrderStatus::values() as $status) {
                $statuses[] = OrderStateFactory::getStatusInfo($status);
            }

            return $this->successResponse($statuses, 'Order statuses retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve order statuses', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request)
    {
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        // Order number filter
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->query('order_number') . '%');
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->query('payment_method'));
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }
    }
}
