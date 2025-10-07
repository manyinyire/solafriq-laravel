<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\InvoiceGeneratorService;
use Illuminate\Console\Command;

class GenerateMissingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for orders that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceGeneratorService $invoiceService)
    {
        $this->info('Checking for orders without invoices...');

        $ordersWithoutInvoices = Order::doesntHave('invoice')->get();

        if ($ordersWithoutInvoices->isEmpty()) {
            $this->info('All orders have invoices!');
            return 0;
        }

        $this->info("Found {$ordersWithoutInvoices->count()} orders without invoices.");

        $bar = $this->output->createProgressBar($ordersWithoutInvoices->count());
        $bar->start();

        foreach ($ordersWithoutInvoices as $order) {
            try {
                $invoice = $invoiceService->generateInvoice($order);
                $this->newLine();
                $this->info("✓ Generated invoice {$invoice->invoice_number} for Order #{$order->id}");
                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("✗ Failed to generate invoice for Order #{$order->id}: {$e->getMessage()}");
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Invoice generation complete!');

        return 0;
    }
}
