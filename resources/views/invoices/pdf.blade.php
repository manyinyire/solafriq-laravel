<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1f2937;
            padding-bottom: 20px;
        }
        .company-info h1 {
            color: #1f2937;
            margin: 0;
            font-size: 28px;
        }
        .company-info p {
            margin: 5px 0;
            color: #666;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }
        .payment-status {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        .payment-status.paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .payment-status.unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .billing-info {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
        }
        .billing-section {
            width: 48%;
        }
        .billing-section h3 {
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #1f2937;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 30px;
            text-align: right;
        }
        .totals table {
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 15px;
            border: none;
        }
        .totals .total-label {
            font-weight: bold;
            color: #1f2937;
        }
        .totals .total-amount {
            font-weight: bold;
            color: #1f2937;
            font-size: 18px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #1f2937;
        }
        .notes h4 {
            margin-top: 0;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1>{{ $companyName ?? 'SolarFriq' }}</h1>
            <p>{{ $companyAddress ?? 'Solar Energy Solutions' }}</p>
            <p>{{ $companyPhone ?? 'Phone: +1 (555) 123-4567' }}</p>
            <p>{{ $companyEmail ?? 'Email: info@solarfriq.com' }}</p>
        </div>
        <div class="invoice-info">
            <h2 class="invoice-number">{{ $invoice->invoice_number }}</h2>
            <p><strong>Date:</strong> {{ $invoice->created_at->format('F d, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->created_at->addDays(30)->format('F d, Y') }}</p>

            <div class="payment-status {{ $invoice->payment_status === 'PAID' ? 'paid' : 'unpaid' }}">
                {{ $invoice->payment_status === 'PAID' ? 'PAID' : 'UNPAID' }}
            </div>
        </div>
    </div>

    <!-- Billing Information -->
    <div class="billing-info">
        <div class="billing-section">
            <h3>Bill To:</h3>
            <p><strong>{{ $order->customer_name }}</strong></p>
            <p>{{ $order->customer_email }}</p>
            @if($order->customer_phone)
                <p>{{ $order->customer_phone }}</p>
            @endif
            @if($order->customer_address)
                <p>{{ $order->customer_address }}</p>
            @endif
        </div>
        <div class="billing-section">
            <h3>Order Details:</h3>
            <p><strong>Order #:</strong> {{ $order->id }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
            <p><strong>Status:</strong> {{ $order->status }}</p>
            @if($order->tracking_number)
                <p><strong>Tracking:</strong> {{ $order->tracking_number }}</p>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->name }}</strong>
                    @if($item->description)
                        <br><small>{{ $item->description }}</small>
                    @endif
                    @if($item->type)
                        <br><small><em>Type: {{ ucwords(str_replace('_', ' ', $item->type)) }}</em></small>
                    @endif
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td class="total-label">Subtotal:</td>
                <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->tax > 0)
            <tr>
                <td class="total-label">Tax:</td>
                <td class="text-right">${{ number_format($invoice->tax, 2) }}</td>
            </tr>
            @endif
            <tr style="border-top: 2px solid #1f2937;">
                <td class="total-label total-amount">Total:</td>
                <td class="text-right total-amount">${{ number_format($invoice->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Payment Information -->
    @if($invoice->payment_status === 'PAID')
    <div class="notes">
        <h4>Payment Information</h4>
        <p><strong>Payment Status:</strong> Paid in Full</p>
        @if($order->payment_method)
            <p><strong>Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
        @endif
        <p><strong>Payment Date:</strong> {{ $invoice->updated_at->format('F d, Y') }}</p>
    </div>
    @else
    <div class="notes">
        <h4>Payment Instructions</h4>
        <p>Payment is due within 30 days of the invoice date. Please include the invoice number {{ $invoice->invoice_number }} with your payment.</p>
        @if($order->payment_method)
            <p><strong>Preferred Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
        @endif
    </div>
    @endif

    <!-- Order Notes -->
    @if($order->notes)
    <div class="notes">
        <h4>Order Notes</h4>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for choosing {{ $companyName ?? 'SolarFriq' }} for your solar energy needs!</p>
        <p>For questions about this invoice, please contact us at {{ $companyEmail ?? 'info@solarfriq.com' }}</p>
    </div>
</body>
</html>