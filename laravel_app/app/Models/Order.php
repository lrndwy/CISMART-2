<?php

namespace App\Models;

use App\Notifications\OrderNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Transisi status yang sah. Semua perubahan status WAJIB lewat sini
     * supaya stok, sold_count, timestamp, riwayat, dan notifikasi konsisten.
     *
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'shop_id',
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
        'expires_at',
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
        'expires_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.date('YmdHis').'-'.strtoupper(substr(uniqid(), -4));
            }

            if (! $order->expires_at) {
                $order->expires_at = $order->paymentExpiryFromNow();
            }
        });

        static::created(function (Order $order) {
            $order->notifyShopOwner('placed');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Batas waktu pembayaran untuk order ini (null bila tidak relevan).
     */
    public function paymentExpiryFromNow(): ?\Illuminate\Support\Carbon
    {
        if ($this->payment_method === 'qris' && in_array($this->payment_status, ['unpaid', 'rejected'], true)) {
            return now()->addHours((int) config('orders.payment_window_hours', 24));
        }

        if ($this->payment_method === 'cod') {
            return now()->addHours((int) config('orders.confirmation_window_hours', 72));
        }

        return null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast()
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)
            && ! $this->isPaid();
    }

    /**
     * @return list<string>
     */
    public function allowedTransitions(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canTransitionTo(string $to): bool
    {
        if (! in_array($to, $this->allowedTransitions(), true)) {
            return false;
        }

        // Tidak boleh "confirmed" tanpa pembayaran sah (COD dianggap sah saat diserahkan,
        // sehingga aturan ini hanya berlaku untuk QRIS/transfer).
        if ($to === self::STATUS_CONFIRMED && ! $this->isPaid()) {
            return false;
        }

        return true;
    }

    public function isPaid(): bool
    {
        if ($this->payment_method === 'cod') {
            return true;
        }

        return $this->payment_status === 'verified';
    }

    /**
     * Ubah status lewat jalur resmi: validasi transisi, jurnal riwayat,
     * penyesuaian stok/sold_count, timestamp, dan notifikasi.
     */
    public function transitionTo(string $to, ?User $actor = null, ?string $reason = null): bool
    {
        if (! $this->canTransitionTo($to)) {
            return false;
        }

        $from = $this->status;

        DB::transaction(function () use ($from, $to, $actor, $reason) {
            $attributes = ['status' => $to];

            if ($to === self::STATUS_CONFIRMED) {
                $attributes['confirmed_at'] = $this->confirmed_at ?? now();
            }

            if ($to === self::STATUS_CANCELLED) {
                $attributes['cancelled_at'] = now();
            }

            $this->update($attributes);

            if ($to === self::STATUS_CANCELLED) {
                $this->restoreStock();
            }

            if ($to === self::STATUS_DELIVERED) {
                $this->adjustSoldCount(1);
            }

            if ($from === self::STATUS_DELIVERED && $to !== self::STATUS_DELIVERED) {
                $this->adjustSoldCount(-1);
            }

            $this->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'actor_id' => $actor?->id,
                'actor_role' => $actor?->getRoleNames()->first(),
                'reason' => $reason,
            ]);

            $this->notifyStatusChange($from, $to, $actor);
        });

        return true;
    }

    /**
     * Kembalikan stok untuk order yang dibatalkan.
     */
    public function restoreStock(): void
    {
        foreach ($this->items()->get() as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);

            if ($product) {
                $product->increment('stock', (int) $item->quantity);
            }
        }
    }

    protected function adjustSoldCount(int $direction): void
    {
        foreach ($this->items()->get() as $item) {
            if (! $item->product_id) {
                continue;
            }

            // Dihitung di PHP (bukan GREATEST/MAX) supaya portabel antar-DB,
            // dan dikunci agar aman dari balapan update.
            $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);

            if (! $product) {
                continue;
            }

            $product->sold_count = max(0, (int) $product->sold_count + ($direction * (int) $item->quantity));
            $product->save();
        }
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cod' => 'Bayar di Tempat',
            'qris' => 'QRIS',
            default => $this->payment_method,
        };
    }

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
            'expires_at' => now()->addHours((int) config('orders.payment_window_hours', 24)),
        ]);

        $this->notifyShopOwner('proof_uploaded');
    }

    public function verifyPayment(?User $actor = null): void
    {
        DB::transaction(function () use ($actor) {
            $this->update([
                'payment_status' => 'verified',
                'payment_verified_at' => now(),
                'payment_rejected_at' => null,
                'payment_rejection_note' => null,
            ]);

            if ($this->status === self::STATUS_PENDING) {
                // Setelah payment_status = verified, transisi ke confirmed sah.
                $this->transitionTo(self::STATUS_CONFIRMED, $actor, 'Pembayaran terverifikasi');
            }

            $this->notifyBuyer('payment_verified');
        });
    }

    public function rejectPayment(?string $note = null, ?User $actor = null): void
    {
        $this->update([
            'payment_status' => 'rejected',
            'payment_rejected_at' => now(),
            'payment_rejection_note' => $note,
            'expires_at' => now()->addHours((int) config('orders.payment_window_hours', 24)),
        ]);

        $this->notifyBuyer('payment_rejected', ['note' => $note]);
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
     * Pendapatan yang benar-benar masuk: QRIS terverifikasi, atau COD yang
     * sudah diserahkan. Jangan memakai `status` saja.
     */
    public function scopeRevenue($query)
    {
        return $query->where(function ($q) {
            $q->where('payment_status', 'verified')
                ->orWhere(function ($q) {
                    $q->where('payment_method', 'cod')
                        ->where('status', self::STATUS_DELIVERED);
                });
        });
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function notifyStatusChange(string $from, string $to, ?User $actor): void
    {
        // Pembeli diberi tahu setiap perubahan yang dilakukan pihak lain.
        if (! $actor || $actor->id !== $this->user_id) {
            $this->notifyBuyer('status_changed', [
                'from' => $from,
                'to' => $this->statusLabelFor($to),
            ]);
        }

        if (! $actor || $actor->id !== $this->shop?->user_id) {
            $this->notifyShopOwner('status_changed', [
                'from' => $from,
                'to' => $this->statusLabelFor($to),
            ]);
        }
    }

    public function notifyBuyer(string $event, array $extra = []): void
    {
        if ($buyer = $this->user) {
            $buyer->notify(new OrderNotification($this, $event, $extra));
        }
    }

    public function notifyShopOwner(string $event, array $extra = []): void
    {
        if ($owner = $this->shop?->user) {
            $owner->notify(new OrderNotification($this, $event, $extra));
        }
    }

    public static function statusLabelFor(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'delivered' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }
}