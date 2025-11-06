<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\InvoiceGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    )
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice - Order ' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-mail',
            with: [
                'order' => $this->order,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Generate and attach invoice PDF
        if ($this->order->invoice) {
            try {
                $invoiceService = app(InvoiceGeneratorService::class);
                $pdfPath = $invoiceService->generateInvoicePDF($this->order->invoice);

                $attachments[] = Attachment::fromPath($pdfPath)
                    ->as('invoice_' . $this->order->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf');
            } catch (\Exception $e) {
                \Log::error('Failed to attach invoice PDF to email', [
                    'order_id' => $this->order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $attachments;
    }
}
