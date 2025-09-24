<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $cartItems = $cart->items()->with('solarSystem')->get();

        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'total' => $cart->total,
            'itemCount' => $cart->item_count,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:1000',
            'payment_method' => 'required|in:card,installment,cash_on_delivery',
            'total' => 'required|numeric|min:0',
        ]);

        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return back()->withErrors(['error' => 'Your cart is empty.']);
        }

        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'total_amount' => $request->total,
                'status' => 'PENDING',
                'payment_status' => $this->getPaymentStatus($request->payment_method),
                'payment_method' => strtoupper($request->payment_method),
                'tracking_number' => $this->generateTrackingNumber(),
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                // Get the solar system details
                $solarSystem = $cartItem->solarSystem;

                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $solarSystem->name,
                    'description' => $solarSystem->description ?? $solarSystem->capacity . 'kW Solar System',
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'image_url' => null, // Set to null or add image handling later
                    'type' => 'solar_system',
                ]);
            }

            // Clear the cart
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'There was an error processing your order. Please try again.']);
        }
    }

    public function success($orderId)
    {
        $order = Order::with(['items'])->findOrFail($orderId);

        // Ensure user can only see their own orders or guest orders without user_id
        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Checkout/Success', [
            'order' => $order,
        ]);
    }

    private function getCart(): ?Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            return Cart::where('user_id', $userId)->with('items')->first();
        }

        return Cart::where('session_id', $sessionId)->with('items')->first();
    }

    private function generateTrackingNumber(): string
    {
        return 'SF' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    private function getPaymentStatus(string $paymentMethod): string
    {
        switch ($paymentMethod) {
            case 'card':
                return 'PAID';
            case 'installment':
            case 'cash_on_delivery':
                return 'PENDING';
            default:
                return 'PENDING';
        }
    }
}
