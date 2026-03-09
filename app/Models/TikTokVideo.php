<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TikTokVideo extends Model
{
    use HasFactory;

    protected $table = 'tik_tok_videos';

    protected $fillable = [
        'video_id',
        'url',
        'product_link',
        'embed_code',
        'product_price',
        'product_title',
        'product_price',
        'product_image',
        'product_currency',
    ];
    protected $casts = [
        'is_selected' => 'boolean',
        'product_price' => 'decimal:2',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
