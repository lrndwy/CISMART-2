<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['shop_id', 'product_id', 'order_item_id', 'user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    protected static function booted(): void
    {
        static::saved(fn (Review $review) => $review->refreshAggregates());
        static::deleted(fn (Review $review) => $review->refreshAggregates());
    }

    /**
     * Rating toko dihitung dari seluruh ulasan tokonya (level toko maupun
     * per produk), sedangkan rating produk hanya dari ulasan produk itu.
     */
    public function refreshAggregates(): void
    {
        $this->shop?->refreshRating();

        $this->product?->refreshRating();
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}