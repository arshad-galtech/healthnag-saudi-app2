<?php

namespace App\Http\Controllers;

use App\Models\IdImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function uploadId(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'shop_domain' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('id_image');
            $shopDomain = $request->input('shop_domain');
            
            // Clean shop domain for use in path
            $cleanShopDomain = preg_replace('/[^a-zA-Z0-9\-]/', '', $shopDomain);
            
            // Generate unique filename
            $filename = $cleanShopDomain . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Store the file in storage/app/public/id_images/
            $path = $file->storeAs('id_images', $filename, 'public');
            
            // Generate the public URL
            $imageUrl = Storage::url($path);
            
            // Store upload record in database
            $upload = IdImageUpload::create([
                'shop_domain' => $shopDomain,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'uploaded',
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);
            
            // Log the upload for audit purposes
            \Log::info('ID image uploaded', [
                'upload_id' => $upload->id,
                'shop_domain' => $shopDomain,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image_url' => $imageUrl,
                'filename' => $filename,
                'upload_id' => $upload->id
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('ID image upload failed', [
                'success' => false,
                'error' => $e->getMessage(),
                'shop_domain' => $request->input('shop_domain')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}