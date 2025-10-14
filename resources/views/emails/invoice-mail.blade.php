<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order Confirmed</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .success-box {
            background: #d1fae5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            border: 2px solid #10b981;
        }
        .order-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .timeline {
            margin: 20px 0;
        }
        .timeline-item {
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border-left: 4px solid #10b981;
            border-radius: 4px;
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
        <h1 style="margin: 0;">🎉 Order Confirmed!</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Your invoice is ready</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $order->customer_name }},</p>
        
        <p>Congratulations! Your quote has been accepted and your order is now confirmed.</p>
        
        <div class="success-box">
            <div style="font-size: 48px; margin-bottom: 10px;">✓</div>
            <h2 style="margin: 0 0 10px 0; color: #047857;">Order #{{ $order->order_number }}</h2>
            <div style="font-size: 14px; color: #065f46;">Invoice attached to this email</div>
        </div>
        
        <div class="order-box">
            <h3 style="margin-top: 0; color: #10b981;">Order Summary</h3>
            
            <div class="info-row">
                <span style="color: #6b7280;">Order Number:</span>
                <strong>{{ $order->order_number }}</strong>
            </div>
            
            <div class="info-row">
                <span style="color: #6b7280;">Order Date:</span>
                <strong>{{ $order->created_at->format('M d, Y') }}</strong>
            </div>
            
            <div class="info-row">
                <span style="color: #6b7280;">Total Amount:</span>
                <strong style="font-size: 20px; color: #059669;">${{ number_format($order->total, 2) }}</strong>
            </div>
            
            <div class="info-row">
                <span style="color: #6b7280;">Payment Status:</span>
                <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                    {{ ucfirst($order->payment_status) }}
                </span>
            </div>
        </div>
        
        <h3 style="color: #10b981;">What Happens Next?</h3>
        
        <div class="timeline">
            <div class="timeline-item">
                <strong>1. Payment Processing</strong>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #6b7280;">
                    Our team will contact you within 24 hours to arrange payment details.
                </p>
            </div>
            
            <div class="timeline-item">
                <strong>2. Installation Scheduling</strong>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #6b7280;">
                    Once payment is confirmed, we'll schedule your installation at a convenient time.
                </p>
            </div>
            
            <div class="timeline-item">
                <strong>3. Professional Installation</strong>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #6b7280;">
                    Our certified technicians will install your solar system with care and precision.
                </p>
            </div>
            
            <div class="timeline-item">
                <strong>4. System Activation</strong>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #6b7280;">
                    We'll test and activate your system, ensuring everything works perfectly.
                </p>
            </div>
        </div>
        
        <div style="background: #dbeafe; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <strong style="color: #1e40af;">📎 Invoice Attached</strong>
            <p style="margin: 5px 0 0 0; color: #1e3a8a;">
                Please find your detailed invoice attached to this email. Keep it for your records.
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/client/orders/' . $order->id) }}" class="button">
                View Order Details
            </a>
        </div>
        
        <div style="background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b; margin: 20px 0;">
            <strong>💡 Need Assistance?</strong>
            <p style="margin: 5px 0 0 0;">
                Our support team is here to help! Contact us anytime if you have questions about your order.
            </p>
        </div>
        
        <p style="margin-top: 30px;">Thank you for choosing SolaFriq for your solar energy needs. We're excited to help you harness the power of the sun!</p>
        
        <p style="margin-top: 20px;">
            <strong>Best regards,</strong><br>
            The SolaFriq Team
        </p>
    </div>
    
    <div class="footer">
        <p><strong>Contact Us:</strong> support@solafriq.com | +263 77 123 4567</p>
        <p>Track your order anytime at {{ url('/client/orders') }}</p>
        <p>&copy; {{ date('Y') }} SolaFriq. All rights reserved.</p>
    </div>
</body>
</html>
