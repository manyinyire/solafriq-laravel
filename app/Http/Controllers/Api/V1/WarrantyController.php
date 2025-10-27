<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\Order;
use App\Http\Resources\WarrantyResource;
use App\Http\Resources\WarrantyClaimResource;
use App\Services\WarrantyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WarrantyClaimStatusUpdated;

class WarrantyController extends BaseController
{
    public function __construct(
        private WarrantyService $warrantyService
    ) {}
    public function index(): JsonResponse
    {
        $warranties = Auth::user()->warranties()
            ->with(['order', 'claims'])
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            WarrantyResource::collection($warranties->items()),
            null,
            [
                'current_page' => $warranties->currentPage(),
                'last_page' => $warranties->lastPage(),
                'per_page' => $warranties->perPage(),
                'total' => $warranties->total(),
            ]
        );
    }

    public function adminIndex(): JsonResponse
    {
        $warranties = Warranty::with(['user', 'order', 'claims'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            WarrantyResource::collection($warranties->items()),
            null,
            [
                'current_page' => $warranties->currentPage(),
                'last_page' => $warranties->lastPage(),
                'per_page' => $warranties->perPage(),
                'total' => $warranties->total(),
            ]
        );
    }

    public function show(Warranty $warranty): JsonResponse
    {
        $this->authorize('view', $warranty);

        $warranty->load(['order', 'user', 'claims']);

        return $this->successResponse(new WarrantyResource($warranty));
    }

    public function createClaim(Request $request, Warranty $warranty): JsonResponse
    {
        $this->authorize('createClaim', $warranty);

        $validated = $request->validate([
            'issue_description' => 'required|string|max:1000',
            'issue_type' => 'required|in:DEFECT,DAMAGE,PERFORMANCE,OTHER',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'images' => 'sometimes|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'sometimes|array|max:3',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:5120',
        ]);

        // Handle file uploads
        $imagePaths = [];
        $documentPaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('warranty-claims/images', 'public');
                $imagePaths[] = $path;
            }
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $path = $document->store('warranty-claims/documents', 'public');
                $documentPaths[] = $path;
            }
        }

        $claim = $warranty->claims()->create([
            'user_id' => Auth::id(),
            'claim_number' => $this->generateClaimNumber(),
            'issue_description' => $validated['issue_description'],
            'issue_type' => $validated['issue_type'],
            'priority' => $validated['priority'],
            'status' => 'SUBMITTED',
            'images' => $imagePaths,
            'documents' => $documentPaths,
        ]);

        return $this->successResponse(new WarrantyClaimResource($claim), 'Warranty claim created successfully');
    }

    public function claims(): JsonResponse
    {
        $claims = Auth::user()->warrantyClaims()
            ->with(['warranty.order'])
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            WarrantyClaimResource::collection($claims->items()),
            null,
            [
                'current_page' => $claims->currentPage(),
                'last_page' => $claims->lastPage(),
                'per_page' => $claims->perPage(),
                'total' => $claims->total(),
            ]
        ]);
    }

    public function updateClaim(Request $request, WarrantyClaim $warrantyClaim): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:SUBMITTED,UNDER_REVIEW,APPROVED,REJECTED,RESOLVED',
            'admin_notes' => 'nullable|string|max:1000',
            'resolution_details' => 'nullable|string|max:1000',
            'estimated_repair_date' => 'nullable|date|after_or_equal:today',
        ]);

        $warrantyClaim->update($validated);

        // Send notification to user about status change
        if ($warrantyClaim->user) {
            try {
                $warrantyClaim->user->notify(new WarrantyClaimStatusUpdated($warrantyClaim));
            } catch (\Exception $e) {
                \Log::warning('Failed to send warranty claim notification', [
                    'claim_id' => $warrantyClaim->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $this->successResponse(new WarrantyClaimResource($warrantyClaim->load(['warranty', 'user'])));
    }

    private function generateClaimNumber(): string
    {
        $prefix = 'WC';
        $year = now()->year;
        $lastClaim = WarrantyClaim::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastClaim ? (int)substr($lastClaim->claim_number, -4) + 1 : 1;

        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get eligible orders for warranty creation (Admin only)
     */
    public function eligibleOrders(): JsonResponse
    {
        $this->authorize('viewAny', Warranty::class);

        $orders = $this->warrantyService->getEligibleOrdersForWarranty();

        return response()->json([
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->count(),
                ];
            }),
        ]);
    }

    /**
     * Manually create warranty for an order (Admin only)
     */
    public function createForOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorize('create', Warranty::class);

        $validated = $request->validate([
            'product_name' => 'nullable|string|max:255',
            'warranty_period_months' => 'nullable|integer|min:1|max:240',
            'start_date' => 'nullable|date',
        ]);

        try {
            $warranty = $this->warrantyService->manuallyCreateWarranty($order, $validated);

            return response()->json([
                'success' => true,
                'data' => new WarrantyResource($warranty),
                'message' => 'Warranty created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Download warranty certificate PDF
     */
    public function downloadCertificate(Warranty $warranty)
    {
        $this->authorize('view', $warranty);

        try {
            return $this->warrantyService->downloadWarrantyCertificate($warranty);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate warranty certificate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get warranty statistics (Admin only)
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Warranty::class);

        $stats = $this->warrantyService->getWarrantyStatistics();

        return response()->json([
            'data' => $stats,
        ]);
    }
}