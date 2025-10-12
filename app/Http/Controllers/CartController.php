<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SolarSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        $cartItems = $cart ? $cart->items()->with('solarSystem')->get() : collect();

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'cartItems' => $cartItems,
            'total' => $cart ? $cart->total : 0,
            'itemCount' => $cart ? $cart->item_count : 0,
        ]);
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'system_id' => 'required|exists:solar_systems,id',
                'quantity' => 'required|integer|min:1|max:10',
            ]);

            $solarSystem = SolarSystem::findOrFail($request->system_id);
            $cart = $this->getCart();

            $existingItem = $cart->items()->where('solar_system_id', $solarSystem->id)->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $request->quantity,
                ]);
            } else {
                $cart->items()->create([
                    'solar_system_id' => $solarSystem->id,
                    'quantity' => $request->quantity,
                    'price' => $solarSystem->price,
                ]);
            }

            return back()->with('success', 'Product added to cart successfully!');
        } catch (\Exception $e) {
            Log::error('Cart add error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'system_id' => $request->system_id ?? null,
                'quantity' => $request->quantity ?? null,
            ]);
            return back()->withErrors(['error' => 'Failed to add product to cart. Please try again.']);
        }
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart = $this->getCart();

        if ($cartItem->cart_id !== $cart->id) {
            return back()->withErrors(['error' => 'Invalid cart item.']);
        }

        $cartItem->update([
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', 'Cart updated successfully!');
    }

    public function remove(CartItem $cartItem)
    {
        $cart = $this->getCart();

        if ($cartItem->cart_id !== $cart->id) {
            return back()->withErrors(['error' => 'Invalid cart item.']);
        }

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart successfully!');
    }

    public function clear()
    {
        $cart = $this->getCart();

        if ($cart) {
            $cart->items()->delete();
        }

        return back()->with('success', 'Cart cleared successfully!');
    }

    private function getCart(): ?Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            return Cart::firstOrCreate([
                'user_id' => $userId,
                'session_id' => null,
            ]);
        }

        return Cart::firstOrCreate([
            'user_id' => null,
            'session_id' => $sessionId,
        ]);
    }
}
