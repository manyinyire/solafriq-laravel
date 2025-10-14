<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SolarSystem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        $cartItems = $cart ? $cart->items()->with(['solarSystem', 'product'])->get() : collect();

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
                'type' => 'required|in:solar_system,product',
                'system_id' => 'required_if:type,solar_system|exists:solar_systems,id',
                'product_id' => 'required_if:type,product|exists:products,id',
                'quantity' => 'required|integer|min:1|max:100',
            ]);

            $cart = $this->getCart();

            if ($request->type === 'solar_system') {
                $solarSystem = SolarSystem::findOrFail($request->system_id);
                
                $existingItem = $cart->items()
                    ->where('solar_system_id', $solarSystem->id)
                    ->where('item_type', 'solar_system')
                    ->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $request->quantity,
                    ]);
                } else {
                    $cart->items()->create([
                        'solar_system_id' => $solarSystem->id,
                        'item_type' => 'solar_system',
                        'quantity' => $request->quantity,
                        'price' => $solarSystem->price,
                    ]);
                }

                return back()->with('success', 'Solar system added to cart successfully!');
            } else {
                $product = Product::findOrFail($request->product_id);
                
                if (!$product->is_active || $product->stock_quantity < $request->quantity) {
                    return back()->withErrors(['error' => 'Product is out of stock or insufficient quantity available.']);
                }

                $existingItem = $cart->items()
                    ->where('product_id', $product->id)
                    ->where('item_type', 'product')
                    ->first();

                if ($existingItem) {
                    $newQuantity = $existingItem->quantity + $request->quantity;
                    if ($newQuantity > $product->stock_quantity) {
                        return back()->withErrors(['error' => 'Cannot add more items. Stock limit reached.']);
                    }
                    $existingItem->update([
                        'quantity' => $newQuantity,
                    ]);
                } else {
                    $cart->items()->create([
                        'product_id' => $product->id,
                        'item_type' => 'product',
                        'quantity' => $request->quantity,
                        'price' => $product->price,
                    ]);
                }

                return back()->with('success', 'Product added to cart successfully!');
            }
        } catch (\Exception $e) {
            Log::error('Cart add error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request' => $request->all(),
            ]);
            return back()->withErrors(['error' => 'Failed to add item to cart. Please try again.']);
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
