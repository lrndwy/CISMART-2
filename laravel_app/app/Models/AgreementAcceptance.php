<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Bukti persetujuan dokumen legal (mis. Perjanjian Penjual).
 *
 * Baris di sini bersifat bukti: dibuat sekali per (user, jenis dokumen, versi)
 * dan tidak pernah diperbarui. Versi baru di config/legal.php menghasilkan
 * baris baru, bukan menimpa baris lama.
 */
class AgreementAcceptance extends Model
{
    public const SELLER_AGREEMENT = 'seller_agreement';

    /** Riwayat persetujuan tidak di-update, jadi tidak ada kolom waktu model. */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'document_type',
        'version',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * Catat persetujuan user atas satu versi dokumen. Idempoten: submit berulang
     * pada versi yang sama tidak menambah baris kedua.
     */
    public static function record(User $user, string $documentType, string $version, Request $request): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $user->getKey(),
                'document_type' => $documentType,
                'version' => $version,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'accepted_at' => now(),
            ],
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}