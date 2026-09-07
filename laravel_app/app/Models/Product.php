<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
