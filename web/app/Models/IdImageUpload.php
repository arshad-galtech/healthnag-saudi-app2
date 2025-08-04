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
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
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
}