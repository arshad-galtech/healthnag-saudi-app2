<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdImageUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_domain',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'status',
        'metadata',
        'email_sent',
        'email_sent_at',
        'order_id',
        'order_number',
        'email_metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'email_metadata' => 'array',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime'
    ];

    /**
     * Get uploads for a specific shop
     */
    public function scopeForShop($query, $shopDomain)
    {
        return $query->where('shop_domain', $shopDomain);
    }

    /**
     * Get uploads by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get uploads that haven't been emailed yet
     */
    public function scopeEmailNotSent($query)
    {
        return $query->where('email_sent', false);
    }

    /**
     * Get uploads by order ID
     */
    public function scopeByOrderId($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Mark email as sent
     */
    public function markEmailSent($emailMetadata = null)
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
            'email_metadata' => $emailMetadata
        ]);
    }
}