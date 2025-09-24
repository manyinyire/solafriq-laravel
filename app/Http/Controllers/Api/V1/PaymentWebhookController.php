<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\InstallmentPayment;
use App\Services\OrderProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    protected $orderService;

    public function __construct(OrderProcessingService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function paystack(Request $request): JsonResponse
    {
        // Verify webhook signature
        if (!$this->verifyPaystackSignature($request)) {
            Log::warning('Invalid Paystack webhook signature', [
                'ip' => $request->ip(),
                'payload' => $request->all()
            ]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Paystack webhook received', [
            'event' => $event,
            'reference' => $data['reference'] ?? 'unknown'
        ]);

        try {
            switch ($event) {
                case 'charge.success':
                    return $this->handlePaystackSuccess($data);
                case 'charge.failed':
                    return $this->handlePaystackFailure($data);
                case 'transfer.success':
                    return $this->handlePaystackTransferSuccess($data);
                case 'transfer.failed':
                    return $this->handlePaystackTransferFailure($data);
                default:
                    Log::info('Unhandled Paystack webhook event', ['event' => $event]);
                    return response()->json(['message' => 'Event not handled']);
            }
        } catch (\Exception $e) {
            Log::error('Error processing Paystack webhook', [
                'event' => $event,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    public function flutterwave(Request $request): JsonResponse
    {
        // Verify webhook signature
        if (!$this->verifyFlutterwaveSignature($request)) {
            Log::warning('Invalid Flutterwave webhook signature', [
                'ip' => $request->ip(),
                'payload' => $request->all()
            ]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Flutterwave webhook received', [
            'event' => $event,
            'tx_ref' => $data['tx_ref'] ?? 'unknown'
        ]);

        try {
            switch ($event) {
                case 'charge.completed':
                    return $this->handleFlutterwaveSuccess($data);
                case 'charge.failed':
                    return $this->handleFlutterwaveFailure($data);
                default:
                    Log::info('Unhandled Flutterwave webhook event', ['event' => $event]);
                    return response()->json(['message' => 'Event not handled']);
            }
        } catch (\Exception $e) {
            Log::error('Error processing Flutterwave webhook', [
                'event' => $event,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    private function handlePaystackSuccess(array $data): JsonResponse
    {
        $reference = $data['reference'];
        $amount = $data['amount'] / 100; // Paystack amount is in kobo

        // Check if this is an order payment or installment payment
        if (str_contains($reference, 'order_')) {
            return $this->processOrderPayment($reference, $amount, 'paystack', $data);
        } elseif (str_contains($reference, 'installment_')) {
            return $this->processInstallmentPayment($reference, $amount, 'paystack', $data);
        }

        Log::warning('Unknown payment reference format', ['reference' => $reference]);
        return response()->json(['message' => 'Unknown payment reference']);
    }

    private function handlePaystackFailure(array $data): JsonResponse
    {
        $reference = $data['reference'];

        Log::info('Paystack payment failed', [
            'reference' => $reference,
            'gateway_response' => $data['gateway_response'] ?? 'Unknown error'
        ]);

        // Mark payment as failed in database
        DB::table('payment_transactions')
            ->where('reference', $reference)
            ->update([
                'status' => 'FAILED',
                'gateway_response' => $data['gateway_response'] ?? 'Payment failed',
                'updated_at' => now()
            ]);

        return response()->json(['message' => 'Payment failure processed']);
    }

    private function handleFlutterwaveSuccess(array $data): JsonResponse
    {
        $reference = $data['tx_ref'];
        $amount = $data['amount'];

        // Check if this is an order payment or installment payment
        if (str_contains($reference, 'order_')) {
            return $this->processOrderPayment($reference, $amount, 'flutterwave', $data);
        } elseif (str_contains($reference, 'installment_')) {
            return $this->processInstallmentPayment($reference, $amount, 'flutterwave', $data);
        }

        Log::warning('Unknown payment reference format', ['reference' => $reference]);
        return response()->json(['message' => 'Unknown payment reference']);
    }

    private function handleFlutterwaveFailure(array $data): JsonResponse
    {
        $reference = $data['tx_ref'];

        Log::info('Flutterwave payment failed', [
            'reference' => $reference,
            'processor_response' => $data['processor_response'] ?? 'Unknown error'
        ]);

        // Mark payment as failed in database
        DB::table('payment_transactions')
            ->where('reference', $reference)
            ->update([
                'status' => 'FAILED',
                'gateway_response' => $data['processor_response'] ?? 'Payment failed',
                'updated_at' => now()
            ]);

        return response()->json(['message' => 'Payment failure processed']);
    }

    private function processOrderPayment(string $reference, float $amount, string $gateway, array $data): JsonResponse
    {
        $orderId = str_replace('order_', '', $reference);
        $order = Order::find($orderId);

        if (!$order) {
            Log::warning('Order not found for payment', ['reference' => $reference]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Process the payment
        $this->orderService->processOrderPayment($order, [
            'amount' => $amount,
            'payment_method' => $gateway,
            'payment_reference' => $reference,
            'gateway_data' => $data
        ]);

        return response()->json(['message' => 'Order payment processed successfully']);
    }

    private function processInstallmentPayment(string $reference, float $amount, string $gateway, array $data): JsonResponse
    {
        $paymentId = str_replace('installment_', '', $reference);
        $payment = InstallmentPayment::find($paymentId);

        if (!$payment) {
            Log::warning('Installment payment not found', ['reference' => $reference]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Process the installment payment
        $this->orderService->processInstallmentPayment($payment, [
            'amount' => $amount,
            'payment_method' => $gateway,
            'payment_reference' => $reference,
            'gateway_data' => $data
        ]);

        return response()->json(['message' => 'Installment payment processed successfully']);
    }

    private function verifyPaystackSignature(Request $request): bool
    {
        $signature = $request->header('x-paystack-signature');
        $secretKey = config('services.paystack.secret_key');

        if (!$signature || !$secretKey) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', $request->getContent(), $secretKey);

        return hash_equals($signature, $computedSignature);
    }

    private function verifyFlutterwaveSignature(Request $request): bool
    {
        $signature = $request->header('verif-hash');
        $secretKey = config('services.flutterwave.secret_hash');

        if (!$signature || !$secretKey) {
            return false;
        }

        return hash_equals($signature, $secretKey);
    }

    private function handlePaystackTransferSuccess(array $data): JsonResponse
    {
        Log::info('Paystack transfer successful', [
            'transfer_code' => $data['transfer_code'] ?? 'unknown',
            'amount' => $data['amount'] ?? 0
        ]);

        return response()->json(['message' => 'Transfer success processed']);
    }

    private function handlePaystackTransferFailure(array $data): JsonResponse
    {
        Log::warning('Paystack transfer failed', [
            'transfer_code' => $data['transfer_code'] ?? 'unknown',
            'amount' => $data['amount'] ?? 0,
            'failure_reason' => $data['failure_reason'] ?? 'Unknown'
        ]);

        return response()->json(['message' => 'Transfer failure processed']);
    }
}