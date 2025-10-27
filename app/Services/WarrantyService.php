<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Warranty;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WarrantyService
{
    /**
     * Automatically create warranties when installation is completed
     */
    public function createWarrantiesForOrder(Order $order): array
    {
        if (!$order->isPaid()) {
            throw new \Exception('Cannot create warranties for unpaid orders');
        }

        $warranties = [];

        return DB::transaction(function () use ($order, &$warranties) {
            // Load order items
            $order->load('items');

            foreach ($order->items as $item) {
                // Only create warranties for solar systems and products with warranty
                if ($this->isWarrantyEligible($item)) {
                    $warranty = $this->createWarranty($order, $item);
                    $warranties[] = $warranty;
                }
            }

            Log::info('Warranties created for order', [
                'order_id' => $order->id,
                'warranty_count' => count($warranties),
            ]);

            return $warranties;
        });
    }

    /**
     * Create a single warranty for an order item
     */
    private function createWarranty(Order $order, $item): Warranty
    {
        // Determine warranty period based on product type
        $warrantyPeriodMonths = $this->getWarrantyPeriod($item);
        
        // Generate unique serial number
        $serialNumber = $this->generateSerialNumber($order, $item);

        // Calculate start and end dates
        $startDate = now();
        $endDate = now()->addMonths($warrantyPeriodMonths);

        return Warranty::create([
            'product_name' => $item->name,
            'serial_number' => $serialNumber,
            'warranty_period_months' => $warrantyPeriodMonths,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'ACTIVE',
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }

    /**
     * Check if an item is eligible for warranty
     */
    private function isWarrantyEligible($item): bool
    {
        // Solar systems and products are warranty eligible
        return in_array($item->type, ['solar_system', 'product', 'custom_system']);
    }

    /**
     * Get warranty period based on product type
     */
    private function getWarrantyPeriod($item): int
    {
        // Default warranty periods
        return match($item->type) {
            'solar_system' => 120, // 10 years for complete systems
            'custom_system' => 120, // 10 years for custom systems
            'product' => 24, // 2 years for individual products
            default => 12, // 1 year default
        };
    }

    /**
     * Generate unique serial number for warranty
     */
    private function generateSerialNumber(Order $order, $item): string
    {
        $prefix = 'WR';
        $year = now()->year;
        $orderId = str_pad($order->id, 6, '0', STR_PAD_LEFT);
        $itemId = str_pad($item->id, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid()), 0, 4));

        return "{$prefix}{$year}{$orderId}{$itemId}{$random}";
    }

    /**
     * Generate warranty certificate PDF
     */
    public function generateWarrantyCertificate(Warranty $warranty)
    {
        // Load relationships
        $warranty->load(['order.items', 'order.user', 'user']);

        // Get company settings
        $companySettings = [
            'company_name' => CompanySetting::get('company_name', 'SolaFriq'),
            'company_address' => CompanySetting::get('company_address', '123 Solar Street, Energy City'),
            'company_phone' => CompanySetting::get('company_phone', '+1 (555) 123-4567'),
            'company_email' => CompanySetting::get('company_email', 'info@solafriq.com'),
            'company_logo' => CompanySetting::get('company_logo', '/images/solafriq-logo.svg'),
        ];

        // Prepare data for PDF
        $data = [
            'warranty' => $warranty,
            'order' => $warranty->order,
            'customer' => $warranty->user ?? $warranty->order->user,
            'companySettings' => $companySettings,
            'generatedDate' => now()->format('F d, Y'),
        ];

        // Generate PDF
        $pdf = PDF::loadView('warranties.certificate', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Download warranty certificate
     */
    public function downloadWarrantyCertificate(Warranty $warranty)
    {
        $pdf = $this->generateWarrantyCertificate($warranty);
        $filename = 'Warranty-Certificate-' . $warranty->serial_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get eligible orders for manual warranty creation
     * (Orders that are installed and paid but don't have warranties yet)
     */
    public function getEligibleOrdersForWarranty()
    {
        return Order::with(['items', 'user', 'warranties'])
            ->where('status', 'INSTALLED')
            ->where('payment_status', 'PAID')
            ->whereDoesntHave('warranties')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Manually create warranty for an order (Admin function)
     */
    public function manuallyCreateWarranty(Order $order, array $warrantyData = []): Warranty
    {
        if (!$order->isPaid()) {
            throw new \Exception('Cannot create warranty for unpaid order');
        }

        if ($order->status !== 'INSTALLED') {
            throw new \Exception('Order must be installed before creating warranty');
        }

        return DB::transaction(function () use ($order, $warrantyData) {
            // Use provided data or defaults
            $warrantyPeriodMonths = $warrantyData['warranty_period_months'] ?? 120;
            $productName = $warrantyData['product_name'] ?? $this->getOrderProductName($order);
            $serialNumber = $warrantyData['serial_number'] ?? $this->generateSerialNumber($order, (object)['id' => 1]);

            $startDate = isset($warrantyData['start_date']) 
                ? Carbon::parse($warrantyData['start_date']) 
                : now();
            
            $endDate = $startDate->copy()->addMonths($warrantyPeriodMonths);

            $warranty = Warranty::create([
                'product_name' => $productName,
                'serial_number' => $serialNumber,
                'warranty_period_months' => $warrantyPeriodMonths,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'ACTIVE',
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);

            Log::info('Warranty manually created', [
                'warranty_id' => $warranty->id,
                'order_id' => $order->id,
                'serial_number' => $serialNumber,
            ]);

            return $warranty;
        });
    }

    /**
     * Get product name from order items
     */
    private function getOrderProductName(Order $order): string
    {
        $order->load('items');
        
        if ($order->items->isEmpty()) {
            return 'Warranty for Order #' . $order->id;
        }
        
        $firstItem = $order->items->first();
        if (!$firstItem || !$firstItem->name) {
            return 'Warranty for Order #' . $order->id;
        }
        
        if ($order->items->count() === 1) {
            return $firstItem->name;
        }

        return $firstItem->name . ' + ' . ($order->items->count() - 1) . ' more items';
    }

    /**
     * Activate warranties for an order (when installation is completed)
     */
    public function activateWarrantiesForOrder(Order $order): void
    {
        $order->warranties()->update([
            'status' => 'ACTIVE',
            'start_date' => now(),
        ]);

        Log::info('Warranties activated for order', [
            'order_id' => $order->id,
            'warranty_count' => $order->warranties()->count(),
        ]);
    }

    /**
     * Check and update expired warranties
     */
    public function updateExpiredWarranties(): int
    {
        $expiredCount = Warranty::where('status', 'ACTIVE')
            ->where('end_date', '<', now())
            ->update(['status' => 'EXPIRED']);

        if ($expiredCount > 0) {
            Log::info('Expired warranties updated', [
                'count' => $expiredCount,
            ]);
        }

        return $expiredCount;
    }

    /**
     * Get warranty statistics
     */
    public function getWarrantyStatistics(): array
    {
        return [
            'total' => Warranty::count(),
            'active' => Warranty::where('status', 'ACTIVE')->count(),
            'expired' => Warranty::where('status', 'EXPIRED')->count(),
            'claimed' => Warranty::where('status', 'CLAIMED')->count(),
            'expiring_soon' => Warranty::where('status', 'ACTIVE')
                ->whereBetween('end_date', [now(), now()->addDays(30)])
                ->count(),
        ];
    }
}
