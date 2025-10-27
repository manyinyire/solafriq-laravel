<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SolarSystem;
use App\Models\Order;
use App\Models\Warranty;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard overview stats
     */
    public function index(): JsonResponse
    {
        try {
            $stats = $this->getDashboardStats();
            $recentActivity = $this->getRecentActivity();
            $monthlyRevenue = $this->getMonthlyRevenue();
            $systemStatusBreakdown = $this->getSystemStatusBreakdown();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_activity' => $recentActivity,
                    'monthly_revenue' => $monthlyRevenue,
                    'system_status' => $systemStatusBreakdown,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get main dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $totalUsers = User::count();

        // Sales for current month (using orders)
        $currentMonth = Carbon::now();
        $monthlySales = Order::whereYear('created_at', $currentMonth->year)
            ->whereMonth('created_at', $currentMonth->month)
            ->count();

        // Pending installations (orders that are confirmed but not yet delivered)
        $pendingInstallations = Order::where('status', 'PENDING')
            ->orWhere('status', 'CONFIRMED')
            ->count();

        // Monthly revenue (from invoices)
        $monthlyRevenue = Invoice::whereYear('created_at', $currentMonth->year)
            ->whereMonth('created_at', $currentMonth->month)
            ->where('payment_status', 'PAID')
            ->sum('total');

        // Total active systems (alternative to warranties)
        $activeSystems = SolarSystem::where('is_active', 1)->count();

        // Calculate growth percentages (compared to last month)
        $lastMonth = Carbon::now()->subMonth();

        $usersLastMonth = User::where('created_at', '<=', $lastMonth)->count();
        $salesLastMonth = Order::whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->count();
        $revenueLastMonth = Invoice::whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->where('payment_status', 'PAID')
            ->sum('total');

        $userGrowth = $usersLastMonth > 0 ? (($totalUsers - $usersLastMonth) / $usersLastMonth) * 100 : 0;
        $salesGrowth = $salesLastMonth > 0 ? (($monthlySales - $salesLastMonth) / $salesLastMonth) * 100 : 0;
        $revenueGrowth = $revenueLastMonth > 0 ? (($monthlyRevenue - $revenueLastMonth) / $revenueLastMonth) * 100 : 0;

        return [
            'total_users' => $totalUsers,
            'monthly_sales' => $monthlySales,
            'pending_installations' => $pendingInstallations,
            'monthly_revenue' => $monthlyRevenue,
            'active_systems' => $activeSystems,
            'growth' => [
                'users' => round($userGrowth, 1),
                'sales' => round($salesGrowth, 1),
                'revenue' => round($revenueGrowth, 1),
            ]
        ];
    }

    /**
     * Get recent activity - shows only recent orders
     */
    private function getRecentActivity(): array
    {
        $recentOrders = Order::with(['user', 'items'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'id' => $order->id,
                    'title' => 'Order #' . $order->id,
                    'description' => $order->customer_name . ' - $' . number_format($order->total_amount, 2),
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'customer_name' => $order->customer_name,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at,
                    'item_count' => $order->items->count(),
                ];
            });

        return $recentOrders->toArray();
    }

    /**
     * Get monthly revenue data for charts
     */
    private function getMonthlyRevenue(): array
    {
        $months = [];
        $revenue = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthRevenue = Invoice::where('payment_status', 'PAID')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total');

            $months[] = $date->format('M Y');
            $revenue[] = floatval($monthRevenue);
        }

        return [
            'labels' => $months,
            'data' => $revenue,
        ];
    }

    /**
     * Get order status breakdown instead of system status
     */
    private function getSystemStatusBreakdown(): array
    {
        // Get order status breakdown
        $orderStatuses = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $labels = $orderStatuses->pluck('status')->toArray();
        $data = $orderStatuses->pluck('count')->toArray();

        // Fallback to totals if no status data
        if (empty($labels)) {
            $totalOrders = Order::count();
            return [
                'labels' => ['Orders'],
                'data' => [$totalOrders],
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get users analytics
     */
    public function usersAnalytics(): JsonResponse
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('last_login', '>=', now()->subDays(30))->count();
            $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();

            $userGrowthData = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $count = User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
                $userGrowthData[] = [
                    'month' => $date->format('M Y'),
                    'users' => $count,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'new_users_this_month' => $newUsersThisMonth,
                    'growth_data' => $userGrowthData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get systems analytics
     */
    public function systemsAnalytics(): JsonResponse
    {
        try {
            $totalSystems = SolarSystem::count();
            $totalCapacity = SolarSystem::sum('capacity_kw');
            $averageCapacity = SolarSystem::avg('capacity_kw');

            $systemsByStatus = SolarSystem::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            $capacityByMonth = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $capacity = SolarSystem::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('capacity_kw');
                $capacityByMonth[] = [
                    'month' => $date->format('M Y'),
                    'capacity' => floatval($capacity),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_systems' => $totalSystems,
                    'total_capacity' => floatval($totalCapacity),
                    'average_capacity' => round($averageCapacity, 2),
                    'systems_by_status' => $systemsByStatus,
                    'capacity_by_month' => $capacityByMonth,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch systems analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get financial analytics
     */
    public function financialAnalytics(): JsonResponse
    {
        try {
            $totalRevenue = Invoice::where('payment_status', 'PAID')->sum('total');
            $pendingPayments = Invoice::where('payment_status', 'PENDING')->sum('total');
            $thisMonthRevenue = Invoice::where('payment_status', 'PAID')
                ->whereMonth('created_at', now()->month)
                ->sum('total');

            $revenueByMonth = [];
            $paymentsByStatus = Invoice::select('payment_status', DB::raw('sum(total) as total'))
                ->groupBy('payment_status')
                ->get();

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $revenue = Invoice::where('payment_status', 'PAID')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total');
                $revenueByMonth[] = [
                    'month' => $date->format('M Y'),
                    'revenue' => floatval($revenue),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => floatval($totalRevenue),
                    'pending_payments' => floatval($pendingPayments),
                    'this_month_revenue' => floatval($thisMonthRevenue),
                    'revenue_by_month' => $revenueByMonth,
                    'payments_by_status' => $paymentsByStatus,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch financial analytics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}