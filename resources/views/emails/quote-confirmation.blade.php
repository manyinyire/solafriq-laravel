<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Request Received</title>
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
            border-left: 4px solid #10b981;
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
            width: 120px;
        }
        .highlight-box {
            background: #d1fae5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
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
        <h1 style="margin: 0;">✅ Quote Request Received!</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for your request</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $quote->customer_name }},</p>
        
        <p>Thank you for requesting a quote from SolaFriq! We have successfully received your request and our team is reviewing it.</p>
        
        <div class="highlight-box">
            <h2 style="margin: 0 0 10px 0; color: #059669;">Your Quote Number</h2>
            <div style="font-size: 24px; font-weight: bold; color: #047857;">{{ $quote->quote_number }}</div>
        </div>
        
        <div class="quote-box">
            <h3 style="margin-top: 0; color: #059669;">Request Summary</h3>
            
            <div class="info-row">
                <span class="label">Total Items:</span>
                <span>{{ $quote->items->count() }} item(s)</span>
            </div>
            
            <div class="info-row">
                <span class="label">Estimated Total:</span>
                <span style="font-size: 18px; font-weight: bold;">${{ number_format($quote->total, 2) }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Request Date:</span>
                <span>{{ $quote->created_at->format('M d, Y') }}</span>
            </div>
            
            @if($quote->valid_until)
            <div class="info-row">
                <span class="label">Valid Until:</span>
                <span>{{ \Carbon\Carbon::parse($quote->valid_until)->format('M d, Y') }}</span>
            </div>
            @endif
        </div>
        
        <h3 style="color: #f97316;">What Happens Next?</h3>
        
        <ol style="padding-left: 20px;">
            <li style="margin-bottom: 10px;">Our team will review your request and prepare a detailed quote</li>
            <li style="margin-bottom: 10px;">You'll receive the complete quote via email within 24-48 hours</li>
            <li style="margin-bottom: 10px;">You can review and accept the quote from your account</li>
            <li style="margin-bottom: 10px;">Once accepted, we'll process your order and send an invoice</li>
        </ol>
        
        <div style="background: #dbeafe; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <strong style="color: #1e40af;">💡 Tip:</strong>
            <p style="margin: 5px 0 0 0; color: #1e3a8a;">You can track your quote status anytime by logging into your account.</p>
        </div>
        
        <p style="margin-top: 30px;">If you have any questions or need immediate assistance, please don't hesitate to contact us.</p>
        
        <p style="margin-top: 20px;">
            <strong>Best regards,</strong><br>
            The SolaFriq Team
        </p>
    </div>
    
    <div class="footer">
        <p>Need help? Contact us at support@solafriq.com or call +263 77 123 4567</p>
        <p>&copy; {{ date('Y') }} SolaFriq. All rights reserved.</p>
    </div>
</body>
</html>
