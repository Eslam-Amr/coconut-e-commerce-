<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Api\Dashboard\DashboardStatisticsService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class DashboardStatisticsController extends Controller
{
    use ApiResponseTrait;

    protected $statisticsService;

    public function __construct(DashboardStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Get comprehensive dashboard statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30); // Default to last 30 days
            $limit = (int) $request->get('limit', 1000); // Default limit for large datasets
            
            // Validate limits to prevent abuse
            $limit = min($limit, 5000); // Maximum limit
            $period = min($period, 365); // Maximum period
            
            $statistics = $this->statisticsService->getStatistics($period, $limit);

            return $this->successResponse('Statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get sales analytics with different time periods
     */
    public function getSalesAnalytics(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // day, week, month, year
            $analytics = $this->statisticsService->getSalesAnalytics($period);

            return $this->successResponse('Sales analytics retrieved successfully', $analytics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve sales analytics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get orders statistics only (focused endpoint)
     */
    public function getOrdersStatistics(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $limit = (int) $request->get('limit', 1000);
            
            $limit = min($limit, 5000);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getOrdersStatistics($period, $limit);
            return $this->successResponse('Orders statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve orders statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get profit statistics only (focused endpoint)
     */
    public function getProfitStatistics(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getProfitStatistics($period);
            return $this->successResponse('Profit statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve profit statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get users statistics only (focused endpoint)
     */
    public function getUsersStatistics(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $limit = (int) $request->get('limit', 1000);
            
            $limit = min($limit, 5000);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getUsersStatistics($period, $limit);
            return $this->successResponse('Users statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get products statistics only (focused endpoint)
     */
    public function getProductsStatistics(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 1000);
            $limit = min($limit, 5000);
            
            $statistics = $this->statisticsService->getProductsStatistics($limit);
            return $this->successResponse('Products statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get revenue statistics only (focused endpoint)
     */
    public function getRevenueStatistics(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getRevenueStatistics($period);
            return $this->successResponse('Revenue statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve revenue statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get overview statistics only (focused endpoint)
     */
    public function getOverviewStatistics(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getOverviewStatistics($period);
            return $this->successResponse('Overview statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve overview statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get orders statistics LITE (ultra-fast, essential data only)
     */
    public function getOrdersStatisticsLite(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getOrdersStatisticsLite($period);
            return $this->successResponse('Orders lite statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve orders lite statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get profit statistics LITE (ultra-fast, essential data only)
     */
    public function getProfitStatisticsLite(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getProfitStatisticsLite($period);
            return $this->successResponse('Profit lite statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve profit lite statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get users statistics LITE (ultra-fast, essential data only)
     */
    public function getUsersStatisticsLite(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getUsersStatisticsLite($period);
            return $this->successResponse('Users lite statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users lite statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get products statistics LITE (ultra-fast, essential data only)
     */
    public function getProductsStatisticsLite(Request $request)
    {
        try {
            $statistics = $this->statisticsService->getProductsStatisticsLite();
            return $this->successResponse('Products lite statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products lite statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get revenue statistics LITE (ultra-fast, essential data only)
     */
    public function getRevenueStatisticsLite(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getRevenueStatisticsLite($period);
            return $this->successResponse('Revenue lite statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve revenue lite statistics', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get overview statistics LITE (ultra-fast, essential data only)
     */
    public function getOverviewStatisticsLite(Request $request)
    {
        try {
            $period = (int) $request->get('period', 30);
            $period = min($period, 365);
            
            $statistics = $this->statisticsService->getOverviewStatisticsLite($period);
            return $this->successResponse('Overview lite statistics retrieved successfully', $statistics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve overview lite statistics', ['error' => $e->getMessage()], 500);
        }
    }
}