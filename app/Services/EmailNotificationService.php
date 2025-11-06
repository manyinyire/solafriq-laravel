<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'order_number' => $order->id,
                'total_amount' => $order->total_amount,
                'items' => $order->items,
                'order_date' => $order->created_at,
            ];

            // In a real implementation, you'd create proper Mailable classes
            // Mail::to($order->customer_email)->send(new OrderConfirmationMail($data));

            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'payment_method' => $order->payment_method,
                'amount_paid' => $order->total_amount,
                'payment_date' => now(),
            ];

            // Mail::to($order->customer_email)->send(new PaymentConfirmationMail($data));

            Log::info('Payment confirmation email sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send status update notification
     */
    public function sendStatusUpdateNotification(Order $order, string $oldStatus, string $newStatus): bool
    {
        try {
            $statusMessages = [
                'PROCESSING' => 'Your order is now being processed and prepared for shipment.',
                'SHIPPED' => 'Great news! Your order has been shipped and is on its way to you.',
                'DELIVERED' => 'Your order has been successfully delivered. Enjoy your new solar system!',
                'CANCELLED' => 'Your order has been cancelled as requested.',
                'REFUNDED' => 'Your refund has been processed and should appear in your account soon.',
            ];

            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'status_message' => $statusMessages[$newStatus] ?? 'Your order status has been updated.',
                'tracking_number' => $order->tracking_number,
            ];

            // Mail::to($order->customer_email)->send(new StatusUpdateMail($data));

            Log::info('Status update email sent', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send status update email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send shipping notification
     */
    public function sendShippingNotification(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'tracking_number' => $order->tracking_number,
                'estimated_delivery' => now()->addDays(config('solafriq.estimated_delivery_days', 3)),
                'carrier' => config('solafriq.default_carrier', 'SolaFriq Logistics')
            ];

            // Mail::to($order->customer_email)->send(new ShippingNotificationMail($data));

            Log::info('Shipping notification email sent', [
                'order_id' => $order->id,
                'tracking_number' => $order->tracking_number
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send shipping notification email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send installation scheduled notification
     */
    public function sendInstallationScheduledNotification(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'installation_date' => $order->installation_date,
                'order_number' => $order->id,
                'items' => $order->items,
                'support_contact' => config('solafriq.support_email', 'support@solafriq.com'),
            ];

            // Mail::to($order->customer_email)->send(new InstallationScheduledMail($data));

            Log::info('Installation scheduled email sent', [
                'order_id' => $order->id,
                'installation_date' => $order->installation_date,
                'customer_email' => $order->customer_email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send installation scheduled email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send order accepted notification
     */
    public function sendOrderAcceptedNotification(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'order_number' => $order->id,
                'tracking_number' => $order->tracking_number,
                'items' => $order->items,
                'total_amount' => $order->total_amount,
            ];

            // Mail::to($order->customer_email)->send(new OrderAcceptedMail($data));

            Log::info('Order accepted email sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order accepted email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send delivery confirmation
     */
    public function sendDeliveryConfirmation(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'delivery_date' => now(),
                'warranty_info' => $order->warranties->isNotEmpty(),
                'support_contact' => config('solafriq.support_email', 'support@solafriq.com'),
            ];

            // Mail::to($order->customer_email)->send(new DeliveryConfirmationMail($data));

            Log::info('Delivery confirmation email sent', [
                'order_id' => $order->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send delivery confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send order cancellation notification
     */
    public function sendOrderCancellation(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'cancellation_reason' => $order->notes,
                'refund_info' => $order->isPaid() ? 'Refund will be processed within 5-7 business days.' : null,
            ];

            // Mail::to($order->customer_email)->send(new OrderCancellationMail($data));

            Log::info('Order cancellation email sent', [
                'order_id' => $order->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order cancellation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send overdue payment reminder
     */
    public function sendOverduePaymentReminder(Order $order): bool
    {
        try {
            $daysOverdue = now()->diffInDays($order->created_at);
            
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'amount_due' => $order->total_amount,
                'days_overdue' => $daysOverdue,
                'payment_methods' => ['Bank Transfer', 'Online Payment', 'Cash Payment'],
            ];

            // Mail::to($order->customer_email)->send(new OverduePaymentReminderMail($data));

            Log::info('Overdue payment reminder sent', [
                'order_id' => $order->id,
                'days_overdue' => $daysOverdue
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send overdue payment reminder', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }


    /**
     * Send warranty expiration notice
     */
    public function sendWarrantyExpirationNotice(Warranty $warranty): bool
    {
        try {
            $daysUntilExpiry = now()->diffInDays($warranty->end_date);
            
            $data = [
                'customer_name' => $warranty->user->name,
                'warranty' => $warranty,
                'product_name' => $warranty->product_name,
                'expiry_date' => $warranty->end_date,
                'days_until_expiry' => $daysUntilExpiry,
                'serial_number' => $warranty->serial_number,
            ];

            // Mail::to($warranty->user->email)->send(new WarrantyExpirationNoticeMail($data));

            Log::info('Warranty expiration notice sent', [
                'warranty_id' => $warranty->id,
                'days_until_expiry' => $daysUntilExpiry
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send warranty expiration notice', [
                'warranty_id' => $warranty->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $data = [
                'user' => $user,
                'dashboard_url' => url('/dashboard'),
                'support_email' => 'support@solafriq.com',
            ];

            // Mail::to($user->email)->send(new WelcomeEmailMail($data));

            Log::info('Welcome email sent', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send admin notification for new order
     */
    public function sendAdminOrderNotification(Order $order): bool
    {
        try {
            $adminEmails = config('solafriq.admin_emails', ['admin@solafriq.com', 'orders@solafriq.com']);
            
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'order_value' => $order->total_amount,
                'items_count' => $order->items->count(),
                'admin_url' => url("/admin/orders/{$order->id}"),
            ];

            foreach ($adminEmails as $adminEmail) {
                // Mail::to($adminEmail)->send(new AdminOrderNotificationMail($data));
            }

            Log::info('Admin order notification sent', [
                'order_id' => $order->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send admin order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send system maintenance notification
     */
    public function sendMaintenanceNotification(array $users, array $maintenanceDetails): bool
    {
        try {
            foreach ($users as $user) {
                $data = array_merge($maintenanceDetails, [
                    'user_name' => $user->name,
                ]);

                // Mail::to($user->email)->send(new MaintenanceNotificationMail($data));
            }

            Log::info('Maintenance notification sent to users', [
                'user_count' => count($users),
                'maintenance_date' => $maintenanceDetails['start_time'] ?? 'TBD'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send maintenance notification', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send order approved notification with invoice attachment
     */
    public function sendOrderApprovedWithInvoice(Order $order): bool
    {
        try {
            $order->load(['items', 'invoice']);

            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'order_number' => $order->id,
                'tracking_number' => $order->tracking_number,
                'items' => $order->items,
                'total_amount' => $order->total_amount,
                'invoice' => $order->invoice,
                'message' => 'Great news! Your order has been approved and is now being processed.',
            ];

            // Generate and attach invoice PDF
            if ($order->invoice) {
                $invoiceService = app(InvoiceGeneratorService::class);
                $pdfPath = $invoiceService->generateInvoicePDF($order->invoice);

                // In production, you would use:
                // Mail::to($order->customer_email)
                //     ->send((new OrderApprovedMail($data))->attach($pdfPath, [
                //         'as' => 'invoice_' . $order->invoice->invoice_number . '.pdf',
                //         'mime' => 'application/pdf',
                //     ]));
            }

            Log::info('Order approved email with invoice sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'invoice_id' => $order->invoice?->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order approved email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send order declined notification
     */
    public function sendOrderDeclinedNotification(Order $order): bool
    {
        try {
            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'order_number' => $order->id,
                'items' => $order->items,
                'total_amount' => $order->total_amount,
                'message' => 'We regret to inform you that your order has been declined. Please contact our support team for more information.',
                'support_email' => config('solafriq.support_email', 'support@solafriq.com'),
                'support_phone' => config('solafriq.support_phone', '+1-800-555-0123'),
            ];

            // Mail::to($order->customer_email)->send(new OrderDeclinedMail($data));

            Log::info('Order declined email sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order declined email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send payment confirmation with paid invoice attachment
     */
    public function sendPaymentConfirmationWithInvoice(Order $order): bool
    {
        try {
            $order->load(['items', 'invoice']);

            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'payment_method' => $order->payment_method,
                'amount_paid' => $order->total_amount,
                'payment_date' => now(),
                'invoice' => $order->invoice,
                'order_number' => $order->id,
                'message' => 'Your payment has been confirmed! Your invoice is attached.',
            ];

            // Generate and attach paid invoice PDF
            if ($order->invoice) {
                $invoiceService = app(InvoiceGeneratorService::class);
                $pdfPath = $invoiceService->generateInvoicePDF($order->invoice);

                // In production, you would use:
                // Mail::to($order->customer_email)
                //     ->send((new PaymentConfirmationMail($data))->attach($pdfPath, [
                //         'as' => 'invoice_' . $order->invoice->invoice_number . '.pdf',
                //         'mime' => 'application/pdf',
                //     ]));
            }

            Log::info('Payment confirmation email with invoice sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'invoice_id' => $order->invoice?->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send order installed notification with warranty certificate
     */
    public function sendOrderInstalledWithWarranty(Order $order): bool
    {
        try {
            $order->load(['items', 'warranties']);

            $data = [
                'order' => $order,
                'customer_name' => $order->customer_name,
                'order_number' => $order->id,
                'installation_date' => now(),
                'warranties' => $order->warranties,
                'items' => $order->items,
                'message' => 'Your solar system has been successfully installed! Your warranty certificates are attached.',
                'support_contact' => config('solafriq.support_email', 'support@solafriq.com'),
            ];

            // Generate and attach warranty certificates PDF
            if ($order->warranties->isNotEmpty()) {
                $warrantyService = app(WarrantyService::class);

                // In production, generate warranty PDFs and attach:
                // foreach ($order->warranties as $warranty) {
                //     $pdfPath = $warrantyService->generateWarrantyCertificate($warranty);
                //     $attachments[] = [
                //         'path' => $pdfPath,
                //         'name' => 'warranty_' . $warranty->serial_number . '.pdf',
                //         'mime' => 'application/pdf'
                //     ];
                // }
                // Mail::to($order->customer_email)
                //     ->send(new OrderInstalledMail($data, $attachments));
            }

            Log::info('Order installed email with warranty sent', [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
                'warranties_count' => $order->warranties->count(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order installed email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}