<?php

namespace App\Http\Controllers\Api\Dashboard\Refund;

use App\Http\Controllers\Controller;
use App\Models\RefundedMoney;
use App\Services\Order\OrderCancellationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class RefundController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;

    public static function middleware(): array
    {
        return [
            'admin',
        ];
    }

    /**
     * Get paginated list of refunds
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int)($request->query('per_page', 20));
            $perPage = $perPage > 0 ? $perPage : 20;

            $query = RefundedMoney::with(['order', 'user'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            $this->applyFilters($query, $request);

            $refunds = $query->paginate($perPage);

            return $this->successResponse($refunds, 'Refunds retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve refunds', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get single refund details
     */
    public function show(RefundedMoney $refund)
    {
        try {
            $refund->load(['order.items.product', 'user']);

            return $this->successResponse($refund, 'Refund retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve refund', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update refund status
     */
    public function updateStatus(RefundedMoney $refund, Request $request)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pending,processing,completed,failed',
                'notes' => 'nullable|string|max:1000'
            ]);

            $cancellationService = new OrderCancellationService();
            
            if ($cancellationService->updateRefundStatus(
                $refund->id, 
                $request->input('status'), 
                $request->input('notes')
            )) {
                $refund->refresh();
                return $this->successResponse($refund, 'Refund status updated successfully');
            }

            return $this->errorResponse('Failed to update refund status', [], 500);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update refund status', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get refund statistics
     */
    public function getStats()
    {
        try {
            $cancellationService = new OrderCancellationService();
            $stats = $cancellationService->getRefundStats();

            return $this->successResponse($stats, 'Refund statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve refund statistics', ['error' => $e->getMessage()]);
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

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->query('payment_method'));
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

        // Order filter
        if ($request->filled('order_id')) {
            $query->where('order_id', $request->query('order_id'));
        }
    }
}
