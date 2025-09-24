<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Services\OrderProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function __construct(
        private OrderProcessingService $orderService
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items', 'user', 'invoice'])
            ->orderBy('created_at', 'desc');

        // Filter by user if authenticated
        if ($request->user() && !$request->user()->isAdmin()) {
            $query->forUser($request->user()->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->withStatus($request->input('status'));
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->withPaymentStatus($request->input('payment_status'));
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_email', 'like', '%' . $search . '%')
                  ->orWhere('tracking_number', 'like', '%' . $search . '%');
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.image_url' => 'nullable|url',
            'items.*.type' => 'required|in:solar_system,product,custom_package,custom_system',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $order = $this->orderService->createOrder($validated, $request->user());

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Order created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Ensure user can only see their own orders (unless admin)
        if (!$request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['items.solarSystem', 'user', 'invoice', 'warranties']);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update the specified order status (Admin only).
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'sometimes|in:PENDING,ACCEPTED,SCHEDULED,INSTALLED,RETURNED',
            'payment_status' => 'sometimes|in:PENDING,PAID,FAILED,OVERDUE',
            'payment_method' => 'nullable|string|max:50',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $order = $this->orderService->updateOrder($order, $validated);

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Order updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process payment for an order.
     */
    public function processPayment(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:credit_card,bank_transfer,cash',
            'payment_details' => 'nullable|array',
        ]);

        try {
            $result = $this->orderService->processPayment($order, $validated);

            return response()->json([
                'data' => $result,
                'message' => 'Payment processed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        // Ensure user can only cancel their own orders (unless admin)
        if (!$request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['PENDING', 'PROCESSING'])) {
            return response()->json([
                'message' => 'Order cannot be cancelled',
            ], 400);
        }

        try {
            $order = $this->orderService->cancelOrder($order, $request->input('reason'));

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Order cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function accept(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        try {
            $order = $this->orderService->updateOrderStatus($order, 'ACCEPTED');

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order),
                'message' => 'Order accepted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to accept order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function decline(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        try {
            $order = $this->orderService->updateOrderStatus($order, 'RETURNED');

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order),
                'message' => 'Order declined successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to decline order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmPayment(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'payment_method' => 'required|string|in:CASH,BANK_TRANSFER,CREDIT_CARD',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        try {
            $order = $this->orderService->confirmPayment($order, $validated);

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Payment confirmed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => 'required|in:PENDING,ACCEPTED,SCHEDULED,INSTALLED,RETURNED',
        ]);

        try {
            $order = $this->orderService->updateOrderStatus($order, $validated['status']);

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Order status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateTracking(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
        ]);

        try {
            $order->update(['tracking_number' => $validated['tracking_number']]);

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Tracking number updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update tracking number',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addNote(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        try {
            $notes = $order->notes ? json_decode($order->notes, true) : [];
            $notes[] = [
                'note' => $validated['note'],
                'added_by' => $request->user()->name,
                'added_at' => now()->toISOString(),
            ];

            $order->update(['notes' => json_encode($notes)]);

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Note added successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add note',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function refund(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0',
            'refund_reason' => 'required|string|max:500',
        ]);

        try {
            $order = $this->orderService->processRefund($order, $validated);

            return response()->json([
                'data' => new OrderResource($order),
                'message' => 'Refund processed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resendNotification(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'notification_type' => 'required|in:order_confirmation,payment_confirmation,shipping_notification,delivery_confirmation',
        ]);

        try {
            // Use the EmailNotificationService to resend notifications
            $emailService = app(\App\Services\EmailNotificationService::class);

            $success = match($validated['notification_type']) {
                'order_confirmation' => $emailService->sendOrderConfirmation($order),
                'payment_confirmation' => $emailService->sendPaymentConfirmation($order),
                'shipping_notification' => $emailService->sendShippingNotification($order),
                'delivery_confirmation' => $emailService->sendDeliveryConfirmation($order),
                default => false,
            };

            if ($success) {
                return response()->json([
                    'message' => 'Notification sent successfully',
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to send notification',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadInvoice(Request $request, Order $order)
    {
        try {
            // Check authorization
            if (!$request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
                abort(403, 'Unauthorized');
            }

            // Load order with relationships
            $order->load(['items', 'user', 'invoice']);

            // Check if order has an invoice
            if (!$order->invoice) {
                abort(404, 'Invoice not found for this order');
            }

            // Check if PDF class exists
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                return response()->json(['error' => 'PDF generation library not available'], 500);
            }

            // Get company settings (you can customize these or fetch from database)
            $companySettings = [
                'companyName' => 'SolarFriq',
                'companyAddress' => '123 Solar Street, Energy City, EC 12345',
                'companyPhone' => '+1 (555) 123-4567',
                'companyEmail' => 'info@solarfriq.com'
            ];

            // Prepare view data
            $viewData = [
                'order' => $order,
                'invoice' => $order->invoice,
                'companyName' => $companySettings['companyName'],
                'companyAddress' => $companySettings['companyAddress'],
                'companyPhone' => $companySettings['companyPhone'],
                'companyEmail' => $companySettings['companyEmail']
            ];

            // Test if view exists
            if (!view()->exists('invoices.pdf')) {
                return response()->json(['error' => 'Invoice template not found'], 500);
            }

            // Generate PDF
            $pdf = PDF::loadView('invoices.pdf', $viewData);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Generate filename
            $filename = 'Invoice-' . $order->id . '.pdf';

            // Return PDF download
            return $pdf->download($filename);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}