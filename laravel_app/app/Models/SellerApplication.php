<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_description',
        'business_type',
        'business_address',
        'business_phone',
        'business_email',
        'business_documents',
        'nib_document_path',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'business_documents' => 'array',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Toko hanya boleh aktif setelah permohonan penjual disetujui, dan toko
        // dibuat otomatis dari data permohonan supaya penjual tidak perlu
        // mengisi ulang nama/deskripsi/kontak/alamat di form Tambah Toko.
        static::updated(function (SellerApplication $application): void {
            if ($application->wasChanged('status') && $application->status === 'approved') {
                $application->provisionShop();
            }
        });
    }

    /**
     * Setujui permohonan. Status disimpan lewat update() supaya hook booted()
     * di atas yang membuat/mengaktifkan toko tetap menjadi satu-satunya jalur.
     */
    public function approve(?User $approver = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver?->id,
        ]);
    }

    /**
     * Buat toko dari data permohonan (atau aktifkan toko yang sudah dibuat
     * penjual tanpa menimpa isian mereka). Idempoten.
     */
    public function provisionShop(): ?Shop
    {
        $user = $this->user;

        if (! $user) {
            return null;
        }

        if (! $user->hasRole('seller')) {
            $user->assignRole('seller');
        }

        return DB::transaction(function () use ($user): Shop {
            $shop = $user->shops()->first();

            if ($shop) {
                // Toko sudah dibuat manual sebelum disetujui: jangan timpa
                // data yang sudah diisi penjual, hanya lengkapi yang kosong.
                $shop->fill(array_filter([
                    'description' => $shop->description ?: $this->business_description,
                    'phone' => $shop->phone ?: $this->business_phone,
                    'email' => $shop->email ?: $this->business_email,
                    'address' => $shop->address ?: $this->business_address,
                ], fn ($value): bool => filled($value)));

                $shop->status = 'approved';
                $shop->save();

                return $shop;
            }

            return Shop::create([
                'user_id' => $user->id,
                'name' => $this->business_name,
                'slug' => Shop::uniqueSlug($this->business_name),
                'description' => $this->business_description,
                'phone' => $this->business_phone,
                'email' => $this->business_email,
                'address' => $this->business_address,
                'status' => 'approved',
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Sudah ada file Izin Usaha (NIB) tersimpan? Diperiksa form supaya pengajuan
     * ulang tidak meminta unggah ulang.
     */
    public function hasNib(): bool
    {
        return filled($this->nib_document_path);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
