<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warranty Certificate - {{ $warranty->serial_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            padding: 20px;
        }
        
        .page {
            page-break-after: always;
            max-width: 750px;
            margin: 0 auto;
        }
        
        .page:last-child {
            page-break-after: auto;
        }
        
        .certificate-container {
            border: 3px solid #2563eb;
            padding: 25px;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            min-height: 950px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #2563eb;
        }
        
        .logo {
            max-width: 100px;
            max-height: 70px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .company-details {
            font-size: 10px;
            color: #64748b;
            margin-top: 6px;
        }
        
        .certificate-title {
            text-align: center;
            margin: 15px 0;
        }
        
        .certificate-title h1 {
            font-size: 28px;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 6px;
        }
        
        .certificate-title .subtitle {
            font-size: 12px;
            color: #64748b;
            font-style: italic;
        }
        
        .certificate-body {
            margin: 18px 0;
        }
        
        .intro-text {
            text-align: center;
            font-size: 12px;
            margin-bottom: 15px;
            color: #475569;
        }
        
        .warranty-details {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 15px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: bold;
            color: #1e40af;
            width: 40%;
        }
        
        .detail-value {
            color: #334155;
            width: 60%;
            text-align: right;
        }
        
        .customer-info {
            background: #f1f5f9;
            padding: 14px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .customer-info h3 {
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .coverage-section {
            margin: 15px 0;
            padding: 14px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        
        .coverage-section h3 {
            color: #92400e;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .coverage-section ul {
            list-style: none;
            padding-left: 0;
        }
        
        .coverage-section li {
            padding: 4px 0;
            padding-left: 20px;
            position: relative;
            font-size: 11px;
        }
        
        .coverage-section li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #059669;
            font-weight: bold;
            font-size: 14px;
        }
        
        .terms-section {
            margin: 15px 0;
            font-size: 10px;
            color: #64748b;
            padding: 14px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .terms-section h4 {
            color: #1e40af;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        .terms-section p {
            margin-bottom: 4px;
            line-height: 1.3;
        }
        
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 2px solid #334155;
            margin-top: 35px;
            padding-top: 6px;
            font-size: 11px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #2563eb;
            font-size: 9px;
            color: #64748b;
        }
        
        .serial-number {
            background: #1e40af;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 12px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .warranty-period {
            text-align: center;
            font-size: 18px;
            color: #1e40af;
            font-weight: bold;
            margin: 12px 0;
            padding: 12px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- PAGE 1: Certificate Header and Warranty Details -->
    <div class="page">
        <div class="certificate-container">
            <!-- Header -->
            <div class="header">
                @if(!empty($companySettings['company_logo']))
                    <img src="{{ public_path($companySettings['company_logo']) }}" alt="{{ $companySettings['company_name'] }}" class="logo">
                @endif
                <div class="company-details">
                    {{ $companySettings['company_address'] }}<br>
                    Phone: {{ $companySettings['company_phone'] }} | Email: {{ $companySettings['company_email'] }}
                </div>
            </div>

            <!-- Certificate Title -->
            <div class="certificate-title">
                <h1>WARRANTY CERTIFICATE</h1>
                <p class="subtitle">Official Product Warranty Documentation</p>
            </div>

            <!-- Serial Number -->
            <div class="serial-number">
                Certificate No: {{ $warranty->serial_number }}
            </div>

            <!-- Certificate Body -->
            <div class="certificate-body">
                <p class="intro-text">
                    This is to certify that the following product is covered under warranty by {{ $companySettings['company_name'] }}
                    in accordance with the terms and conditions specified herein.
                </p>

                <!-- Warranty Period Highlight -->
                <div class="warranty-period">
                    {{ $warranty->warranty_period_months }} Months Warranty Coverage
                </div>

                <!-- Warranty Details -->
                <div class="warranty-details">
                    <div class="detail-row">
                        <span class="detail-label">Product Name:</span>
                        <span class="detail-value">{{ $warranty->product_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order ID:</span>
                        <span class="detail-value">#{{ $order->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Warranty Start Date:</span>
                        <span class="detail-value">{{ $warranty->start_date->format('F d, Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Warranty End Date:</span>
                        <span class="detail-value">{{ $warranty->end_date->format('F d, Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge status-active">{{ $warranty->status }}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Remaining Days:</span>
                        <span class="detail-value">{{ $warranty->remaining_days }} days</span>
                    </div>
                </div>

                <!-- Footer for Page 1 -->
                <div class="footer">
                    <p>This is an official warranty certificate issued by {{ $companySettings['company_name'] }}</p>
                    <p style="margin-top: 5px; font-weight: bold;">Page 1 of 3</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 2: Customer Information and Warranty Coverage -->
    <div class="page">
        <div class="certificate-container">
            <!-- Header -->
            <div class="header">
                @if(!empty($companySettings['company_logo']))
                    <img src="{{ public_path($companySettings['company_logo']) }}" alt="{{ $companySettings['company_name'] }}" class="logo">
                @endif
                <div class="company-details">
                    Warranty Certificate No: {{ $warranty->serial_number }}
                </div>
            </div>

            <!-- Customer Information -->
            <div class="customer-info">
                <h3>Customer Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $order->customer_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $order->customer_email }}</span>
                </div>
                @if($order->customer_phone)
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $order->customer_phone }}</span>
                </div>
                @endif
                @if($order->customer_address)
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $order->customer_address }}</span>
                </div>
                @endif
            </div>

            <!-- Coverage Section -->
            <div class="coverage-section">
                <h3>Warranty Coverage Includes:</h3>
                <ul>
                    <li>Manufacturing defects in materials and workmanship</li>
                    <li>Functional failures under normal use conditions</li>
                    <li>Performance issues as per product specifications</li>
                    <li>Free repair or replacement of defective parts</li>
                    <li>Technical support and maintenance guidance</li>
                    <li>Professional installation verification and support</li>
                </ul>
            </div>

            <!-- Footer for Page 2 -->
            <div class="footer">
                <p>For warranty claims or inquiries, please contact us at {{ $companySettings['company_email'] }} or {{ $companySettings['company_phone'] }}</p>
                <p style="margin-top: 5px; font-weight: bold;">Page 2 of 3</p>
            </div>
        </div>
    </div>

    <!-- PAGE 3: Terms and Conditions -->
    <div class="page">
        <div class="certificate-container">
            <!-- Header -->
            <div class="header">
                @if(!empty($companySettings['company_logo']))
                    <img src="{{ public_path($companySettings['company_logo']) }}" alt="{{ $companySettings['company_name'] }}" class="logo">
                @endif
                <div class="company-details">
                    Warranty Certificate No: {{ $warranty->serial_number }}
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="terms-section">
                <h4>Terms and Conditions:</h4>
                <p>1. This warranty is valid only for the original purchaser and is non-transferable.</p>
                <p>2. The warranty does not cover damage caused by misuse, abuse, accident, or improper installation.</p>
                <p>3. Normal wear and tear, cosmetic damage, and damage from external causes are not covered.</p>
                <p>4. Warranty service must be performed by authorized service centers only.</p>
                <p>5. This warranty is void if the product has been modified or repaired by unauthorized personnel.</p>
                <p>6. Customer must present this certificate and proof of purchase for warranty claims.</p>
                <p>7. {{ $companySettings['company_name'] }} reserves the right to repair or replace defective products at its discretion.</p>
                <p>8. The warranty period begins from the installation date and cannot be extended or transferred.</p>
                <p>9. Any disputes arising from this warranty shall be subject to the jurisdiction of local courts.</p>
                <p>10. {{ $companySettings['company_name'] }} is not liable for any indirect, incidental, or consequential damages.</p>
            </div>

            <div class="terms-section" style="margin-top: 25px;">
                <h4>Exclusions:</h4>
                <p>• Damage caused by natural disasters, fire, flood, or acts of God</p>
                <p>• Damage resulting from improper storage or handling</p>
                <p>• Products used for commercial purposes beyond their rated capacity</p>
                <p>• Failure to follow manufacturer's installation and maintenance guidelines</p>
                <p>• Damage from power surges, lightning, or electrical faults not covered by surge protection</p>
                <p>• Cosmetic issues that do not affect functionality</p>
            </div>

            <div class="terms-section" style="margin-top: 25px;">
                <h4>Claim Process:</h4>
                <p>1. Contact our customer service team with your warranty certificate number</p>
                <p>2. Provide proof of purchase and detailed description of the issue</p>
                <p>3. Our technical team will assess the claim within 3-5 business days</p>
                <p>4. Approved claims will be serviced within 7-14 business days</p>
                <p>5. All warranty services must be performed by authorized personnel</p>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Authorized Signature</strong><br>
                        {{ $companySettings['company_name'] }}
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Date of Issue</strong><br>
                        {{ $generatedDate }}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>This is an official warranty certificate issued by {{ $companySettings['company_name'] }}</p>
                <p style="margin-top: 5px; font-weight: bold;">Keep this certificate in a safe place for future reference</p>
                <p style="margin-top: 5px; font-weight: bold;">Page 3 of 3</p>
            </div>
        </div>
    </div>
</body>
</html>
