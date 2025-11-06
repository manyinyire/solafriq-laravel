<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use App\Models\Warranty;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->where('status', 'PENDING')->count(),
            'accepted_orders' => $user->orders()->where('status', 'ACCEPTED')->count(),
            'completed_orders' => $user->orders()->where('status', 'INSTALLED')->count(),
            'total_spent' => $user->orders()->whereNotIn('status', ['RETURNED'])->sum('total_amount'),
            'active_warranties' => $user->warranties()->where('status', 'ACTIVE')->count(),
        ];

        return $this->successResponse($stats);
    }

    public function recentOrders(): JsonResponse
    {
        $orders = Auth::user()->orders()
            ->with([
                'items.product',
                'items.solarSystem',
                'invoice',
                'warranties'
            ])
            ->latest()
            ->take(5)
            ->get();

        return $this->successResponse($orders);
    }


    public function warrantySummary(): JsonResponse
    {
        $warranties = Auth::user()->warranties()
            ->with(['order'])
            ->where('status', 'ACTIVE')
            ->get();

        $summary = [
            'total_warranties' => $warranties->count(),
            'expiring_soon' => $warranties->filter(function ($warranty) {
                return now()->diffInDays($warranty->expires_at) <= 30;
            })->count(),
            'recent_claims' => Auth::user()->warrantyClaims()
                ->with('warranty')
                ->latest()
                ->take(3)
                ->get(),
        ];

        return $this->successResponse($summary);
    }

    public function adminOverview(): JsonResponse
    {
        $overview = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'PENDING')->count(),
            'accepted_orders' => Order::where('status', 'ACCEPTED')->count(),
            'scheduled_orders' => Order::where('status', 'SCHEDULED')->count(),
            'installed_orders' => Order::where('status', 'INSTALLED')->count(),
            'total_revenue' => Order::where('payment_status', 'PAID')->sum('total_amount'),
            'monthly_revenue' => Order::whereMonth('created_at', now()->month)
                ->where('payment_status', 'PAID')
                ->sum('total_amount'),
            'weekly_revenue' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('payment_status', 'PAID')
                ->sum('total_amount'),
            'daily_revenue' => Order::whereDate('created_at', today())
                ->where('payment_status', 'PAID')
                ->sum('total_amount'),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'pending_payments' => Order::where('payment_status', 'PENDING')->sum('total_amount'),
            'conversion_rate' => $this->getConversionRate(),
        ];

        return response()->json($overview);
    }

    public function salesAnalytics(): JsonResponse
    {
        $analytics = [
            'monthly_sales' => Order::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as orders_count')
            )
                ->whereYear('created_at', now()->year)
                ->whereNotIn('status', ['RETURNED'])
                ->where('payment_status', 'PAID')
                ->groupBy('month')
                ->get(),

            'top_products' => DB::table('order_items')
                ->select('name', 'type', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_revenue'))
                ->groupBy('name', 'type')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get(),

            'revenue_by_month' => Order::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders_count')
            )
                ->where('payment_status', 'PAID')
                ->whereYear('created_at', now()->year)
                ->groupBy('year', 'month')
                ->orderBy('month')
                ->get(),

            'order_status_distribution' => Order::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
        ];

        return response()->json($analytics);
    }

    public function systemMetrics(): JsonResponse
    {
        $metrics = [
            'database_size' => $this->getDatabaseSize(),
            'total_files_uploaded' => $this->getFilesCount(),
            'system_uptime' => $this->getSystemUptime(),
            'recent_activities' => $this->getRecentSystemLogs(),
        ];

        return response()->json($metrics);
    }

    private function getDatabaseSize(): string
    {
        $size = DB::select("
            SELECT
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = ?
        ", [config('database.connections.mysql.database')]);

        return ($size[0]->size_mb ?? 0) . ' MB';
    }

    private function getFilesCount(): int
    {
        $publicPath = public_path('storage');
        if (!is_dir($publicPath)) {
            return 0;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($publicPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        return iterator_count($files);
    }

    private function getSystemUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'N/A on Windows';
        }

        $uptime = shell_exec('uptime -p');
        return trim($uptime ?? 'Unknown');
    }

    private function getRecentSystemLogs(): array
    {
        return DB::table('system_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getConversionRate(): float
    {
        $totalOrders = Order::count();
        $paidOrders = Order::where('payment_status', 'PAID')->count();

        if ($totalOrders === 0) {
            return 0.0;
        }

        return round(($paidOrders / $totalOrders) * 100, 2);
    }
}