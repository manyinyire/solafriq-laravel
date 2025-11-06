<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Get the active cart for the current user or session
     *
     * @return Cart|null
     */
    public function getActiveCart(): ?Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            return Cart::where('user_id', $userId)->with('items')->first();
        }

        return Cart::where('session_id', $sessionId)->with('items')->first();
    }

    /**
     * Get or create cart for current user/session
     *
     * @return Cart
     */
    public function getOrCreateCart(): Cart
    {
        $cart = $this->getActiveCart();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);
        }

        return $cart;
    }

    /**
     * Clear the cart
     *
     * @param Cart $cart
     * @return bool
     */
    public function clearCart(Cart $cart): bool
    {
        $cart->items()->delete();
        return $cart->delete();
    }
}
