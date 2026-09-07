<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MlPrediction extends Model
{
    protected $fillable = [
        'shop_id',
        'user_id',
        'omzet',
        'aset',
        'modal_kerja',
        'jumlah_investasi',
        'jumlah_tenaga_kerja',
        'bangunan_gedung',
        'mesin_peralatan',
        'mesin_peralatan_impor',
        'pembelian_pematangan_tanah',
        'lain_lain',
        'tki',
        'jenis_perusahaan',
        'risiko_proyek',
        'skala_usaha',
        'status_penanaman_modal',
        'kecamatan_usaha',
        'kelurahan_usaha',
        'kl_sektor_pembina',
        'judul_kbli',
        'predicted_cluster',
        'confidence',
        'probabilities',
        'cluster_profile',
        'prediction_type',
        'model_version',
        'notes',
    ];

    protected $casts = [
        'probabilities' => 'array',
        'cluster_profile' => 'array',
        'omzet' => 'decimal:2',
        'aset' => 'decimal:2',
        'modal_kerja' => 'decimal:2',
        'jumlah_investasi' => 'decimal:2',
        'confidence' => 'decimal:4',
    ];

    /**
     * Get the shop that owns the prediction
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the user who created the prediction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get cluster label as human-readable text
     */
    public function getClusterLabelAttribute(): string
    {
        return match ($this->predicted_cluster) {
            0 => 'Cluster A - UMKM Skala Kecil',
            1 => 'Cluster B - UMKM Skala Menengah',
            2 => 'Cluster C - UMKM Skala Besar',
            default => 'Unknown Cluster'
        };
    }

    /**
     * Get detailed cluster description with characteristics
     */
    public function getClusterDescriptionAttribute(): string
    {
        return match ($this->predicted_cluster) {
            0 => 'UMKM Mikro/Kecil: Omzet < 2.5M, Aset < 50M, Pekerja < 20 orang. Fokus pasar lokal, modal terbatas.',
            1 => 'UMKM Menengah: Omzet 2.5M-50M, Aset 50M-500M, Pekerja 20-99 orang. Ekspansi regional, modal terstruktur.',
            2 => 'UMKM Besar: Omzet > 50M, Aset > 500M, Pekerja 100+ orang. Jangkauan nasional, investasi tinggi.',
            default => 'Belum ada prediksi cluster'
        };
    }

    /**
     * Get confidence level as percentage
     */
    public function getConfidencePercentageAttribute(): string
    {
        return number_format($this->confidence * 100, 2) . '%';
    }

    /**
     * Scope: Filter by shop
     */
    public function scopeByShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * Scope: Filter by cluster
     */
    public function scopeByCluster($query, $clusterId)
    {
        return $query->where('predicted_cluster', $clusterId);
    }

    /**
     * Scope: Recent predictions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
