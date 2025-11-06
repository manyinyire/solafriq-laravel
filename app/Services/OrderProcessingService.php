<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Product;
use App\Jobs\ProcessOrderJob;
use App\Jobs\SendOrderNotificationJob;
use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\OrderNotCancellableException;
use App\Exceptions\InvalidRefundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProcessingService
{
    public function __construct(
        private InvoiceGeneratorService $invoiceService,
        private EmailNotificationService $emailService,
        private WarrantyService $warrantyService
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

            // Create order items and deduct stock
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
                
                // Deduct stock if product_id is provided
                if (isset($itemData['product_id'])) {
                    $this->deductStock($itemData['product_id'], $itemData['quantity']);
                }
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
            throw new OrderAlreadyPaidException();
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
            throw new OrderNotCancellableException();
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
            'PROCESSING' => $this->handleAcceptedStatus($order), // Approve order
            'ACCEPTED' => $this->handleAcceptedStatus($order),
            'CANCELLED' => $this->handleDeclinedStatus($order), // Decline order
            'SCHEDULED' => $this->emailService->sendShippingNotification($order),
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
            'tracking_number' => generateTrackingNumber($order->id)
        ]);

        // Generate or regenerate invoice
        if (!$order->invoice) {
            $this->invoiceService->generateInvoice($order);
        }

        // Create warranties for applicable items
        $this->createWarranties($order);

        // Send order accepted email to customer with invoice
        $this->emailService->sendOrderApprovedWithInvoice($order);
    }

    /**
     * Handle declined status
     */
    private function handleDeclinedStatus(Order $order): void
    {
        // Send order declined email to customer
        $this->emailService->sendOrderDeclinedNotification($order);
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
        // Create warranties if they don't exist
        if ($order->warranties->isEmpty()) {
            $this->createWarranties($order);
        }

        // Activate warranties
        $this->activateWarranties($order);

        // Send installation confirmation with warranty certificates
        $this->emailService->sendOrderInstalledWithWarranty($order);
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

        // Send payment confirmation with paid invoice
        $this->emailService->sendPaymentConfirmationWithInvoice($order);
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
     * Create warranties for order items
     */
    private function createWarranties(Order $order): void
    {
        try {
            $this->warrantyService->createWarrantiesForOrder($order);
        } catch (\Exception $e) {
            Log::warning('Failed to create warranties for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Activate warranties
     */
    private function activateWarranties(Order $order): void
    {
        try {
            $this->warrantyService->activateWarrantiesForOrder($order);
        } catch (\Exception $e) {
            Log::warning('Failed to activate warranties for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
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
            $updateData = [
                'payment_status' => 'PAID',
                'payment_method' => $paymentData['payment_method'],
            ];
            
            // Only add transaction reference if provided
            if (isset($paymentData['transaction_reference']) && !empty($paymentData['transaction_reference'])) {
                $updateData['notes'] = ($order->notes ? $order->notes . "\n\n" : '') .
                              "Payment Reference: " . $paymentData['transaction_reference'];
            }
            
            $order->update($updateData);

            // Update invoice
            if ($order->invoice) {
                $order->invoice->markAsPaid();
            }

            // Send payment confirmation with paid invoice
            $this->emailService->sendPaymentConfirmationWithInvoice($order);

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

            // Send installation scheduled email
            $this->emailService->sendInstallationScheduledNotification($order);

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
            throw new InvalidRefundException('Cannot refund an unpaid order');
        }

        if ($order->status === 'RETURNED') {
            throw new InvalidRefundException('Order is already returned/refunded');
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

    /**
     * Deduct stock quantity for a product
     */
    private function deductStock(int $productId, int $quantity): void
    {
        $product = Product::find($productId);
        
        if ($product) {
            $newStock = max(0, $product->stock_quantity - $quantity);
            $product->update(['stock_quantity' => $newStock]);
            
            Log::info('Stock deducted', [
                'product_id' => $productId,
                'product_name' => $product->name,
                'quantity_deducted' => $quantity,
                'remaining_stock' => $newStock,
            ]);
            
            // Log warning if stock is low
            if ($newStock <= 5 && $newStock > 0) {
                Log::warning('Low stock alert', [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'remaining_stock' => $newStock,
                ]);
            } elseif ($newStock === 0) {
                Log::warning('Out of stock', [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                ]);
            }
        }
    }
}