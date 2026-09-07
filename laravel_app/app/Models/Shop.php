<?php

namespace App\Models;

use App\Services\QrisPayload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo',
        'banner',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'delivery_enabled',
        'delivery_unit_meters',
        'delivery_rate',
        'status',
        'qris_static_payload',
        'qris_mode',
        'qris_image',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'delivery_enabled' => 'boolean',
        'delivery_unit_meters' => 'integer',
        'delivery_rate' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function hasDelivery(): bool
    {
        return $this->delivery_enabled
            && $this->hasLocation()
            && (int) $this->delivery_unit_meters > 0
            && (int) $this->delivery_rate > 0;
    }

    public function hasQris(): bool
    {
        return filled($this->qris_static_payload) && QrisPayload::isValid($this->qris_static_payload);
    }

    public function qrisPayloadFor(float|int|string $amount, ?string $billNumber = null): string
    {
        if ($this->qris_mode === 'dynamic') {
            return QrisPayload::toDynamic($this->qris_static_payload, $amount, $billNumber);
        }

        return QrisPayload::normalize($this->qris_static_payload);
    }
}
