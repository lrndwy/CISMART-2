<?php

/**
 * Test Kolom Kategori Cluster Baru
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MlPrediction;

echo "======================================================\n";
echo "Test Kolom Kategori Cluster dengan Deskripsi Lengkap\n";
echo "======================================================\n\n";

// Get latest predictions
$predictions = MlPrediction::with(['shop', 'user'])
    ->whereNotNull('predicted_cluster')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($predictions->isEmpty()) {
    echo "❌ Tidak ada prediksi dengan cluster\n";
    exit;
}

echo "✅ Ditemukan {$predictions->count()} prediksi\n\n";

foreach ($predictions as $pred) {
    echo "========================================\n";
    echo "ID: {$pred->id}\n";
    echo "Toko: " . ($pred->shop->name ?? 'N/A') . "\n";
    echo "User: {$pred->user->name}\n";
    echo "Created: {$pred->created_at->format('d M Y H:i')}\n";
    echo "----------------------------------------\n";

    // Data UMKM
    echo "📊 Data UMKM:\n";
    echo "   Omzet: Rp " . number_format($pred->omzet, 0, ',', '.') . "\n";
    echo "   Aset: Rp " . number_format($pred->aset, 0, ',', '.') . "\n";
    echo "   Tenaga Kerja: {$pred->jumlah_tenaga_kerja} orang\n";
    echo "   Skala Usaha: {$pred->skala_usaha}\n";
    echo "\n";

    // Hasil Prediksi
    echo "🎯 Hasil Prediksi:\n";
    echo "   Cluster: {$pred->predicted_cluster}\n";
    echo "   Label: {$pred->cluster_label}\n";
    echo "   Confidence: {$pred->confidence_percentage}\n";
    echo "\n";

    // **KOLOM BARU**: Kategori Cluster dengan Deskripsi
    echo "📋 Kategori Cluster (BARU):\n";
    echo "   {$pred->cluster_description}\n";
    echo "\n";
}

echo "========================================\n";
echo "✅ Test Selesai!\n";
echo "\n";
echo "📝 Kolom 'Kategori Cluster' sudah ditambahkan dengan deskripsi:\n";
echo "   - Cluster 0: Omzet < 2.5M, Aset < 50M, Pekerja < 20\n";
echo "   - Cluster 1: Omzet 2.5M-50M, Aset 50M-500M, Pekerja 20-99\n";
echo "   - Cluster 2: Omzet > 50M, Aset > 500M, Pekerja 100+\n";
echo "\n";
echo "🌐 Test di browser:\n";
echo "   http://127.0.0.1:8000/admin/ml-predictions\n";
echo "   Kolom 'Kategori Cluster' akan muncul di table\n";
echo "======================================================\n";
