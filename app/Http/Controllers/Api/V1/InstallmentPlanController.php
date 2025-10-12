<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Services\OrderProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class InstallmentPlanController extends Controller
{
    protected $orderService;

    public function __construct(OrderProcessingService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): JsonResponse
    {
        $plans = Auth::user()->installmentPlans()
            ->with(['order', 'payments'])
            ->latest()
            ->paginate(15);

        return response()->json($plans);
    }

    public function adminIndex(): JsonResponse
    {
        $plans = InstallmentPlan::with(['user', 'order', 'payments'])
            ->latest()
            ->paginate(20);

        return response()->json($plans);
    }

    public function show(InstallmentPlan $installmentPlan): JsonResponse
    {
        $this->authorize('view', $installmentPlan);

        $installmentPlan->load(['order', 'payments', 'user']);

        return response()->json($installmentPlan);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'down_payment' => 'required|numeric|min:0',
            'monthly_payment' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1|max:60',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        $plan = Auth::user()->installmentPlans()->create($validated);

        // Create the payment schedule
        $this->createPaymentSchedule($plan);

        return response()->json($plan->load(['order', 'payments']), 201);
    }

    public function update(Request $request, InstallmentPlan $installmentPlan): JsonResponse
    {
        $this->authorize('update', $installmentPlan);

        $validated = $request->validate([
            'monthly_payment' => 'sometimes|numeric|min:0',
            'duration_months' => 'sometimes|integer|min:1|max:60',
            'status' => 'sometimes|in:ACTIVE,COMPLETED,DEFAULTED,CANCELLED',
        ]);

        $installmentPlan->update($validated);

        return response()->json($installmentPlan->load(['order', 'payments']));
    }

    public function destroy(InstallmentPlan $installmentPlan): JsonResponse
    {
        $this->authorize('delete', $installmentPlan);

        $installmentPlan->delete();

        return response()->json(['message' => 'Installment plan deleted successfully']);
    }

    public function processPayment(Request $request, InstallmentPlan $installmentPlan, InstallmentPayment $payment): JsonResponse
    {
        $this->authorize('update', $installmentPlan);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
        ]);

        try {
            $result = $this->orderService->processInstallmentPayment($payment, $validated);

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function createPaymentSchedule(InstallmentPlan $plan): void
    {
        $startDate = Carbon::parse($plan->start_date);

        for ($i = 0; $i < $plan->duration_months; $i++) {
            $dueDate = $startDate->copy()->addMonths($i);

            $plan->payments()->create([
                'amount' => $plan->monthly_payment,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'PENDING',
                'payment_number' => $i + 1,
            ]);
        }
    }
}