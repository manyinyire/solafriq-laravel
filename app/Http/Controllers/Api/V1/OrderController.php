<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\ScheduleInstallationRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderProcessingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
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

        $orders = $query->paginate($request->input('per_page', config('solafriq.default_per_page', 15)));

        return $this->successResponse(
            OrderResource::collection($orders->items()),
            null,
            [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        );
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated(), $request->user());

            return $this->successResponse(
                new OrderResource($order),
                'Order created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create order: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Ensure user can only see their own orders (unless admin)
        if (!$request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $order->load(['items', 'user', 'invoice', 'warranties']);

        return $this->successResponse(new OrderResource($order));
    }

    /**
     * Update the specified order status (Admin only).
     */
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->updateOrder($order, $request->validated());

            return $this->successResponse(
                new OrderResource($order),
                'Order updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update order: ' . $e->getMessage(), 500);
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
            $order = $this->orderService->updateOrderStatus($order, 'PROCESSING');

            return $this->successResponse(
                new OrderResource($order),
                'Order accepted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to accept order: ' . $e->getMessage(), 500);
        }
    }

    public function decline(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        try {
            $order = $this->orderService->updateOrderStatus($order, 'RETURNED');

            return $this->successResponse(
                new OrderResource($order),
                'Order declined successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to decline order: ' . $e->getMessage(), 500);
        }
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->confirmPayment($order, $request->validated());

            return $this->successResponse(
                new OrderResource($order),
                'Payment confirmed successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to confirm payment: ' . $e->getMessage(), 500);
        }
    }

    public function scheduleInstallation(ScheduleInstallationRequest $request, Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->scheduleInstallation($order, $request->validated()['installation_date']);

            return $this->successResponse(
                new OrderResource($order),
                'Installation scheduled successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to schedule installation: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->updateOrderStatus($order, $request->validated()['status']);

            return $this->successResponse(
                new OrderResource($order),
                'Order status updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update order status: ' . $e->getMessage(), 500);
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

            return $this->successResponse(
                new OrderResource($order),
                'Tracking number updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update tracking number: ' . $e->getMessage(), 500);
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

            return $this->successResponse(
                new OrderResource($order),
                'Note added successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add note: ' . $e->getMessage(), 500);
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

            return $this->successResponse(
                new OrderResource($order),
                'Refund processed successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process refund: ' . $e->getMessage(), 500);
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
                return $this->successResponse(null, 'Notification sent successfully');
            } else {
                return $this->errorResponse('Failed to send notification', 500);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send notification: ' . $e->getMessage(), 500);
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

            // Generate invoice if it doesn't exist
            if (!$order->invoice) {
                $invoiceService = app(\App\Services\InvoiceGeneratorService::class);
                $invoiceService->generateInvoice($order);
                $order->load('invoice');
            }

            // Check if PDF class exists
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                return response()->json(['error' => 'PDF generation library not available'], 500);
            }

            // Get company settings from database
            $companySettings = [
                'companyName' => \App\Models\CompanySetting::get('company_name', 'SolarFriq'),
                'companyAddress' => \App\Models\CompanySetting::get('company_address', '123 Solar Street, Energy City, EC 12345'),
                'companyPhone' => \App\Models\CompanySetting::get('company_phone', '+1 (555) 123-4567'),
                'companyEmail' => \App\Models\CompanySetting::get('company_email', 'info@solarfriq.com')
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

    /**
     * Get all scheduled installations
     */
    public function scheduledInstallations(Request $request): JsonResponse
    {
        $query = Order::with(['items', 'user'])
            ->where('status', 'SCHEDULED')
            ->whereNotNull('installation_date')
            ->orderBy('installation_date', 'asc');

        // Filter by date range if provided
        if ($request->filled('from_date')) {
            $query->where('installation_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('installation_date', '<=', $request->input('to_date'));
        }

        $orders = $query->paginate($request->input('per_page', config('solafriq.default_per_page', 15)));

        return response()->json([
            'data' => OrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }
}