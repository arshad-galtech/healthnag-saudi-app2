<?php

namespace App\Http\Controllers;

use App\Models\IdImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OrderWebhookController extends Controller
{
    public function handleOrderCreated(Request $request)
    {
        try {
            $orderData = $request->all();
            
            Log::info('Order created webhook received', [
                'order_id' => $orderData['id'] ?? 'unknown',
                'order_number' => $orderData['order_number'] ?? 'unknown'
            ]);
            
            // Check if order has Saudi ID attributes
            $attributes = $orderData['note_attributes'] ?? [];
            $saudiImageUrl = null;
            $saudiUploadId = null;
            
            // Look for Saudi ID attributes
            foreach ($attributes as $attribute) {
                if ($attribute['name'] === 'saudi_id_image_url') {
                    $saudiImageUrl = $attribute['value'];
                }
                if ($attribute['name'] === 'saudi_upload_id') {
                    $saudiUploadId = $attribute['value'];
                }
            }
            
            // If no Saudi ID data found, return early
            if (!$saudiImageUrl && !$saudiUploadId) {
                Log::info('No Saudi ID data found in order', [
                    'order_id' => $orderData['id']
                ]);
                return response()->json(['status' => 'success'], 200);
            }
            
            // Extract order information
            $orderId = $orderData['id'] ?? 'N/A';
            $orderNumber = $orderData['order_number'] ?? 'N/A';
            $customerName = 'Guest';
            $customerEmail = 'N/A';
            
            // Get customer information
            if (isset($orderData['customer']) && $orderData['customer']) {
                $customer = $orderData['customer'];
                $customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
                $customerEmail = $customer['email'] ?? 'N/A';
                
                if (empty($customerName)) {
                    $customerName = $customer['email'] ?? 'Guest';
                }
            }
            
            // Get shipping address for additional context
            $shippingAddress = '';
            if (isset($orderData['shipping_address']) && $orderData['shipping_address']) {
                $address = $orderData['shipping_address'];
                $shippingAddress = trim(implode(', ', array_filter([
                    $address['address1'] ?? '',
                    $address['city'] ?? '',
                    $address['country'] ?? ''
                ])));
            }
            
            // Try to find the upload record in database
            $uploadRecord = null;
            if ($saudiUploadId && is_numeric($saudiUploadId)) {
                $uploadRecord = IdImageUpload::find($saudiUploadId);
            }
            
            // If no record found by ID, try to find by image URL or shop domain
            if (!$uploadRecord && isset($orderData['shop_domain'])) {
                $uploadRecord = IdImageUpload::where('shop_domain', $orderData['shop_domain'])
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            // Prepare email data
            $emailData = [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'shipping_address' => $shippingAddress,
                'saudi_image_url' => $saudiImageUrl,
                'upload_record' => $uploadRecord
            ];
            
            // Send email notification
            $this->sendSaudiIdNotification($emailData);
            
            Log::info('Saudi ID notification sent for order', [
                'order_id' => $orderId,
                'customer_name' => $customerName,
                'has_upload_record' => $uploadRecord ? true : false
            ]);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('Order webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    private function sendSaudiIdNotification($data)
    {
        $to = 'arshad.galtech@gmail.com';
        $subject = 'Saudi ID Upload - Order #' . $data['order_number'];
        
        // Create email content
        $emailContent = $this->createEmailContent($data);
        
        // Send email using Laravel's Mail facade
        Mail::send([], [], function ($message) use ($to, $subject, $emailContent, $data) {
            $message->to($to)
                    ->subject($subject)
                    ->html($emailContent);
            
            // Attach image if available
            if ($data['upload_record'] && $data['upload_record']->file_path) {
                $filePath = storage_path('app/public/' . $data['upload_record']->file_path);
                if (file_exists($filePath)) {
                    $message->attach($filePath, [
                        'as' => 'saudi_id_' . $data['order_number'] . '.' . pathinfo($filePath, PATHINFO_EXTENSION),
                        'mime' => $data['upload_record']->mime_type ?? 'application/octet-stream'
                    ]);
                }
            }
        });
    }
    
    private function createEmailContent($data)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Saudi ID Upload Notification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1b2b6b; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .info-row { margin: 10px 0; padding: 10px; background-color: white; border-radius: 4px; }
                .label { font-weight: bold; color: #1b2b6b; }
                .image-info { margin: 20px 0; padding: 15px; background-color: #e8f4f8; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🇸🇦 Saudi ID Upload Notification</h1>
                </div>
                
                <div class="content">
                    <h2>New Order with Saudi ID Upload</h2>
                    
                    <div class="info-row">
                        <span class="label">Order ID:</span> ' . htmlspecialchars($data['order_id']) . '
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Order Number:</span> #' . htmlspecialchars($data['order_number']) . '
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Customer Name:</span> ' . htmlspecialchars($data['customer_name']) . '
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Customer Email:</span> ' . htmlspecialchars($data['customer_email']) . '
                    </div>';
        
        if ($data['shipping_address']) {
            $html .= '
                    <div class="info-row">
                        <span class="label">Shipping Address:</span> ' . htmlspecialchars($data['shipping_address']) . '
                    </div>';
        }
        
        if ($data['upload_record']) {
            $html .= '
                    <div class="image-info">
                        <h3>📄 Uploaded ID Information</h3>
                        <p><span class="label">Original Filename:</span> ' . htmlspecialchars($data['upload_record']->original_filename) . '</p>
                        <p><span class="label">File Size:</span> ' . number_format($data['upload_record']->file_size / 1024, 2) . ' KB</p>
                        <p><span class="label">Upload Date:</span> ' . $data['upload_record']->created_at->format('Y-m-d H:i:s') . '</p>
                        <p><span class="label">File Type:</span> ' . htmlspecialchars($data['upload_record']->mime_type) . '</p>
                        <p><strong>📎 ID document is attached to this email</strong></p>
                    </div>';
        } else {
            $html .= '
                    <div class="image-info">
                        <h3>⚠️ Upload Record Not Found</h3>
                        <p>The order contains Saudi ID reference but the upload record could not be located in the database.</p>';
            
            if ($data['saudi_image_url']) {
                $html .= '<p><span class="label">Image URL:</span> ' . htmlspecialchars($data['saudi_image_url']) . '</p>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '
                    <div style="margin-top: 30px; padding: 15px; background-color: #fff3cd; border-radius: 4px;">
                        <h4>📋 Action Required</h4>
                        <p>This order requires Saudi ID verification for customs clearance. Please review the attached document and process accordingly.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}