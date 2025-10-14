<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Quote is Ready</title>
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .price-highlight {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .item-row {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .item-row:last-child {
            border-bottom: none;
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
        <h1 style="margin: 0;">📄 Your Quote is Ready!</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Quote #{{ $quote->quote_number }}</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $quote->customer_name }},</p>
        
        <p>Great news! We've prepared your detailed quote and it's ready for your review.</p>
        
        <div class="price-highlight">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total Quote Amount</div>
            <div style="font-size: 36px; font-weight: bold;">${{ number_format($quote->total, 2) }}</div>
            @if($quote->valid_until)
            <div style="font-size: 14px; opacity: 0.9; margin-top: 10px;">
                Valid until {{ \Carbon\Carbon::parse($quote->valid_until)->format('M d, Y') }}
            </div>
            @endif
        </div>
        
        <div class="quote-box">
            <h3 style="margin-top: 0; color: #f97316;">Quote Summary</h3>
            
            @foreach($quote->items as $item)
            <div class="item-row">
                <div>
                    <strong>{{ $item->item_name }}</strong>
                    <div style="font-size: 13px; color: #6b7280;">Qty: {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}</div>
                </div>
                <div style="font-weight: bold;">${{ number_format($item->total_price, 2) }}</div>
            </div>
            @endforeach
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Subtotal:</span>
                    <span>${{ number_format($quote->subtotal, 2) }}</span>
                </div>
                @if($quote->tax > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Tax:</span>
                    <span>${{ number_format($quote->tax, 2) }}</span>
                </div>
                @endif
                @if($quote->discount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #059669;">
                    <span>Discount:</span>
                    <span>-${{ number_format($quote->discount, 2) }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; margin-top: 10px; padding-top: 10px; border-top: 2px solid #f97316;">
                    <span>Total:</span>
                    <span>${{ number_format($quote->total, 2) }}</span>
                </div>
            </div>
        </div>
        
        <div style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b; margin: 20px 0;">
            <strong>📎 Attached Document</strong>
            <p style="margin: 5px 0 0 0;">Please find the detailed quote PDF attached to this email.</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/quotes/' . $quote->id) }}" class="button">
                View & Accept Quote
            </a>
        </div>
        
        <h3 style="color: #f97316;">Ready to Proceed?</h3>
        <p>If you're happy with this quote, simply click the button above to accept it. Once accepted, we'll:</p>
        <ul style="padding-left: 20px;">
            <li>Create your order immediately</li>
            <li>Send you an invoice</li>
            <li>Begin processing your solar system installation</li>
        </ul>
        
        <p style="margin-top: 30px;">Have questions or need adjustments? Feel free to reach out to our team anytime.</p>
        
        <p style="margin-top: 20px;">
            <strong>Best regards,</strong><br>
            The SolaFriq Team
        </p>
    </div>
    
    <div class="footer">
        <p>Questions? Contact us at support@solafriq.com or call +263 77 123 4567</p>
        <p>&copy; {{ date('Y') }} SolaFriq. All rights reserved.</p>
    </div>
</body>
</html>
