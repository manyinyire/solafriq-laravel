<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #1f2937;
            font-size: 22px;
            margin: 0 0 5px 0;
        }
        h2 {
            color: #1f2937;
            font-size: 18px;
            margin: 0;
        }
        h3 {
            color: #1f2937;
            font-size: 13px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 4px;
            margin: 0 0 8px 0;
        }
        h4 {
            color: #1f2937;
            font-size: 12px;
            margin: 0 0 8px 0;
        }
        p {
            margin: 2px 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 3px solid #1f2937;
        }
        .header-table td {
            padding-bottom: 10px;
            vertical-align: top;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #1f2937;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        .payment-badge {
            display: inline-block;
            padding: 5px 10px;
            font-weight: bold;
            border: 2px solid;
            margin-top: 8px;
        }
        .paid {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #065f46;
        }
        .unpaid {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #991b1b;
        }
        .info-table {
            width: 100%;
            margin: 15px 0;
        }
        .info-table td {
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .items-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 280px;
            float: right;
            margin: 15px 0;
        }
        .totals-table td {
            padding: 5px 8px;
        }
        .total-row {
            border-top: 2px solid #1f2937;
            font-weight: bold;
            font-size: 13px;
        }
        .notes-box {
            clear: both;
            margin-top: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border-left: 4px solid #1f2937;
        }
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 60%;">
                <div class="company-name">{{ $companyName ?? 'SolarFriq' }}</div>
                <p style="color: #666;">{{ $companyAddress ?? 'Solar Energy Solutions' }}</p>
                <p style="color: #666;">{{ $companyPhone ?? 'Phone: +1 (555) 123-4567' }}</p>
                <p style="color: #666;">{{ $companyEmail ?? 'Email: info@solarfriq.com' }}</p>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <p><strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
                <p><strong>Due:</strong> {{ $invoice->created_at->addDays(30)->format('M d, Y') }}</p>
                <div class="payment-badge {{ $invoice->payment_status === 'PAID' ? 'paid' : 'unpaid' }}">
                    {{ $invoice->payment_status === 'PAID' ? 'PAID' : 'UNPAID' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Billing Info -->
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <h3>Bill To</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->customer_email }}</p>
                @if($order->customer_phone)
                <p>{{ $order->customer_phone }}</p>
                @endif
                @if($order->customer_address)
                <p>{{ $order->customer_address }}</p>
                @endif
            </td>
            <td>
                <h3>Order Details</h3>
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
                <p><strong>Status:</strong> {{ $order->status }}</p>
                @if($order->tracking_number)
                <p><strong>Tracking:</strong> {{ $order->tracking_number }}</p>
                @endif
            </td>
        </tr>
    </table>

    <!-- Items -->
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-right" style="width: 15%;">Qty</th>
                <th class="text-right" style="width: 17%;">Unit Price</th>
                <th class="text-right" style="width: 18%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->name }}</strong>
                    @if($item->description)
                    <br><span style="font-size: 10px;">{{ $item->description }}</span>
                    @endif
                    @if($item->type)
                    <br><span style="font-size: 10px; font-style: italic;">{{ ucwords(str_replace('_', ' ', $item->type)) }}</span>
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
    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->tax > 0)
        <tr>
            <td><strong>Tax:</strong></td>
            <td class="text-right">${{ number_format($invoice->tax, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td><strong>TOTAL:</strong></td>
            <td class="text-right">${{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

    <!-- Payment Info -->
    @if($invoice->payment_status === 'PAID')
    <div class="notes-box">
        <h4>Payment Information</h4>
        <p><strong>Status:</strong> Paid in Full</p>
        @if($order->payment_method)
        <p><strong>Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
        @endif
        <p><strong>Date:</strong> {{ $invoice->updated_at->format('M d, Y') }}</p>
    </div>
    @else
    <div class="notes-box">
        <h4>Payment Instructions</h4>
        <p>Payment is due within 30 days. Please include invoice number {{ $invoice->invoice_number }} with your payment.</p>
        @if($order->payment_method)
        <p><strong>Preferred Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
        @endif
    </div>
    @endif

    <!-- Notes -->
    @if($order->notes)
    <div class="notes-box">
        <h4>Order Notes</h4>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Thank you for choosing {{ $companyName ?? 'SolarFriq' }}!</strong></p>
        <p>Questions? Contact us at {{ $companyEmail ?? 'info@solarfriq.com' }}</p>
    </div>
</body>
</html>
