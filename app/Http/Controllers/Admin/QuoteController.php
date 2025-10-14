<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    /**
     * Display list of all quotes
     */
    public function index(Request $request)
    {
        $query = Quote::with(['user', 'items'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('quote_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $request->search . '%');
            });
        }

        $quotes = $query->paginate(20);

        return Inertia::render('Admin/Quotes', [
            'quotes' => $quotes,
            'filters' => [
                'status' => $request->status ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Show quote details
     */
    public function show($id)
    {
        $quote = Quote::with(['user', 'items.solarSystem', 'items.product'])
            ->findOrFail($id);

        return Inertia::render('Admin/QuoteDetails', [
            'quote' => $quote,
        ]);
    }

    /**
     * Update quote details
     */
    public function update(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'admin_notes' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $total = $request->subtotal + $request->tax - $request->discount;

        $quote->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'discount' => $request->discount,
            'total' => $total,
            'admin_notes' => $request->admin_notes,
            'terms_and_conditions' => $request->terms_and_conditions,
            'valid_until' => $request->valid_until,
        ]);

        return back()->with('success', 'Quote updated successfully!');
    }

    /**
     * Update quote items
     */
    public function updateItems(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        if (!$quote->canBeEdited()) {
            return back()->withErrors(['error' => 'This quote cannot be edited.']);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'nullable|exists:quote_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                if (isset($itemData['id'])) {
                    // Update existing item
                    $item = QuoteItem::find($itemData['id']);
                    if ($item && $item->quote_id === $quote->id) {
                        $item->update([
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                        ]);
                    }
                }
            }

            // Recalculate quote totals
            $subtotal = $quote->items()->sum('total_price');
            $quote->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $quote->tax - $quote->discount,
            ]);

            DB::commit();
            return back()->with('success', 'Quote items updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update quote items.']);
        }
    }

    /**
     * Send quote to client
     */
    public function send($id)
    {
        $quote = Quote::with(['items.solarSystem', 'items.product'])->findOrFail($id);

        if ($quote->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending quotes can be sent.']);
        }

        try {
            // Generate PDF
            $pdf = $this->generateQuotePDF($quote);

            // Send email with PDF attachment
            $this->sendQuoteEmail($quote, $pdf);

            // Update quote status
            $quote->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return back()->with('success', 'Quote sent successfully to ' . $quote->customer_email);
        } catch (\Exception $e) {
            \Log::error('Quote send error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to send quote. Please try again.']);
        }
    }

    /**
     * Download quote as PDF
     */
    public function downloadPDF($id)
    {
        $quote = Quote::with(['items.solarSystem', 'items.product'])->findOrFail($id);
        
        $pdf = $this->generateQuotePDF($quote);
        
        return $pdf->download('quote-' . $quote->quote_number . '.pdf');
    }

    /**
     * Generate quote PDF
     */
    private function generateQuotePDF(Quote $quote)
    {
        $companySettings = CompanySetting::getPublic();
        
        $pdf = Pdf::loadView('quotes.pdf', [
            'quote' => $quote,
            'companySettings' => $companySettings,
        ]);

        return $pdf;
    }

    /**
     * Send quote email
     */
    private function sendQuoteEmail(Quote $quote, $pdf)
    {
        Mail::to($quote->customer_email)->send(new \App\Mail\QuoteMail($quote, $pdf));
    }

    /**
     * Delete quote
     */
    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);

        if ($quote->status === 'accepted') {
            return back()->withErrors(['error' => 'Cannot delete accepted quotes.']);
        }

        $quote->delete();

        return redirect()->route('admin.quotes')->with('success', 'Quote deleted successfully!');
    }
}
