<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Quote Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .quote-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f97316;
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
            display: inline-block;
            width: 150px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">🔔 New Quote Request</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Quote #{{ $quote->quote_number }}</p>
    </div>
    
    <div class="content">
        <p>Hello Admin,</p>
        
        <p>A new quote request has been submitted and requires your attention.</p>
        
        <div class="quote-box">
            <h2 style="margin-top: 0; color: #f97316;">Quote Details</h2>
            
            <div class="info-row">
                <span class="label">Quote Number:</span>
                <span>{{ $quote->quote_number }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Customer Name:</span>
                <span>{{ $quote->customer_name }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Email:</span>
                <span>{{ $quote->customer_email }}</span>
            </div>
            
            @if($quote->customer_phone)
            <div class="info-row">
                <span class="label">Phone:</span>
                <span>{{ $quote->customer_phone }}</span>
            </div>
            @endif
            
            <div class="info-row">
                <span class="label">Total Amount:</span>
                <span style="font-size: 18px; font-weight: bold; color: #059669;">${{ number_format($quote->total, 2) }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Items:</span>
                <span>{{ $quote->items->count() }} item(s)</span>
            </div>
            
            <div class="info-row">
                <span class="label">Requested:</span>
                <span>{{ $quote->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
        
        @if($quote->notes)
        <div style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b; margin: 20px 0;">
            <strong>Customer Notes:</strong>
            <p style="margin: 10px 0 0 0;">{{ $quote->notes }}</p>
        </div>
        @endif
        
        <div style="text-align: center;">
            <a href="{{ url('/admin/quotes/' . $quote->id) }}" class="button">
                View & Process Quote
            </a>
        </div>
        
        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            Please review the quote details and send a detailed quote to the customer as soon as possible.
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from your SolaFriq Quote Management System.</p>
        <p>&copy; {{ date('Y') }} SolaFriq. All rights reserved.</p>
    </div>
</body>
</html>
