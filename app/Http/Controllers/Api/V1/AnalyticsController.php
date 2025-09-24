<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\InstallmentPlan;
use App\Models\WarrantyClaim;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function salesReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'compare_previous' => 'boolean',
        ]);

        $startDate = $validated['start_date'] ?? now()->subMonths(6);
        $endDate = $validated['end_date'] ?? now();
        $period = $validated['period'];

        $currentPeriod = $this->getSalesData($startDate, $endDate, $period);
        $previousPeriod = null;

        if ($validated['compare_previous'] ?? false) {
            $periodLength = Carbon::parse($endDate)->diffInDays($startDate);
            $prevStart = Carbon::parse($startDate)->subDays($periodLength);
            $prevEnd = Carbon::parse($startDate)->subDay();
            $previousPeriod = $this->getSalesData($prevStart, $prevEnd, $period);
        }

        return response()->json([
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'summary' => $this->generateSalesSummary($currentPeriod, $previousPeriod),
        ]);
    }

    public function customerInsights(): JsonResponse
    {
        $insights = [
            'total_customers' => User::where('role', 'CLIENT')->count(),
            'new_customers_this_month' => User::where('role', 'CLIENT')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'customer_retention_rate' => $this->calculateRetentionRate(),
            'customer_lifetime_value' => $this->calculateCustomerLifetimeValue(),
            'customer_segmentation' => $this->getCustomerSegmentation(),
            'top_customers' => $this->getTopCustomers(),
            'customer_acquisition_by_month' => $this->getCustomerAcquisitionData(),
            'customer_satisfaction' => $this->getCustomerSatisfactionMetrics(),
        ];

        return response()->json($insights);
    }

    public function systemPerformance(): JsonResponse
    {
        $performance = [
            'order_processing' => $this->getOrderProcessingMetrics(),
            'payment_success_rate' => $this->getPaymentSuccessRate(),
            'warranty_claim_metrics' => $this->getWarrantyClaimMetrics(),
            'system_reliability' => $this->getSystemReliabilityMetrics(),
            'response_times' => $this->getResponseTimeMetrics(),
            'error_rates' => $this->getErrorRates(),
        ];

        return response()->json($performance);
    }

    private function getSalesData($startDate, $endDate, $period): array
    {
        $query = Order::whereBetween('created_at', [$startDate, $endDate])
                     ->where('status', '!=', 'CANCELLED');

        $groupBy = match ($period) {
            'daily' => "DATE(created_at)",
            'weekly' => "YEARWEEK(created_at)",
            'monthly' => "YEAR(created_at), MONTH(created_at)",
            'yearly' => "YEAR(created_at)",
        };

        $results = $query->select(
            DB::raw("{$groupBy} as period"),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('AVG(total_amount) as average_order_value')
        )->groupBy(DB::raw($groupBy))
         ->orderBy(DB::raw($groupBy))
         ->get();

        return [
            'data' => $results,
            'total_revenue' => $results->sum('total_revenue'),
            'total_orders' => $results->sum('order_count'),
            'average_order_value' => $results->avg('average_order_value'),
        ];
    }

    private function generateSalesSummary($current, $previous): array
    {
        if (!$previous) {
            return [
                'revenue_growth' => null,
                'order_growth' => null,
                'aov_growth' => null,
            ];
        }

        return [
            'revenue_growth' => $this->calculateGrowthPercentage(
                $current['total_revenue'],
                $previous['total_revenue']
            ),
            'order_growth' => $this->calculateGrowthPercentage(
                $current['total_orders'],
                $previous['total_orders']
            ),
            'aov_growth' => $this->calculateGrowthPercentage(
                $current['average_order_value'],
                $previous['average_order_value']
            ),
        ];
    }

    private function calculateGrowthPercentage($current, $previous): float
    {
        if ($previous == 0) return 0;
        return (($current - $previous) / $previous) * 100;
    }

    private function calculateRetentionRate(): float
    {
        $totalCustomers = User::where('role', 'CLIENT')->count();
        $repeatCustomers = User::where('role', 'CLIENT')
            ->whereHas('orders', function ($query) {
                $query->havingRaw('COUNT(*) > 1');
            })->count();

        return $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;
    }

    private function calculateCustomerLifetimeValue(): float
    {
        return User::where('role', 'CLIENT')
            ->withSum(['orders' => function ($query) {
                $query->where('status', '!=', 'CANCELLED');
            }], 'total_amount')
            ->get()
            ->avg('orders_sum_total_amount') ?? 0;
    }

    private function getCustomerSegmentation(): array
    {
        return [
            'new_customers' => User::where('role', 'CLIENT')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'regular_customers' => User::where('role', 'CLIENT')
                ->whereHas('orders', function ($query) {
                    $query->havingRaw('COUNT(*) BETWEEN 2 AND 5');
                })->count(),
            'vip_customers' => User::where('role', 'CLIENT')
                ->whereHas('orders', function ($query) {
                    $query->havingRaw('COUNT(*) > 5');
                })->count(),
        ];
    }

    private function getTopCustomers(): array
    {
        return User::where('role', 'CLIENT')
            ->withSum(['orders' => function ($query) {
                $query->where('status', '!=', 'CANCELLED');
            }], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->limit(10)
            ->get(['id', 'name', 'email', 'orders_sum_total_amount'])
            ->toArray();
    }

    private function getCustomerAcquisitionData(): array
    {
        return User::where('role', 'CLIENT')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    private function getCustomerSatisfactionMetrics(): array
    {
        return [
            'warranty_claim_rate' => $this->getWarrantyClaimRate(),
            'order_completion_rate' => $this->getOrderCompletionRate(),
            'average_response_time' => $this->getAverageResponseTime(),
        ];
    }

    private function getOrderProcessingMetrics(): array
    {
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'DELIVERED')->count();
        $pendingOrders = Order::where('status', 'PENDING')->count();

        return [
            'total_orders' => $totalOrders,
            'completion_rate' => $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0,
            'pending_orders' => $pendingOrders,
            'average_processing_time' => $this->getAverageProcessingTime(),
        ];
    }

    private function getPaymentSuccessRate(): float
    {
        $totalPayments = DB::table('installment_payments')->count();
        $successfulPayments = DB::table('installment_payments')
            ->where('status', 'PAID')
            ->count();

        return $totalPayments > 0 ? ($successfulPayments / $totalPayments) * 100 : 0;
    }

    private function getWarrantyClaimMetrics(): array
    {
        $totalClaims = WarrantyClaim::count();
        $resolvedClaims = WarrantyClaim::where('status', 'RESOLVED')->count();

        return [
            'total_claims' => $totalClaims,
            'resolution_rate' => $totalClaims > 0 ? ($resolvedClaims / $totalClaims) * 100 : 0,
            'average_resolution_time' => $this->getAverageClaimResolutionTime(),
            'claims_by_type' => WarrantyClaim::select('issue_type', DB::raw('COUNT(*) as count'))
                ->groupBy('issue_type')
                ->get()
                ->toArray(),
        ];
    }

    private function getSystemReliabilityMetrics(): array
    {
        return [
            'uptime_percentage' => 99.5, // This would come from monitoring service
            'error_count_24h' => $this->getErrorCount24h(),
            'performance_score' => 95.2, // This would come from performance monitoring
        ];
    }

    private function getResponseTimeMetrics(): array
    {
        // This would typically come from application performance monitoring
        return [
            'average_api_response_time' => 150, // milliseconds
            'average_page_load_time' => 2.3, // seconds
            'database_query_time' => 45, // milliseconds
        ];
    }

    private function getErrorRates(): array
    {
        return [
            'api_error_rate' => 0.2, // percentage
            'payment_failure_rate' => 2.1, // percentage
            'system_error_rate' => 0.05, // percentage
        ];
    }

    // Helper methods
    private function getWarrantyClaimRate(): float
    {
        $totalOrders = Order::where('status', 'DELIVERED')->count();
        $ordersWithClaims = Order::whereHas('warranties.claims')->count();

        return $totalOrders > 0 ? ($ordersWithClaims / $totalOrders) * 100 : 0;
    }

    private function getOrderCompletionRate(): float
    {
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'DELIVERED')->count();

        return $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
    }

    private function getAverageResponseTime(): float
    {
        // This would typically come from support ticket system
        return 24.5; // hours
    }

    private function getAverageProcessingTime(): float
    {
        return Order::where('status', 'DELIVERED')
            ->select(DB::raw('AVG(DATEDIFF(updated_at, created_at)) as avg_days'))
            ->first()
            ->avg_days ?? 0;
    }

    private function getAverageClaimResolutionTime(): float
    {
        return WarrantyClaim::where('status', 'RESOLVED')
            ->select(DB::raw('AVG(DATEDIFF(updated_at, created_at)) as avg_days'))
            ->first()
            ->avg_days ?? 0;
    }

    private function getErrorCount24h(): int
    {
        return DB::table('system_logs')
            ->where('level', 'error')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
    }
}