<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'weight_gram',
        'images',
        'category_id',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'sold_count' => 'integer',
        'images' => 'array',
        'is_featured' => 'boolean',
    ];

    protected $guarded = ['id'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Hitung ulang rating agregat produk dari ulasan produk.
     */
    public function refreshRating(): void
    {
        $aggregate = $this->reviews()
            ->whereNotNull('product_id')
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')
            ->first();

        $this->newQuery()->whereKey($this->getKey())->update([
            'rating' => round((float) $aggregate->average, 2),
            'total_reviews' => (int) $aggregate->total,
        ]);

        $this->refresh();
    }

    public function averageRating(): float
    {
        return (float) ($this->rating ?? 0);
    }

    public function isFavoritedBy(?User $user): bool
    {
        return $user !== null
            && $this->favorites()->where('user_id', $user->id)->exists();
    }

    protected function firstImage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->images ? $this->images[0] ?? null : null,
        );
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Produk publik: status published DAN tokonya sudah disetujui admin.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereHas('shop', fn ($shop) => $shop->where('status', 'approved'));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
