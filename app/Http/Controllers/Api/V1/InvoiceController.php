<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    protected $invoiceGenerator;

    public function __construct(InvoiceGeneratorService $invoiceGenerator)
    {
        $this->invoiceGenerator = $invoiceGenerator;
    }

    /**
     * Display a listing of the user's invoices
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::whereHas('order', function ($orderQuery) {
                $orderQuery->where('user_id', Auth::id());
            })
            ->with(['order', 'order.items'])
            ->latest();

        // Search filter if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('id', 'like', '%' . $search . '%')
                                 ->orWhere('customer_name', 'like', '%' . $search . '%')
                                 ->orWhere('customer_email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => InvoiceResource::collection($invoices->items()),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ]);
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        // Check if user owns this invoice through the order
        if ($invoice->order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $invoice->load(['order', 'order.items']);

        return response()->json([
            'success' => true,
            'data' => new InvoiceResource($invoice)
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function download(Invoice $invoice)
    {
        // Check if user owns this invoice
        if ($invoice->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        try {
            $pdf = $this->invoiceGenerator->generatePDF($invoice);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment for an invoice
     */
    public function pay(Request $request, Invoice $invoice): JsonResponse
    {
        // Check if user owns this invoice
        if ($invoice->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'PAID') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        // Check if invoice is overdue and handle accordingly
        if ($invoice->status === 'OVERDUE') {
            // You might want to add late fees here
        }

        $request->validate([
            'payment_method' => 'required|string|in:card,bank_transfer,mobile_money',
            'payment_reference' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            // Check if the amount matches the invoice total or remaining balance
            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;

            if ($request->amount > $remainingAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds remaining balance'
                ], 400);
            }

            // Process the payment (integrate with payment gateway here)
            $payment = $invoice->payments()->create([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference ?? 'REF-' . time(),
                'status' => 'COMPLETED', // In real implementation, this would be 'PENDING' until confirmed
                'paid_at' => now(),
            ]);

            // Update invoice paid amount
            $invoice->increment('paid_amount', $request->amount);

            // Update invoice status if fully paid
            if ($invoice->fresh()->paid_amount >= $invoice->total_amount) {
                $invoice->update([
                    'status' => 'PAID',
                    'paid_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'amount_paid' => $request->amount,
                    'remaining_balance' => $invoice->total_amount - $invoice->fresh()->paid_amount,
                    'invoice_status' => $invoice->fresh()->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice statistics for the authenticated user
     */
    public function stats(): JsonResponse
    {
        $invoiceQuery = Invoice::whereHas('order', function ($orderQuery) {
            $orderQuery->where('user_id', Auth::id());
        });

        $stats = [
            'total_invoices' => $invoiceQuery->count(),
            'paid_invoices' => $invoiceQuery->where('payment_status', 'PAID')->count(),
            'pending_invoices' => $invoiceQuery->where('payment_status', 'PENDING')->count(),
            'overdue_invoices' => $invoiceQuery->where('payment_status', 'OVERDUE')->count(),
            'outstanding_amount' => $invoiceQuery->whereIn('payment_status', ['PENDING', 'OVERDUE'])->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}