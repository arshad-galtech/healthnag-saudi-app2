<?php

namespace App\Http\Controllers;

use App\Mail\SaudiIdNotification;
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

            // Check if we already have a record for this order
            $existingOrderRecord = IdImageUpload::byOrderId($orderId)->first();
            if ($existingOrderRecord) {
                $uploadRecord = $existingOrderRecord;
                
                // If email already sent for this order, skip sending
                if ($uploadRecord->email_sent) {
                    Log::info('Email already sent for this order', [
                        'order_id' => $orderId,
                        'upload_id' => $uploadRecord->id,
                        'email_sent_at' => $uploadRecord->email_sent_at
                    ]);
                    return response()->json(['status' => 'success', 'message' => 'Email already sent'], 200);
                }
            }

            // Update upload record with order information if we have one
            if ($uploadRecord && !$uploadRecord->order_id) {
                $uploadRecord->update([
                    'order_id' => $orderId,
                    'order_number' => $orderNumber
                ]);
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
            $this->sendSaudiIdNotification($emailData, $uploadRecord);
            
            Log::info('Saudi ID notification sent for order', [
                'order_id' => $orderId,
                'customer_name' => $customerName,
                'has_upload_record' => $uploadRecord ? true : false,
                'email_marked_sent' => $uploadRecord ? true : false
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
    
    private function sendSaudiIdNotification($data, $uploadRecord = null)
    {
        // Prepare attachment path if available
        $attachmentPath = null;
        if ($data['upload_record'] && $data['upload_record']->file_path) {
            $attachmentPath = storage_path('app/public/' . $data['upload_record']->file_path);
        }
        
        try {
            // Send email using Laravel Mailable
            Mail::send(new SaudiIdNotification($data, $data['upload_record'], $attachmentPath));
            
            // Mark email as sent in database if we have an upload record
            if ($uploadRecord) {
                $emailMetadata = [
                    'sent_to' => 'arshad.galtech@gmail.com',
                    'order_id' => $data['order_id'],
                    'order_number' => $data['order_number'],
                    'customer_name' => $data['customer_name'],
                    'has_attachment' => $attachmentPath ? true : false,
                    'sent_via' => 'order_webhook'
                ];
                
                $uploadRecord->markEmailSent($emailMetadata);
                
                Log::info('Email status updated in database', [
                    'upload_id' => $uploadRecord->id,
                    'order_id' => $data['order_id'],
                    'email_sent_at' => $uploadRecord->fresh()->email_sent_at
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send Saudi ID notification email', [
                'error' => $e->getMessage(),
                'order_id' => $data['order_id'],
                'upload_record_id' => $uploadRecord ? $uploadRecord->id : null
            ]);
            
            throw $e; // Re-throw to be caught by main webhook handler
        }
    }
}