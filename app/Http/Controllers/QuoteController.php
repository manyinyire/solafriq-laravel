<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Cart;
use App\Models\Order;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    /**
     * List all quotes for the authenticated user
     */
    public function index()
    {
        $quotes = Quote::with(['items'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Client/Quotes', [
            'quotes' => $quotes,
        ]);
    }

    /**
     * Create a quote request from cart
     */
    public function requestQuote(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $cart = $this->getCart();
            
            if (!$cart || $cart->items->isEmpty()) {
                return back()->withErrors(['error' => 'Your cart is empty.']);
            }

            // Auto-create user if not logged in and email doesn't exist
            $user = Auth::user();
            $autoCreatedPassword = null;
            
            if (!$user) {
                $user = \App\Models\User::where('email', $request->customer_email)->first();
                
                if (!$user) {
                    // Generate a random password
                    $autoCreatedPassword = \Illuminate\Support\Str::random(12);
                    
                    // Create new user
                    $user = \App\Models\User::create([
                        'name' => $request->customer_name,
                        'email' => $request->customer_email,
                        'phone' => $request->customer_phone,
                        'address' => $request->customer_address,
                        'password' => bcrypt($autoCreatedPassword),
                        'email_verified_at' => now(), // Auto-verify email
                    ]);
                }
                
                // Log the user in
                Auth::login($user);
            }

            // Create quote
            $quote = Quote::create([
                'quote_number' => Quote::generateQuoteNumber(),
                'user_id' => $user->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'status' => 'pending',
                'subtotal' => $cart->total ?? 0,
                'tax' => 0,
                'discount' => 0,
                'total' => $cart->total ?? 0,
                'notes' => $request->notes,
                'valid_until' => now()->addDays(30),
            ]);

            // Create quote items from cart items
            foreach ($cart->items as $cartItem) {
                // Get item name and description based on type
                $itemName = '';
                $itemDescription = '';
                
                if ($cartItem->item_type === 'solar_system' && $cartItem->solarSystem) {
                    $itemName = $cartItem->solarSystem->name;
                    $itemDescription = $cartItem->solarSystem->description ?? $cartItem->solarSystem->short_description ?? null;
                } elseif ($cartItem->item_type === 'product' && $cartItem->product) {
                    $itemName = $cartItem->product->name;
                    $itemDescription = $cartItem->product->description ?? null;
                } else {
                    $itemName = 'Unknown Item';
                }
                
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'solar_system_id' => $cartItem->solar_system_id,
                    'product_id' => $cartItem->product_id,
                    'item_type' => $cartItem->item_type,
                    'item_name' => $itemName,
                    'item_description' => $itemDescription,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->price,
                    'total_price' => $cartItem->price * $cartItem->quantity,
                ]);
            }

            // Clear cart
            $cart->items()->delete();

            // Send notification to admin
            $this->sendQuoteRequestNotification($quote);

            // Send confirmation to client (with password if auto-created)
            $this->sendQuoteConfirmation($quote, $autoCreatedPassword);

            DB::commit();

            $successMessage = 'Quote request submitted successfully! We will review and send you a detailed quote soon.';
            if ($autoCreatedPassword) {
                $successMessage .= ' An account has been created for you. Check your email for login details.';
            }

            return redirect()->route('quotes.show', $quote->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quote request error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return back()->withErrors(['error' => 'Failed to submit quote request. Please try again. Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Show quote details to client
     */
    public function show($id)
    {
        $quote = Quote::with(['items.solarSystem', 'items.product'])
            ->where('user_id', Auth::id())
            ->orWhere('customer_email', Auth::user()?->email)
            ->findOrFail($id);

        return Inertia::render('Client/QuoteDetails', [
            'quote' => $quote,
        ]);
    }

    /**
     * Client accepts quote
     */
    public function accept($id)
    {
        $quote = Quote::where('user_id', Auth::id())->findOrFail($id);

        if (!$quote->canBeAccepted()) {
            return back()->withErrors(['error' => 'This quote cannot be accepted.']);
        }

        try {
            DB::beginTransaction();

            $quote->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Convert quote to order
            $order = $this->convertQuoteToOrder($quote);

            DB::commit();

            // Send invoice to client
            $this->sendInvoice($order);

            return redirect()->route('client.orders.show', $order->id)
                ->with('success', 'Quote accepted! Your order has been created and invoice sent to your email.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quote acceptance error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to accept quote. Please try again.']);
        }
    }

    /**
     * Client rejects quote
     */
    public function reject(Request $request, $id)
    {
        $quote = Quote::where('user_id', Auth::id())->findOrFail($id);

        $quote->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'notes' => ($quote->notes ?? '') . "\n\nRejection reason: " . ($request->reason ?? 'Not specified'),
        ]);

        return back()->with('success', 'Quote rejected.');
    }

    /**
     * Convert quote to order
     */
    private function convertQuoteToOrder(Quote $quote): Order
    {
        $order = Order::create([
            'order_number' => $this->generateOrderNumber(),
            'user_id' => $quote->user_id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'pending',
            'subtotal' => $quote->subtotal,
            'tax' => $quote->tax,
            'discount' => $quote->discount,
            'total' => $quote->total,
            'shipping_address' => $quote->customer_address,
            'billing_address' => $quote->customer_address,
            'customer_name' => $quote->customer_name,
            'customer_email' => $quote->customer_email,
            'customer_phone' => $quote->customer_phone,
            'notes' => $quote->notes,
        ]);

        // Create order items from quote items
        foreach ($quote->items as $quoteItem) {
            $order->items()->create([
                'solar_system_id' => $quoteItem->solar_system_id,
                'product_id' => $quoteItem->product_id,
                'item_type' => $quoteItem->item_type,
                'item_name' => $quoteItem->item_name,
                'quantity' => $quoteItem->quantity,
                'unit_price' => $quoteItem->unit_price,
                'total_price' => $quoteItem->total_price,
            ]);
        }

        // Link order to quote
        $quote->update(['converted_order_id' => $order->id]);

        return $order;
    }

    /**
     * Generate order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Send quote request notification to admin
     */
    private function sendQuoteRequestNotification(Quote $quote)
    {
        $adminEmail = CompanySetting::where('key', 'company_email')->value('value') ?? config('mail.from.address');
        
        Mail::to($adminEmail)->send(new \App\Mail\QuoteRequestNotification($quote));
    }

    /**
     * Send quote confirmation to client
     */
    private function sendQuoteConfirmation(Quote $quote, $password = null)
    {
        Mail::to($quote->customer_email)->send(new \App\Mail\QuoteConfirmation($quote, $password));
    }

    /**
     * Send invoice to client
     */
    private function sendInvoice(Order $order)
    {
        Mail::to($order->customer_email)->send(new \App\Mail\InvoiceMail($order));
    }

    /**
     * Get current cart
     */
    private function getCart(): ?Cart
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        if ($userId) {
            return Cart::with(['items.solarSystem', 'items.product'])
                ->where('user_id', $userId)
                ->where('session_id', null)
                ->first();
        }

        return Cart::with(['items.solarSystem', 'items.product'])
            ->where('user_id', null)
            ->where('session_id', $sessionId)
            ->first();
    }
}
