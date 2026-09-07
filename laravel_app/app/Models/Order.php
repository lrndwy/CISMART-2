<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'shipping_notes',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_distance_meters',
        'payment_method',
        'payment_status',
        'qris_type',
        'qris_payload',
        'payment_proof_path',
        'payment_verified_at',
        'payment_rejected_at',
        'payment_rejection_note',
        'shipping_method',
        'subtotal',
        'shipping_cost',
        'total',
        'status',
        'shop_whatsapp',
        'whatsapp_sent_at',
        'notes',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'delivery_distance_meters' => 'integer',
        'whatsapp_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.date('YmdHis').'-'.strtoupper(substr(uniqid(), -4));
            }
        });
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'whatsapp-order' => 'Pesan via WhatsApp',
            'bank-transfer' => 'Transfer Bank',
            'cod' => 'Bayar di Tempat',
            'qris' => 'QRIS',
            default => $this->payment_method,
        };
    }

    /**
     * Get the shipping method label.
     */
    public function getShippingMethodLabelAttribute(): string
    {
        if (! $this->shipping_method || $this->shipping_method === 'none') {
            return 'Tidak ada pengiriman';
        }

        return match ($this->shipping_method) {
            'pickup' => 'Ambil di toko',
            'delivery' => 'Kirim ke lokasi',
            'regular' => 'Reguler (3-5 hari)',
            'express' => 'Express (1-2 hari)',
            'same-day' => 'Same Day (Cilacap)',
            default => $this->shipping_method,
        };
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'success',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'delivered' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function isQris(): bool
    {
        return $this->payment_method === 'qris';
    }

    public function needsPaymentProof(): bool
    {
        return $this->isQris() && in_array($this->payment_status, ['unpaid', 'rejected'], true);
    }

    public function isAwaitingPaymentVerification(): bool
    {
        return $this->payment_status === 'awaiting_verification';
    }

    public function markPaymentProofUploaded(string $path): void
    {
        $this->update([
            'payment_proof_path' => $path,
            'payment_status' => 'awaiting_verification',
            'payment_rejected_at' => null,
            'payment_rejection_note' => null,
        ]);
    }

    public function verifyPayment(): void
    {
        $this->update([
            'payment_status' => 'verified',
            'payment_verified_at' => now(),
            'payment_rejected_at' => null,
            'payment_rejection_note' => null,
            'status' => $this->status === 'pending' ? 'confirmed' : $this->status,
            'confirmed_at' => $this->confirmed_at ?? now(),
        ]);
    }

    public function rejectPayment(?string $note = null): void
    {
        $this->update([
            'payment_status' => 'rejected',
            'payment_rejected_at' => now(),
            'payment_rejection_note' => $note,
        ]);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Belum dibayar',
            'awaiting_verification' => 'Menunggu verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'not_required' => '-',
            default => $this->payment_status ?? '-',
        };
    }

    /**
     * Scope a query to only include orders with a specific status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed orders.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}
