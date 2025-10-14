<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            padding: 20px;
        }
        
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #f97316;
            padding-bottom: 20px;
        }
        
        .company-info {
            float: left;
            width: 50%;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 10px;
        }
        
        .quote-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        
        .quote-number {
            font-size: 20px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 10px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #f97316;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #f97316;
        }
        
        .customer-details {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table thead {
            background-color: #f97316;
            color: white;
        }
        
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals-table {
            width: 40%;
            float: right;
            margin-top: 20px;
        }
        
        .totals-table td {
            padding: 8px;
        }
        
        .totals-table .total-row {
            font-weight: bold;
            font-size: 14px;
            background-color: #f97316;
            color: white;
        }
        
        .terms {
            margin-top: 40px;
            padding: 15px;
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-accepted {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <div class="company-logo">{{ $companySettings['company_name'] ?? 'SolaFriq' }}</div>
                <div>{{ $companySettings['company_email'] ?? 'info@solafriq.com' }}</div>
                <div>{{ $companySettings['company_phone'] ?? '+263 77 123 4567' }}</div>
                <div>{{ $companySettings['company_address'] ?? 'Harare, Zimbabwe' }}</div>
            </div>
            <div class="quote-info">
                <div class="quote-number">QUOTATION</div>
                <div><strong>Quote #:</strong> {{ $quote->quote_number }}</div>
                <div><strong>Date:</strong> {{ $quote->created_at->format('d M, Y') }}</div>
                @if($quote->valid_until)
                <div><strong>Valid Until:</strong> {{ $quote->valid_until->format('d M, Y') }}</div>
                @endif
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="section">
            <div class="section-title">Customer Information</div>
            <div class="customer-details">
                <div><strong>Name:</strong> {{ $quote->customer_name }}</div>
                <div><strong>Email:</strong> {{ $quote->customer_email }}</div>
                @if($quote->customer_phone)
                <div><strong>Phone:</strong> {{ $quote->customer_phone }}</div>
                @endif
                @if($quote->customer_address)
                <div><strong>Address:</strong> {{ $quote->customer_address }}</div>
                @endif
            </div>
        </div>

        <!-- Quote Items -->
        <div class="section">
            <div class="section-title">Quote Items</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 40%;">Item Description</th>
                        <th style="width: 15%;" class="text-right">Quantity</th>
                        <th style="width: 17.5%;" class="text-right">Unit Price</th>
                        <th style="width: 17.5%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->item_name }}</strong>
                            @if($item->item_description)
                            <br><small style="color: #6b7280;">{{ $item->item_description }}</small>
                            @endif
                        </td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="clearfix">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($quote->subtotal, 2) }}</td>
                </tr>
                @if($quote->tax > 0)
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">${{ number_format($quote->tax, 2) }}</td>
                </tr>
                @endif
                @if($quote->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-${{ number_format($quote->discount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td class="text-right">${{ number_format($quote->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($quote->notes)
        <div class="section" style="clear: both; margin-top: 30px;">
            <div class="section-title">Notes</div>
            <div style="padding: 10px; background-color: #f9fafb;">
                {{ $quote->notes }}
            </div>
        </div>
        @endif

        <!-- Terms and Conditions -->
        @if($quote->terms_and_conditions)
        <div class="terms">
            <div style="font-weight: bold; margin-bottom: 10px;">Terms & Conditions</div>
            <div style="font-size: 11px;">{{ $quote->terms_and_conditions }}</div>
        </div>
        @else
        <div class="terms">
            <div style="font-weight: bold; margin-bottom: 10px;">Terms & Conditions</div>
            <div style="font-size: 11px;">
                <ul style="margin-left: 20px;">
                    <li>This quote is valid for 30 days from the date of issue.</li>
                    <li>Prices are subject to change without notice.</li>
                    <li>Payment terms: 50% deposit required, balance due upon installation.</li>
                    <li>Installation timeline will be confirmed upon order acceptance.</li>
                    <li>All products come with manufacturer warranty.</li>
                </ul>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div>{{ $companySettings['company_name'] ?? 'SolaFriq' }} | {{ $companySettings['company_email'] ?? 'info@solafriq.com' }} | {{ $companySettings['company_phone'] ?? '+263 77 123 4567' }}</div>
        </div>
    </div>
</body>
</html>
