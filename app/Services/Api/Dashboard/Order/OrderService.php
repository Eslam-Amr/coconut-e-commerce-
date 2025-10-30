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
                $order->status_info = OrderStateFactory::getStatusInfo($order->status->value);
                return $order;
            });

            return $this->successResponse($orders, __('messages.orders_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_retrieve_orders'), ['error' => $e->getMessage()]);
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

            $order->status_info = OrderStateFactory::getStatusInfo($order->status->value);

            return $this->successResponse($order, __('messages.order_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_retrieve_order'), ['error' => $e->getMessage()]);
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
            $currentState = OrderStateFactory::create($order->status->value);

            // Check if transition is possible
            if (!$currentState->changeStatus($order, $newStatus)) {
                return $this->errorResponse(
                    'Cannot change order status from ' . $order->status->value . ' to ' . $newStatus,
                    ['possible_statuses' => $currentState->getPossibleNextStatuses()],
                    422
                );
            }

            // Reload order with relationships
            $order->load(['user', 'items.product']);
            $order->status_info = OrderStateFactory::getStatusInfo($order->status->value);

            return $this->successResponse($order, __('messages.order_status_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_update_order_status'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get order statistics
     */
    public function getStats()
    {
        try {
            $stats = Order::selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status IN (?, ?, ?, ?) THEN total ELSE 0 END) as total_revenue
            ', [
                OrderStatus::PENDING->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::SHIPPED->value,
                OrderStatus::DELIVERED->value,
                OrderStatus::CANCELLED->value,
                OrderStatus::DELIVERED->value,
                OrderStatus::SHIPPED->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::CONFIRMED->value
            ])->first();

            return $this->successResponse($stats, __('messages.order_statistics_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_retrieve_order_statistics'), ['error' => $e->getMessage()]);
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

            return $this->successResponse($statuses, __('messages.order_statuses_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.failed_to_retrieve_order_statuses'), ['error' => $e->getMessage()]);
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
