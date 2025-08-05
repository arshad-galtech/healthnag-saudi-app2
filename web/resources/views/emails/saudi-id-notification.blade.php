<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saudi ID Upload Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1b2b6b, #2c3e8b);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 30px 20px;
        }
        .content h2 {
            color: #1b2b6b;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .info-section {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row {
            margin: 12px 0;
            padding: 12px;
            background-color: white;
            border-radius: 4px;
            border-left: 4px solid #1b2b6b;
        }
        .label {
            font-weight: bold;
            color: #1b2b6b;
            display: inline-block;
            min-width: 140px;
        }
        .value {
            color: #333;
        }
        .upload-info {
            background: linear-gradient(135deg, #e8f4f8, #f0f8ff);
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
        }
        .upload-info h3 {
            color: #1b2b6b;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .upload-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }
        .upload-detail {
            background: white;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .upload-detail .label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .upload-detail .value {
            font-size: 14px;
            font-weight: 600;
            color: #1b2b6b;
            margin-top: 4px;
        }
        .attachment-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .attachment-notice .icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .warning h4 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .warning p {
            color: #856404;
            margin: 8px 0;
        }
        .action-required {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .action-required h4 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .action-required p {
            margin: 0;
            opacity: 0.9;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
        }
        @media (max-width: 600px) {
            .upload-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🇸🇦 Saudi ID Upload Notification</h1>
        </div>
        
        <div class="content">
            <h2>New Order with Saudi ID Upload</h2>
            
            <div class="info-section">
                <div class="info-row">
                    <span class="label">Order ID:</span>
                    <span class="value">{{ $orderData['order_id'] ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Order Number:</span>
                    <span class="value">#{{ $orderData['order_number'] ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Customer Name:</span>
                    <span class="value">{{ $orderData['customer_name'] ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Customer Email:</span>
                    <span class="value">{{ $orderData['customer_email'] ?? 'N/A' }}</span>
                </div>
                
                @if(!empty($orderData['shipping_address']))
                <div class="info-row">
                    <span class="label">Shipping Address:</span>
                    <span class="value">{{ $orderData['shipping_address'] }}</span>
                </div>
                @endif
            </div>

            @if($uploadRecord)
                <div class="upload-info">
                    <h3>📄 Uploaded ID Information</h3>
                    
                    <div class="upload-details">
                        <div class="upload-detail">
                            <div class="label">Original Filename</div>
                            <div class="value">{{ $uploadRecord->original_filename }}</div>
                        </div>
                        
                        <div class="upload-detail">
                            <div class="label">File Size</div>
                            <div class="value">{{ number_format($uploadRecord->file_size / 1024, 2) }} KB</div>
                        </div>
                        
                        <div class="upload-detail">
                            <div class="label">Upload Date</div>
                            <div class="value">{{ $uploadRecord->created_at->format('M d, Y H:i') }}</div>
                        </div>
                        
                        <div class="upload-detail">
                            <div class="label">File Type</div>
                            <div class="value">{{ $uploadRecord->mime_type }}</div>
                        </div>
                    </div>
                    
                    <div class="attachment-notice">
                        <div class="icon">📎</div>
                        <strong>ID document is attached to this email</strong>
                    </div>
                </div>
            @else
                <div class="warning">
                    <h4>⚠️ Upload Record Not Found</h4>
                    <p>The order contains Saudi ID reference but the upload record could not be located in the database.</p>
                    @if(!empty($orderData['saudi_image_url']))
                        <p><span class="label">Image URL:</span> {{ $orderData['saudi_image_url'] }}</p>
                    @endif
                </div>
            @endif
            
            <div class="action-required">
                <h4>📋 Action Required</h4>
                <p>This order requires Saudi ID verification for customs clearance. Please review the attached document and process accordingly.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was automatically generated by the Saudi ID Notification System</p>
            <p>Generated on {{ now()->format('M d, Y \a\t H:i T') }}</p>
        </div>
    </div>
</body>
</html>