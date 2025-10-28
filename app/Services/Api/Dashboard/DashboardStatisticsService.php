<?php

namespace App\Services\Api\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardStatisticsService
{
    /**
     * Get comprehensive dashboard statistics with large dataset optimization
     */
    public function getStatistics(int $period = 30, int $limit = 1000): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            // Set memory limit for large datasets
            ini_set('memory_limit', '512M');
            
            // Set execution time limit
            set_time_limit(60);
            
            return [
                'overview' => $this->getOverviewStats($startDate, $endDate),
                'orders' => $this->getOrderStats($startDate, $endDate, $limit),
                'revenue' => $this->getRevenueStats($startDate, $endDate),
                'products' => $this->getProductStats($limit),
                'users' => $this->getUserStats($startDate, $endDate, $limit),
                'low_stock' => $this->getLowStockStats($limit),
                'recent_activity' => $this->getRecentActivity($startDate, $endDate, $limit),
                'profit_analytics' => $this->getProfitAnalytics($startDate, $endDate),
                'growth_metrics' => $this->getGrowthMetrics($startDate, $endDate),
            ];
        } catch (\Exception $e) {
            // Log error and return minimal data
            Log::error('Dashboard statistics error: ' . $e->getMessage());
            
            return [
                'overview' => $this->getMinimalOverviewStats($startDate, $endDate),
                'orders' => ['by_status' => [], 'daily_trends' => [], 'payment_methods' => []],
                'revenue' => ['summary' => (object)[], 'monthly_comparison' => []],
                'products' => ['overview' => (object)[], 'top_selling' => [], 'low_stock' => []],
                'users' => ['overview' => (object)[], 'registration_trends' => [], 'top_customers' => [], 'activity' => (object)[]],
                'low_stock' => (object)[],
                'recent_activity' => ['recent_orders' => [], 'recent_users' => []],
                'profit_analytics' => ['summary' => (object)[], 'by_status' => [], 'monthly_trends' => []],
                'growth_metrics' => ['current_period' => (object)[], 'previous_period' => (object)[], 'growth_percentages' => [], 'period_comparison' => []],
                'error' => 'Statistics temporarily unavailable due to large dataset'
            ];
        }
    }

    /**
     * Get minimal overview stats for error fallback
     */
    private function getMinimalOverviewStats(Carbon $startDate, Carbon $endDate): object
    {
        $minimal = DB::select("
            SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                0 as processing_orders,
                0 as shipped_orders,
                (SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at <= ?) as new_users,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM product_variants WHERE stock <= minimum_stock LIMIT 1000) as low_stock_items,
                COALESCE(SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END), 0) as total_revenue,
                COALESCE(AVG(CASE WHEN status = 'delivered' THEN total END), 0) as average_order_value
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            LIMIT 1
        ", [$startDate, $endDate, $startDate, $endDate]);

        return $minimal[0] ?? (object)[
            'total_orders' => 0,
            'completed_orders' => 0,
            'pending_orders' => 0,
            'cancelled_orders' => 0,
            'processing_orders' => 0,
            'shipped_orders' => 0,
            'new_users' => 0,
            'total_users' => 0,
            'low_stock_items' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0,
        ];
    }

    /**
     * Get overview statistics with optimized queries
     */
    public function getOverviewStats(Carbon $startDate, Carbon $endDate): object
    {
        $overview = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ?) as total_orders,
                (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'delivered') as completed_orders,
                (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'pending') as pending_orders,
                (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'cancelled') as cancelled_orders,
                (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'processing') as processing_orders,
                (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'shipped') as shipped_orders,
                (SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at <= ?) as new_users,
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM product_variants WHERE stock <= minimum_stock) as low_stock_items,
                (SELECT SUM(total) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'delivered') as total_revenue,
                (SELECT AVG(total) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'delivered') as average_order_value
        ", [
            $startDate, $endDate, // total_orders
            $startDate, $endDate, // completed_orders
            $startDate, $endDate, // pending_orders
            $startDate, $endDate, // cancelled_orders
            $startDate, $endDate, // processing_orders
            $startDate, $endDate, // shipped_orders
            $startDate, $endDate, // new_users
            $startDate, $endDate, // total_revenue
            $startDate, $endDate, // average_order_value
        ]);

        return $overview[0] ?? (object)[
            'total_orders' => 0,
            'completed_orders' => 0,
            'pending_orders' => 0,
            'cancelled_orders' => 0,
            'processing_orders' => 0,
            'shipped_orders' => 0,
            'new_users' => 0,
            'total_users' => 0,
            'low_stock_items' => 0,
            'total_revenue' => 0,
            'average_order_value' => 0,
        ];
    }

    /**
     * Get order statistics with trends (optimized for large datasets)
     */
    public function getOrderStats(Carbon $startDate, Carbon $endDate, int $limit = 1000): array
    {
        // Orders by status
        $ordersByStatus = DB::select("
            SELECT 
                status,
                COUNT(*) as count,
                SUM(total) as total_amount
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY status
        ", [$startDate, $endDate]);

        // Daily order trends for the last 7 days
        $dailyTrends = DB::select("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as orders_count,
                SUM(total) as daily_revenue
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY DATE(created_at)
            ORDER BY date DESC
            LIMIT 7
        ", [Carbon::now()->subDays(7), $endDate]);

        // Payment method distribution
        $paymentMethods = DB::select("
            SELECT 
                payment_method,
                COUNT(*) as count,
                SUM(total) as total_amount
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY payment_method
        ", [$startDate, $endDate]);

        return [
            'by_status' => $ordersByStatus,
            'daily_trends' => array_reverse($dailyTrends), // Reverse to show oldest first
            'payment_methods' => $paymentMethods,
        ];
    }

    /**
     * Get revenue statistics
     */
    public function getRevenueStats(Carbon $startDate, Carbon $endDate): array
    {
        // Revenue summary
        $revenueSummary = DB::select("
            SELECT 
                SUM(total) as total_revenue,
                SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) as completed_revenue,
                SUM(CASE WHEN status = 'pending' THEN total ELSE 0 END) as pending_revenue,
                AVG(total) as average_order_value,
                COUNT(*) as total_orders
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
        ", [$startDate, $endDate]);

        // Monthly revenue comparison
        $monthlyComparison = DB::select("
            SELECT 
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(total) as revenue,
                COUNT(*) as orders
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year DESC, month DESC
            LIMIT 6
        ", [Carbon::now()->subMonths(6), $endDate]);

        return [
            'summary' => $revenueSummary[0] ?? (object)[
                'total_revenue' => 0,
                'completed_revenue' => 0,
                'pending_revenue' => 0,
                'average_order_value' => 0,
                'total_orders' => 0,
            ],
            'monthly_comparison' => $monthlyComparison,
        ];
    }

    /**
     * Get product statistics (optimized for large datasets)
     */
    public function getProductStats(int $limit = 1000): array
    {
        // Product overview
        $productOverview = DB::select("
            SELECT 
                COUNT(*) as total_products,
                COUNT(CASE WHEN active = 1 THEN 1 END) as active_products,
                COUNT(CASE WHEN active = 0 THEN 1 END) as inactive_products,
                SUM(total_quantity) as total_stock
            FROM products
        ");

        // Top selling products (optimized for large datasets)
        $topSellingProducts = DB::select("
            SELECT 
                p.id,
                pt.name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.price) as total_revenue
            FROM products p
            JOIN product_translations pt ON p.id = pt.product_id AND pt.locale = 'en'
            JOIN order_items oi ON p.id = oi.product_id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'delivered'
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY p.id, pt.name
            HAVING total_sold > 0
            ORDER BY total_sold DESC
            LIMIT ?
        ", [$limit > 50 ? 50 : $limit]);

        // Low stock products (optimized for large datasets)
        $lowStockProducts = DB::select("
            SELECT 
                pv.id,
                pv.sku,
                pt.name as product_name,
                pv.stock,
                pv.minimum_stock,
                pv.price,
                CASE 
                    WHEN pv.stock = 0 THEN 999999
                    ELSE pv.minimum_stock - pv.stock
                END as urgency_score
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            JOIN product_translations pt ON p.id = pt.product_id AND pt.locale = 'en'
            WHERE pv.stock <= pv.minimum_stock
            AND p.active = 1
            ORDER BY urgency_score DESC, pv.stock ASC
            LIMIT ?
        ", [$limit > 20 ? 20 : $limit]);

        return [
            'overview' => $productOverview[0] ?? (object)[
                'total_products' => 0,
                'active_products' => 0,
                'inactive_products' => 0,
                'total_stock' => 0,
            ],
            'top_selling' => $topSellingProducts,
            'low_stock' => $lowStockProducts,
        ];
    }

    /**
     * Get user statistics (optimized for large datasets)
     */
    public function getUserStats(Carbon $startDate, Carbon $endDate, int $limit = 1000): array
    {
        // User overview with comprehensive metrics
        $userOverview = DB::select("
            SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 END) as new_users,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users,
                COUNT(CASE WHEN email_verified_at IS NULL THEN 1 END) as unverified_users,
                0 as active_users,
                0 as inactive_users,
                COUNT(CASE WHEN created_at >= ? AND created_at <= ? AND email_verified_at IS NOT NULL THEN 1 END) as new_verified_users
            FROM users
        ", [
            $startDate, $endDate, // new_users
            $startDate, $endDate, // new_verified_users
        ]);

        // User registration trends
        $registrationTrends = DB::select("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as registrations,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_registrations
            FROM users 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY DATE(created_at)
            ORDER BY date DESC
            LIMIT 7
        ", [Carbon::now()->subDays(7), $endDate]);

        // Top customers by order value (optimized for large datasets)
        $topCustomers = DB::select("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.phone,
                COUNT(o.id) as total_orders,
                SUM(o.total) as total_spent,
                AVG(o.total) as average_order_value,
                MAX(o.created_at) as last_order_date,
                MIN(o.created_at) as first_order_date
            FROM users u
            JOIN orders o ON u.id = o.user_id
            WHERE o.status = 'delivered'
            AND o.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY u.id, u.name, u.email, u.phone
            HAVING total_spent > 0
            ORDER BY total_spent DESC
            LIMIT ?
        ", [$limit > 20 ? 20 : $limit]);

        // User activity metrics
        $userActivity = DB::select("
            SELECT 
                0 as users_logged_in_today,
                0 as users_logged_in_week,
                0 as users_logged_in_month,
                0 as users_never_logged_in
            FROM users
            LIMIT 1
        ");

        return [
            'overview' => $userOverview[0] ?? (object)[
                'total_users' => 0,
                'new_users' => 0,
                'verified_users' => 0,
                'unverified_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'new_verified_users' => 0,
            ],
            'registration_trends' => array_reverse($registrationTrends),
            'top_customers' => $topCustomers,
            'activity' => $userActivity[0] ?? (object)[
                'users_logged_in_today' => 0,
                'users_logged_in_week' => 0,
                'users_logged_in_month' => 0,
                'users_never_logged_in' => 0,
            ],
        ];
    }

    /**
     * Get low stock statistics (optimized for large datasets)
     */
    public function getLowStockStats(int $limit = 1000): object
    {
        $lowStockStats = DB::select("
            SELECT 
                COUNT(*) as total_low_stock,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock > 0 AND stock <= minimum_stock THEN 1 ELSE 0 END) as low_stock,
                SUM(stock * price) as potential_loss_value
            FROM product_variants 
            WHERE stock <= minimum_stock
        ");

        return $lowStockStats[0] ?? (object)[
            'total_low_stock' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0,
            'potential_loss_value' => 0,
        ];
    }

    /**
     * Get recent activity (optimized for large datasets)
     */
    public function getRecentActivity(Carbon $startDate, Carbon $endDate, int $limit = 1000): array
    {
        // Recent orders (optimized for large datasets)
        $recentOrders = DB::select("
            SELECT 
                o.id,
                o.order_number,
                o.status,
                o.total,
                u.name as customer_name,
                o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.created_at >= ? AND o.created_at <= ?
            ORDER BY o.created_at DESC
            LIMIT ?
        ", [$startDate, $endDate, $limit > 10 ? 10 : $limit]);

        // Recent user registrations (optimized for large datasets)
        $recentUsers = DB::select("
            SELECT 
                id,
                name,
                email,
                created_at
            FROM users
            WHERE created_at >= ? AND created_at <= ?
            ORDER BY created_at DESC
            LIMIT ?
        ", [$startDate, $endDate, $limit > 5 ? 5 : $limit]);

        return [
            'recent_orders' => $recentOrders,
            'recent_users' => $recentUsers,
        ];
    }

    /**
     * Get sales analytics with different time periods
     */
    public function getSalesAnalytics(string $period = 'month'): array
    {
        $startDate = $this->getStartDateForPeriod($period);
        $endDate = Carbon::now();

        return [
            'period' => $period,
            'sales_data' => $this->getSalesData($startDate, $endDate, $period),
            'comparison' => $this->getPeriodComparison($startDate, $endDate, $period),
        ];
    }

    /**
     * Get start date based on period
     */
    private function getStartDateForPeriod(string $period): Carbon
    {
        switch ($period) {
            case 'day':
                return Carbon::now()->subDay();
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subMonth();
        }
    }

    /**
     * Get sales data for the specified period
     */
    private function getSalesData(Carbon $startDate, Carbon $endDate, string $period): array
    {
        $groupBy = $this->getGroupByClause($period);
        
        return DB::select("
            SELECT 
                {$groupBy} as period_label,
                COUNT(*) as orders_count,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY {$groupBy}
            ORDER BY period_label ASC
        ", [$startDate, $endDate]);
    }

    /**
     * Get group by clause based on period
     */
    private function getGroupByClause(string $period): string
    {
        switch ($period) {
            case 'day':
                return 'DATE(created_at)';
            case 'week':
                return 'YEARWEEK(created_at)';
            case 'month':
                return 'DATE_FORMAT(created_at, "%Y-%m")';
            case 'year':
                return 'YEAR(created_at)';
            default:
                return 'DATE_FORMAT(created_at, "%Y-%m")';
        }
    }

    /**
     * Get period comparison data
     */
    private function getPeriodComparison(Carbon $startDate, Carbon $endDate, string $period): array
    {
        $previousStart = $this->getPreviousPeriodStart($startDate, $period);
        $previousEnd = $startDate;

        $current = DB::select("
            SELECT 
                COUNT(*) as orders_count,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
        ", [$startDate, $endDate]);

        $previous = DB::select("
            SELECT 
                COUNT(*) as orders_count,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
        ", [$previousStart, $previousEnd]);

        $currentData = $current[0] ?? (object)['orders_count' => 0, 'total_revenue' => 0, 'average_order_value' => 0];
        $previousData = $previous[0] ?? (object)['orders_count' => 0, 'total_revenue' => 0, 'average_order_value' => 0];

        return [
            'current' => $currentData,
            'previous' => $previousData,
            'growth' => [
                'orders_growth' => $this->calculateGrowth($currentData->orders_count, $previousData->orders_count),
                'revenue_growth' => $this->calculateGrowth($currentData->total_revenue, $previousData->total_revenue),
                'aov_growth' => $this->calculateGrowth($currentData->average_order_value, $previousData->average_order_value),
            ],
        ];
    }

    /**
     * Get previous period start date
     */
    private function getPreviousPeriodStart(Carbon $startDate, string $period): Carbon
    {
        switch ($period) {
            case 'day':
                return $startDate->copy()->subDay();
            case 'week':
                return $startDate->copy()->subWeek();
            case 'month':
                return $startDate->copy()->subMonth();
            case 'year':
                return $startDate->copy()->subYear();
            default:
                return $startDate->copy()->subMonth();
        }
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }



    /**
     * Get profit analytics
     */
    public function getProfitAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        // Revenue vs Costs analysis
        $profitAnalysis = DB::select("
            SELECT 
                SUM(total) as total_revenue,
                SUM(subtotal) as gross_revenue,
                SUM(discount) as total_discounts,
                SUM(shipping_price) as total_shipping_revenue,
                SUM(tax) as total_tax_collected,
                SUM(vat) as total_vat_collected,
                COUNT(*) as total_orders,
                AVG(total) as average_order_value,
                AVG(subtotal) as average_gross_value
            FROM orders 
            WHERE created_at >= ? AND created_at <= ? AND (status = 'delivered' or status = 'completed')
        ", [$startDate, $endDate]);

        // Profit margins by order status
        $profitByStatus = DB::select("
            SELECT 
                status,
                COUNT(*) as order_count,
                SUM(total) as total_revenue,
                SUM(subtotal) as gross_revenue,
                SUM(discount) as total_discounts,
                AVG(total) as average_order_value,
                ROUND((SUM(total) / SUM(subtotal)) * 100, 2) as profit_margin_percentage
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY status
            ORDER BY total_revenue DESC
        ", [$startDate, $endDate]);

        // Monthly profit trends
        $monthlyProfitTrends = DB::select("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as orders_count,
                SUM(total) as total_revenue,
                SUM(subtotal) as gross_revenue,
                SUM(discount) as total_discounts,
                AVG(total) as average_order_value,
                ROUND((SUM(total) / SUM(subtotal)) * 100, 2) as profit_margin_percentage
            FROM orders 
            WHERE created_at >= ? AND created_at <= ? AND status = 'delivered'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 6
        ", [Carbon::now()->subMonths(6), $endDate]);

        return [
            'summary' => $profitAnalysis[0] ?? (object)[
                'total_revenue' => 0,
                'gross_revenue' => 0,
                'total_discounts' => 0,
                'total_shipping_revenue' => 0,
                'total_tax_collected' => 0,
                'total_vat_collected' => 0,
                'total_orders' => 0,
                'average_order_value' => 0,
                'average_gross_value' => 0,
            ],
            'by_status' => $profitByStatus,
            'monthly_trends' => $monthlyProfitTrends,
        ];
    }

    /**
     * Get orders statistics only (focused endpoint)
     */
    public function getOrdersStatistics(int $period = 30, int $limit = 1000): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            return $this->getOrderStats($startDate, $endDate, $limit);
        } catch (\Exception $e) {
            Log::error('Orders statistics error: ' . $e->getMessage());
            return [
                'by_status' => [],
                'daily_trends' => [],
                'payment_methods' => [],
                'error' => 'Orders statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get profit analytics only (focused endpoint)
     */
    public function getProfitStatistics(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            return $this->getProfitAnalytics($startDate, $endDate);
        } catch (\Exception $e) {
            Log::error('Profit statistics error: ' . $e->getMessage());
            return [
                'summary' => (object)[],
                'by_status' => [],
                'monthly_trends' => [],
                'error' => 'Profit statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get users statistics only (focused endpoint)
     */
    public function getUsersStatistics(int $period = 30, int $limit = 1000): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            return $this->getUserStats($startDate, $endDate, $limit);
        } catch (\Exception $e) {
            Log::error('Users statistics error: ' . $e->getMessage());
            return [
                'overview' => (object)[],
                'registration_trends' => [],
                'top_customers' => [],
                'activity' => (object)[],
                'error' => 'Users statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get products statistics only (focused endpoint)
     */
    public function getProductsStatistics(int $limit = 1000): array
    {
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            return $this->getProductStats($limit);
        } catch (\Exception $e) {
            Log::error('Products statistics error: ' . $e->getMessage());
            return [
                'overview' => (object)[],
                'top_selling' => [],
                'low_stock' => [],
                'error' => 'Products statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get revenue statistics only (focused endpoint)
     */
    public function getRevenueStatistics(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            return $this->getRevenueStats($startDate, $endDate);
        } catch (\Exception $e) {
            Log::error('Revenue statistics error: ' . $e->getMessage());
            return [
                'summary' => (object)[],
                'monthly_comparison' => [],
                'error' => 'Revenue statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get overview statistics only (focused endpoint)
     */
    public function getOverviewStatistics(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '256M');
            set_time_limit(30);
            
            return [
                'overview' => $this->getOverviewStats($startDate, $endDate),
                'low_stock' => $this->getLowStockStats(100),
                'recent_activity' => $this->getRecentActivity($startDate, $endDate, 50),
            ];
        } catch (\Exception $e) {
            Log::error('Overview statistics error: ' . $e->getMessage());
            return [
                'overview' => $this->getMinimalOverviewStats($startDate, $endDate),
                'low_stock' => (object)[],
                'recent_activity' => ['recent_orders' => [], 'recent_users' => []],
                'error' => 'Overview statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get orders statistics LITE (ultra-fast, essential data only)
     */
    public function getOrdersStatisticsLite(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '128M');
            set_time_limit(15);
            
            // Ultra-fast order status count only
            $orderStats = DB::select("
                SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(total) as total_amount
                FROM orders 
                WHERE created_at >= ? AND created_at <= ?
                GROUP BY status
                ORDER BY count DESC
            ", [$startDate, $endDate]);
            
            return [
                'by_status' => $orderStats,
                'total_orders' => array_sum(array_column($orderStats, 'count')),
                'total_revenue' => array_sum(array_column($orderStats, 'total_amount'))
            ];
        } catch (\Exception $e) {
            Log::error('Orders lite statistics error: ' . $e->getMessage());
            return [
                'by_status' => [],
                'total_orders' => 0,
                'total_revenue' => 0,
                'error' => 'Orders lite statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get profit statistics LITE (ultra-fast, essential data only)
     */
    public function getProfitStatisticsLite(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '128M');
            set_time_limit(15);
            
            // Ultra-fast profit summary only
            $profitSummary = DB::select("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total) as total_revenue,
                    SUM(subtotal) as gross_revenue,
                    SUM(discount) as total_discounts,
                    AVG(total) as average_order_value
                FROM orders 
                WHERE created_at >= ? AND created_at <= ? AND (status = 'delivered' OR status = 'completed')
            ", [$startDate, $endDate]);
            
            $summary = $profitSummary[0] ?? (object)[
                'total_orders' => 0,
                'total_revenue' => 0,
                'gross_revenue' => 0,
                'total_discounts' => 0,
                'average_order_value' => 0
            ];
            
            return [
                'summary' => $summary,
                'profit_margin' => $summary->total_revenue > 0 ? 
                    round((($summary->total_revenue - $summary->total_discounts) / $summary->total_revenue) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Profit lite statistics error: ' . $e->getMessage());
            return [
                'summary' => (object)[],
                'profit_margin' => 0,
                'error' => 'Profit lite statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get users statistics LITE (ultra-fast, essential data only)
     */
    public function getUsersStatisticsLite(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '128M');
            set_time_limit(15);
            
            // Ultra-fast user counts only
            $userStats = DB::select("
                SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 END) as new_users,
                    COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users,
                    COUNT(CASE WHEN email_verified_at IS NULL THEN 1 END) as unverified_users
                FROM users
            ", [$startDate, $endDate]);
            
            return [
                'overview' => $userStats[0] ?? (object)[
                    'total_users' => 0,
                    'new_users' => 0,
                    'verified_users' => 0,
                    'unverified_users' => 0
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Users lite statistics error: ' . $e->getMessage());
            return [
                'overview' => (object)[],
                'error' => 'Users lite statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get products statistics LITE (ultra-fast, essential data only)
     */
    public function getProductsStatisticsLite(): array
    {
        try {
            ini_set('memory_limit', '128M');
            set_time_limit(15);
            
            // Ultra-fast product counts only
            $productStats = DB::select("
                SELECT 
                    COUNT(*) as total_products,
                    COUNT(CASE WHEN active = 1 THEN 1 END) as active_products,
                    COUNT(CASE WHEN active = 0 THEN 1 END) as inactive_products
                FROM products
            ");
            
            // Get total stock from product_variants
            $totalStock = DB::select("
                SELECT SUM(stock) as total_stock
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE p.active = 1
            ");
            
            // Ultra-fast low stock count only
            $lowStockCount = DB::select("
                SELECT COUNT(*) as low_stock_count
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.stock <= pv.minimum_stock AND p.active = 1
            ");
            
            $productOverview = $productStats[0] ?? (object)[
                'total_products' => 0,
                'active_products' => 0,
                'inactive_products' => 0
            ];
            
            // Add total stock from the separate query
            $productOverview->total_stock = $totalStock[0]->total_stock ?? 0;
            
            return [
                'overview' => $productOverview,
                'low_stock_count' => $lowStockCount[0]->low_stock_count ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('Products lite statistics error: ' . $e->getMessage());
            return [
                'overview' => (object)[],
                'low_stock_count' => 0,
                'error' => 'Products lite statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get revenue statistics LITE (ultra-fast, essential data only)
     */
    public function getRevenueStatisticsLite(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '128M');
            set_time_limit(15);
            
            // Ultra-fast revenue summary only
            $revenueStats = DB::select("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total) as total_revenue,
                    AVG(total) as average_order_value,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                    SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) as completed_revenue
                FROM orders 
                WHERE created_at >= ? AND created_at <= ?
            ", [$startDate, $endDate]);
            
            return [
                'summary' => $revenueStats[0] ?? (object)[
                    'total_orders' => 0,
                    'total_revenue' => 0,
                    'average_order_value' => 0,
                    'completed_orders' => 0,
                    'completed_revenue' => 0
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Revenue lite statistics error: ' . $e->getMessage());
            return [
                'summary' => (object)[],
                'error' => 'Revenue lite statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get overview statistics LITE (ultra-fast, essential data only)
     */
    public function getOverviewStatisticsLite(int $period = 30): array
    {
        $startDate = Carbon::now()->subDays($period);
        $endDate = Carbon::now();
        
        try {
            ini_set('memory_limit', '128M');
            set_time_limit(15);
            
            // Ultra-fast overview counts only
            $overviewStats = DB::select("
                SELECT 
                    (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ?) as total_orders,
                    (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'delivered') as completed_orders,
                    (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'pending') as pending_orders,
                    (SELECT COUNT(*) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'cancelled') as cancelled_orders,
                    (SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at <= ?) as new_users,
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT SUM(total) FROM orders WHERE created_at >= ? AND created_at <= ? AND status = 'delivered') as total_revenue
            ", [
                $startDate, $endDate, // total_orders
                $startDate, $endDate, // completed_orders
                $startDate, $endDate, // pending_orders
                $startDate, $endDate, // cancelled_orders
                $startDate, $endDate, // new_users
                $startDate, $endDate, // total_revenue
            ]);
            
            return [
                'overview' => $overviewStats[0] ?? (object)[
                    'total_orders' => 0,
                    'completed_orders' => 0,
                    'pending_orders' => 0,
                    'cancelled_orders' => 0,
                    'new_users' => 0,
                    'total_users' => 0,
                    'total_revenue' => 0
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Overview lite statistics error: ' . $e->getMessage());
            return [
                'overview' => (object)[],
                'error' => 'Overview lite statistics temporarily unavailable'
            ];
        }
    }

    /**
     * Get growth metrics
     */
    public function getGrowthMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $previousStart = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $previousEnd = $startDate;

        // Current period metrics
        $currentMetrics = DB::select("
            SELECT 
                COUNT(*) as total_orders,
                COUNT(DISTINCT user_id) as unique_customers,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) as completed_revenue
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
        ", [$startDate, $endDate]);

        // Previous period metrics
        $previousMetrics = DB::select("
            SELECT 
                COUNT(*) as total_orders,
                COUNT(DISTINCT user_id) as unique_customers,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders,
                SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) as completed_revenue
            FROM orders 
            WHERE created_at >= ? AND created_at <= ?
        ", [$previousStart, $previousEnd]);

        // User growth
        $userGrowth = DB::select("
            SELECT 
                COUNT(*) as current_period_users,
                (SELECT COUNT(*) FROM users WHERE created_at >= ? AND created_at <= ?) as previous_period_users
            FROM users 
            WHERE created_at >= ? AND created_at <= ?
        ", [$previousStart, $previousEnd, $startDate, $endDate]);

        $current = $currentMetrics[0] ?? (object)[
            'total_orders' => 0, 'unique_customers' => 0, 'total_revenue' => 0,
            'average_order_value' => 0, 'completed_orders' => 0, 'completed_revenue' => 0
        ];

        $previous = $previousMetrics[0] ?? (object)[
            'total_orders' => 0, 'unique_customers' => 0, 'total_revenue' => 0,
            'average_order_value' => 0, 'completed_orders' => 0, 'completed_revenue' => 0
        ];

        $userGrowthData = $userGrowth[0] ?? (object)[
            'current_period_users' => 0, 'previous_period_users' => 0
        ];

        return [
            'current_period' => $current,
            'previous_period' => $previous,
            'growth_percentages' => [
                'orders_growth' => $this->calculateGrowth($current->total_orders, $previous->total_orders),
                'customers_growth' => $this->calculateGrowth($current->unique_customers, $previous->unique_customers),
                'revenue_growth' => $this->calculateGrowth($current->total_revenue, $previous->total_revenue),
                'aov_growth' => $this->calculateGrowth($current->average_order_value, $previous->average_order_value),
                'completed_orders_growth' => $this->calculateGrowth($current->completed_orders, $previous->completed_orders),
                'completed_revenue_growth' => $this->calculateGrowth($current->completed_revenue, $previous->completed_revenue),
                'user_growth' => $this->calculateGrowth($userGrowthData->current_period_users, $userGrowthData->previous_period_users),
            ],
            'period_comparison' => [
                'current_period_days' => $startDate->diffInDays($endDate),
                'previous_period_days' => $previousStart->diffInDays($previousEnd),
                'current_period_start' => $startDate->toDateString(),
                'current_period_end' => $endDate->toDateString(),
                'previous_period_start' => $previousStart->toDateString(),
                'previous_period_end' => $previousEnd->toDateString(),
            ],
        ];
    }
}
