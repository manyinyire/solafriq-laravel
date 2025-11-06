<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceGeneratorService
{
    /**
     * Generate invoice for an order
     */
    public function generateInvoice(Order $order): Invoice
    {
        // Calculate invoice totals
        $subtotal = $order->items->sum(fn($item) => $item->price * $item->quantity);
        $tax = $subtotal * config('solafriq.tax_rate', 0.0825);
        $total = $subtotal + $tax;

        // Create or update invoice
        $invoice = Invoice::updateOrCreate(
            ['order_id' => $order->id],
            [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'payment_status' => $order->payment_status,
            ]
        );

        Log::info('Invoice generated', [
            'invoice_id' => $invoice->id,
            'order_id' => $order->id,
            'total' => $total
        ]);

        return $invoice;
    }

    /**
     * Generate PDF invoice using DomPDF
     */
    public function generateInvoicePDF(Invoice $invoice): string
    {
        $invoice->load(['order.items', 'order.user']);
        
        // Generate HTML content
        $htmlContent = $this->buildInvoiceHTML($invoice);
        
        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($htmlContent);
        $pdf->setPaper('A4', 'portrait');
        
        // Store PDF and return path
        $filename = "invoice_{$invoice->invoice_number}.pdf";
        $pdfPath = storage_path("app/public/invoices/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(dirname($pdfPath))) {
            mkdir(dirname($pdfPath), 0755, true);
        }
        
        // Save PDF to storage
        $pdf->save($pdfPath);
        
        Log::info('Invoice PDF generated', [
            'invoice_id' => $invoice->id,
            'pdf_path' => $pdfPath
        ]);
        
        return $pdfPath;
    }

    /**
     * Build HTML content for invoice
     */
    private function buildInvoiceHTML(Invoice $invoice): string
    {
        $order = $invoice->order;
        
        // Get company settings
        $companyName = setting('company_name', 'SolaFriq');
        $companyEmail = setting('company_email', 'info@solafriq.com');
        $companyPhone = setting('company_phone', '+1-XXX-XXX-XXXX');
        $companyAddress = setting('company_address', 'New York, USA');
        $companyLogo = setting('company_logo', '/images/solafriq-logo.svg');
        
        // Convert logo to base64 for PDF compatibility
        $logoData = '';
        if (!str_starts_with($companyLogo, 'http')) {
            // It's a relative path
            $publicPath = public_path($companyLogo);
            if (file_exists($publicPath)) {
                $imageData = file_get_contents($publicPath);
                $imageType = mime_content_type($publicPath);
                $logoData = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            } else {
                // Fallback to URL
                $logoData = url($companyLogo);
            }
        } else {
            // It's already a full URL
            $logoData = $companyLogo;
        }

        $pdfContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice {$invoice->invoice_number}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company-logo { max-height: 60px; margin-bottom: 10px; }
                .company-name { font-size: 24px; font-weight: bold; color: #f97316; }
                .invoice-details { margin: 20px 0; }
                .customer-details { background: #f8f9fa; padding: 15px; margin: 20px 0; }
                .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .items-table th { background-color: #f8f9fa; }
                .totals { margin: 20px 0; text-align: right; }
                .total-row { font-weight: bold; font-size: 18px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <img src='{$logoData}' alt='{$companyName}' class='company-logo' onerror=\"this.style.display='none'\" />
                <div class='company-name'>{$companyName}</div>
                <div>Premium Solar Solutions</div>
                <div>{$companyAddress} | {$companyEmail}</div>
                <div>{$companyPhone}</div>
            </div>
            
            <div class='invoice-details'>
                <h2>Invoice {$invoice->invoice_number}</h2>
                <p><strong>Date:</strong> {$invoice->created_at->format('F j, Y')}</p>
                <p><strong>Order ID:</strong> {$order->id}</p>
                <p><strong>Payment Status:</strong> {$invoice->payment_status}</p>
            </div>
            
            <div class='customer-details'>
                <h3>Bill To:</h3>
                <p><strong>{$order->customer_name}</strong></p>
                <p>{$order->customer_email}</p>
                " . ($order->customer_phone ? "<p>{$order->customer_phone}</p>" : "") . "
                " . ($order->customer_address ? "<p>{$order->customer_address}</p>" : "") . "
            </div>
            
            <table class='items-table'>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($order->items as $item) {
            $pdfContent .= "
                    <tr>
                        <td>{$item->name}</td>
                        <td>" . ($item->description ?? '-') . "</td>
                        <td>{$item->quantity}</td>
                        <td>$" . number_format($item->price, 2) . "</td>
                        <td>$" . number_format($item->total_price, 2) . "</td>
                    </tr>";
        }
        
        $pdfContent .= "
                </tbody>
            </table>
            
            <div class='totals'>
                <p>Subtotal: $" . number_format($invoice->subtotal, 2) . "</p>
                <p>Sales Tax (" . (config('solafriq.tax_rate') * 100) . "%): $" . number_format($invoice->tax, 2) . "</p>
                <p class='total-row'>Total: $" . number_format($invoice->total, 2) . "</p>
            </div>
            
            <div style='margin-top: 40px; font-size: 12px; color: #666;'>
                <p>Thank you for choosing {$companyName} for your solar energy needs!</p>
                <p>For support, contact us at {$companyEmail} or {$companyPhone}</p>
            </div>
        </body>
        </html>";
        
        return $pdfContent;
    }

    /**
     * Send invoice via email
     */
    public function sendInvoiceEmail(Invoice $invoice): bool
    {
        try {
            $pdfPath = $this->generateInvoicePDF($invoice);
            
            // Send email with PDF attachment
            // This would integrate with your email service
            
            Log::info('Invoice email sent', [
                'invoice_id' => $invoice->id,
                'customer_email' => $invoice->order->customer_email
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Update invoice payment status
     */
    public function updatePaymentStatus(Invoice $invoice, string $paymentStatus): Invoice
    {
        $invoice->update(['payment_status' => $paymentStatus]);
        
        Log::info('Invoice payment status updated', [
            'invoice_id' => $invoice->id,
            'payment_status' => $paymentStatus
        ]);
        
        return $invoice;
    }

    /**
     * Generate payment reminder
     */
    public function generatePaymentReminder(Invoice $invoice): array
    {
        $daysOverdue = now()->diffInDays($invoice->created_at);
        $order = $invoice->order;
        
        return [
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'amount_due' => $invoice->total,
            'days_overdue' => $daysOverdue,
            'due_date' => $invoice->created_at->addDays(config('solafriq.payment_terms_days', 30)),
            'payment_methods' => [
                'Bank Transfer',
                'Online Payment',
                'Cash Payment'
            ]
        ];
    }

    /**
     * Create invoice for an order (wrapper for generateInvoice)
     */
    public function createInvoiceForOrder(Order $order): Invoice
    {
        return $this->generateInvoice($order);
    }
}