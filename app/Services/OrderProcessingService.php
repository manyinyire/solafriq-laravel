<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\User;
use App\Jobs\ProcessOrderJob;
use App\Jobs\SendOrderNotificationJob;
use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProcessingService
{
    public function __construct(
        private InvoiceGeneratorService $invoiceService,
        private EmailNotificationService $emailService
    ) {}

    /**
     * Create a new order with items and invoice
     */
    public function createOrder(array $orderData, ?User $user = null): Order
    {
        return DB::transaction(function () use ($orderData, $user) {
            // Calculate total amount
            $totalAmount = collect($orderData['items'])->sum(fn($item) => 
                $item['price'] * $item['quantity']
            );

            // Create the order
            $order = Order::create([
                'total_amount' => $totalAmount,
                'status' => 'PENDING',
                'payment_status' => 'PENDING',
                'payment_method' => $orderData['payment_method'] ?? null,
                'notes' => $orderData['notes'] ?? null,
                'user_id' => $user?->id,
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'customer_phone' => $orderData['customer_phone'] ?? null,
                'customer_address' => $orderData['customer_address'] ?? null,
            ]);

            // Create order items
            foreach ($orderData['items'] as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'image_url' => $itemData['image_url'] ?? null,
                    'type' => $itemData['type'],
                ]);
            }

            // Generate invoice
            $this->invoiceService->generateInvoice($order);

            // Dispatch background jobs
            ProcessOrderJob::dispatch($order);
            SendOrderNotificationJob::dispatch($order);

            // Fire event
            event(new OrderCreated($order));

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'total_amount' => $order->total_amount
            ]);

            return $order->load(['items', 'invoice']);
        });
    }

    /**
     * Update an existing order
     */
    public function updateOrder(Order $order, array $updateData): Order
    {
        return DB::transaction(function () use ($order, $updateData) {
            $oldStatus = $order->status;
            $oldPaymentStatus = $order->payment_status;

            $order->update($updateData);

            // Handle status changes
            if (isset($updateData['status']) && $updateData['status'] !== $oldStatus) {
                $this->handleStatusChange($order, $oldStatus, $updateData['status']);
            }

            if (isset($updateData['payment_status']) && $updateData['payment_status'] !== $oldPaymentStatus) {
                $this->handlePaymentStatusChange($order, $oldPaymentStatus, $updateData['payment_status']);
            }

            // Fire event
            event(new OrderUpdated($order, $updateData));

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'changes' => $updateData
            ]);

            return $order->load(['items', 'invoice']);
        });
    }

    /**
     * Process payment for an order
     */
    public function processPayment(Order $order, array $paymentData): array
    {
        if ($order->isPaid()) {
            throw new \Exception('Order is already paid');
        }

        return DB::transaction(function () use ($order, $paymentData) {
            // Here you would integrate with payment gateways like Paystack, Flutterwave, etc.
            $paymentResult = $this->processPaymentGateway($order, $paymentData);

            if ($paymentResult['success']) {
                $order->update([
                    'payment_status' => 'PAID',
                    'payment_method' => $paymentData['payment_method'],
                ]);

                // Update invoice
                if ($order->invoice) {
                    $order->invoice->markAsPaid();
                }

                // Send payment confirmation
                $this->emailService->sendPaymentConfirmation($order);

                Log::info('Payment processed successfully', [
                    'order_id' => $order->id,
                    'payment_method' => $paymentData['payment_method'],
                    'amount' => $order->total_amount
                ]);
            }

            return $paymentResult;
        });
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if (!in_array($order->status, ['PENDING', 'ACCEPTED'])) {
            throw new \Exception('Order cannot be cancelled in current status');
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => 'RETURNED',
                'notes' => ($order->notes ? $order->notes . "\n\n" : '') . 
                          "Cancelled: " . ($reason ?? 'Customer request')
            ]);

            // Handle refunds if payment was processed
            if ($order->isPaid()) {
                $this->initiateRefund($order);
            }

            // Send cancellation notification
            $this->emailService->sendOrderCancellation($order);

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'reason' => $reason
            ]);

            return $order;
        });
    }

    /**
     * Handle order status changes
     */
    private function handleStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        match ($newStatus) {
            'ACCEPTED' => $this->handleAcceptedStatus($order),
            'SCHEDULED' => $this->handleScheduledStatus($order),
            'INSTALLED' => $this->handleInstalledStatus($order),
            'RETURNED' => $this->handleReturnedStatus($order),
            default => null,
        };

        // Send status update notification
        $this->emailService->sendStatusUpdateNotification($order, $oldStatus, $newStatus);
    }

    /**
     * Handle payment status changes
     */
    private function handlePaymentStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($newStatus === 'PAID' && $oldStatus !== 'PAID') {
            $this->handlePaymentReceived($order);
        } elseif ($newStatus === 'OVERDUE') {
            $this->handleOverduePayment($order);
        }
    }

    /**
     * Handle accepted status
     */
    private function handleAcceptedStatus(Order $order): void
    {
        // Generate tracking number for accepted orders
        $order->update([
            'tracking_number' => $this->generateTrackingNumber($order)
        ]);

        // Create warranties for applicable items
        $this->createWarranties($order);
    }

    /**
     * Handle scheduled status
     */
    private function handleScheduledStatus(Order $order): void
    {
        // Send scheduling notification
        $this->emailService->sendShippingNotification($order);
    }

    /**
     * Handle installed status
     */
    private function handleInstalledStatus(Order $order): void
    {
        // Activate warranties
        $this->activateWarranties($order);

        // Send installation confirmation
        $this->emailService->sendDeliveryConfirmation($order);
    }

    /**
     * Handle payment received
     */
    private function handlePaymentReceived(Order $order): void
    {
        // Update invoice
        if ($order->invoice) {
            $order->invoice->markAsPaid();
        }

        // Send payment confirmation
        $this->emailService->sendPaymentConfirmation($order);
    }

    /**
     * Process payment through gateway (stub - implement actual payment logic)
     */
    private function processPaymentGateway(Order $order, array $paymentData): array
    {
        // This is a stub - implement actual payment gateway integration
        // For Paystack, Flutterwave, etc.
        
        return [
            'success' => true,
            'transaction_id' => 'TXN_' . uniqid(),
            'reference' => 'REF_' . uniqid(),
            'amount' => $order->total_amount,
            'currency' => 'USD',
            'payment_method' => $paymentData['payment_method'],
        ];
    }

    /**
     * Generate tracking number
     */
    private function generateTrackingNumber(Order $order): string
    {
        return 'SF' . date('Ymd') . str_pad($order->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create warranties for order items
     */
    private function createWarranties(Order $order): void
    {
        // Implementation would create warranty records
        // for applicable solar system items
    }

    /**
     * Activate warranties
     */
    private function activateWarranties(Order $order): void
    {
        $order->warranties()->update([
            'status' => 'ACTIVE',
            'start_date' => now(),
        ]);
    }

    /**
     * Initiate refund process
     */
    private function initiateRefund(Order $order): void
    {
        // Implementation would handle refund through payment gateway
        Log::info('Refund initiated', ['order_id' => $order->id]);
    }

    /**
     * Handle returned status
     */
    private function handleReturnedStatus(Order $order): void
    {
        // Cancel any pending warranties
        $order->warranties()->update(['status' => 'EXPIRED']);
    }

    /**
     * Handle overdue payments
     */
    private function handleOverduePayment(Order $order): void
    {
        // Send overdue payment reminder
        $this->emailService->sendOverduePaymentReminder($order);
    }

    /**
     * Update the status of an order.
     */
    public function updateOrderStatus(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus) {
            $oldStatus = $order->status;

            $order->update(['status' => $newStatus]);

            $this->handleStatusChange($order, $oldStatus, $newStatus);

            event(new OrderUpdated($order, ['status' => $newStatus]));

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return $order;
        });
    }

    /**
     * Confirm payment for an order.
     */
    public function confirmPayment(Order $order, array $paymentData): Order
    {
        return DB::transaction(function () use ($order, $paymentData) {
            $order->update([
                'payment_status' => 'PAID',
                'payment_method' => $paymentData['payment_method'],
                'notes' => ($order->notes ? $order->notes . "\n\n" : '') .
                          "Payment confirmed by admin. Method: " . $paymentData['payment_method'] .
                          (isset($paymentData['transaction_reference']) ? ", Ref: " . $paymentData['transaction_reference'] : ''),
            ]);

            // Update invoice
            if ($order->invoice) {
                $order->invoice->markAsPaid();
            }

            // Send payment confirmation
            $this->emailService->sendPaymentConfirmation($order);

            Log::info('Payment confirmed by admin', [
                'order_id' => $order->id,
                'payment_method' => $paymentData['payment_method'],
                'transaction_reference' => $paymentData['transaction_reference'] ?? null,
            ]);

            return $order;
        });
    }

    public function scheduleInstallation(Order $order, string $installationDate): Order
    {
        return DB::transaction(function () use ($order, $installationDate) {
            $order->update([
                'status' => 'SCHEDULED',
                'installation_date' => $installationDate,
            ]);

            $this->handleStatusChange($order, 'PROCESSING', 'SCHEDULED');

            Log::info('Installation scheduled', [
                'order_id' => $order->id,
                'installation_date' => $installationDate,
            ]);

            return $order;
        });
    }

    /**
     * Process refund for an order.
     */
    public function processRefund(Order $order, array $refundData): Order
    {
        if (!$order->isPaid()) {
            throw new \Exception('Cannot refund an unpaid order');
        }

        if ($order->status === 'RETURNED') {
            throw new \Exception('Order is already returned/refunded');
        }

        return DB::transaction(function () use ($order, $refundData) {
            $refundAmount = $refundData['refund_amount'];
            $refundReason = $refundData['refund_reason'];

            // Update order status and payment status
            $order->update([
                'status' => 'RETURNED',
                'payment_status' => 'REFUNDED',
                'notes' => ($order->notes ? $order->notes . "\n\n" : '') .
                          "Refund processed by admin. Amount: $" . number_format($refundAmount, 2) .
                          ", Reason: " . $refundReason,
            ]);

            // Process actual refund through payment gateway
            // This would integrate with Paystack, Flutterwave, etc.
            $this->processRefundGateway($order, $refundAmount, $refundReason);

            // Update invoice
            if ($order->invoice) {
                $order->invoice->update(['status' => 'REFUNDED']);
            }

            // Cancel warranties
            $order->warranties()->update(['status' => 'EXPIRED']);

            Log::info('Refund processed', [
                'order_id' => $order->id,
                'refund_amount' => $refundAmount,
                'refund_reason' => $refundReason,
            ]);

            return $order;
        });
    }

    /**
     * Process refund through payment gateway.
     */
    private function processRefundGateway(Order $order, float $refundAmount, string $reason): array
    {
        // This is a stub - implement actual refund gateway integration
        // For Paystack, Flutterwave, etc.

        Log::info('Refund gateway processing', [
            'order_id' => $order->id,
            'amount' => $refundAmount,
            'reason' => $reason,
        ]);

        return [
            'success' => true,
            'refund_id' => 'RF_' . uniqid(),
            'amount' => $refundAmount,
            'reference' => 'REFUND_' . uniqid(),
        ];
    }
}