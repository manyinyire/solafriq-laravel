<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuoteItem;
use App\Models\OrderItem;

class FixUnknownQuoteItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:unknown-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix quote and order items that have "Unknown Item" as their name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing Unknown Items in quotes and orders...');

        // Fix Quote Items
        $quoteItems = QuoteItem::where('item_name', 'Unknown Item')
            ->with(['product', 'solarSystem'])
            ->get();

        $this->info("Found {$quoteItems->count()} unknown quote items");

        $fixedQuoteItems = 0;
        foreach ($quoteItems as $item) {
            $itemName = 'Unknown Item';
            $itemDescription = '';

            if ($item->item_type === 'solar_system' && $item->solarSystem) {
                $itemName = $item->solarSystem->name;
                $itemDescription = $item->solarSystem->description ?? $item->solarSystem->short_description ?? null;
            } elseif ($item->item_type === 'product' && $item->product) {
                $itemName = $item->product->name;
                $itemDescription = $item->product->description ?? null;
            } elseif ($item->item_type === 'custom_component' && $item->product) {
                $itemName = $item->product->name;
                $itemDescription = $item->product->description ?? null;
            }

            if ($itemName !== 'Unknown Item') {
                $item->update([
                    'item_name' => $itemName,
                    'item_description' => $itemDescription,
                ]);
                $fixedQuoteItems++;
                $this->line("  Fixed: {$itemName}");
            }
        }

        // Fix Order Items (they might have same issue)
        $orderItems = OrderItem::where('name', 'Unknown Item')->get();
        $this->info("Found {$orderItems->count()} unknown order items");

        $fixedOrderItems = 0;
        foreach ($orderItems as $item) {
            // Order items don't have relationships, so we can't fix them automatically
            $this->warn("  Order item {$item->id} needs manual review");
        }

        $this->info("\nSummary:");
        $this->info("  Fixed {$fixedQuoteItems} quote items");
        $this->info("  Found {$orderItems->count()} order items that need manual review");

        $this->newLine();
        $this->info('Done!');
    }
}
