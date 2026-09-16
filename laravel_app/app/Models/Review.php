<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = ['shop_id', 'user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    protected static function booted(): void
    {
        static::saved(fn (Review $review) => $review->shop?->refreshRating());
        static::deleted(fn (Review $review) => $review->shop?->refreshRating());
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
