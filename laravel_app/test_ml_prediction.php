<?php

/**
 * ML Prediction Test Script
 * Run: php test_ml_prediction.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MlPrediction;
use App\Services\MLService;

echo "=================================\n";
echo "ML Prediction End-to-End Test\n";
echo "=================================\n\n";

// Test 1: Create prediction in database
echo "[Test 1] Creating test prediction in database...\n";

try {
    $prediction = MlPrediction::create([
        'user_id' => 29, // Change to your user ID
        'shop_id' => null,
        'omzet' => 500000000,
        'aset' => 1000000000,
        'modal_kerja' => 300000000,
        'jumlah_investasi' => 800000000,
        'jumlah_tenaga_kerja' => 25,
        'bangunan_gedung' => 200000000,
        'mesin_peralatan' => 150000000,
        'mesin_peralatan_impor' => 50000000,
        'pembelian_pematangan_tanah' => 100000000,
        'lain_lain' => 50000000,
        'tki' => 0,
        'jenis_perusahaan' => 'PT',
        'risiko_proyek' => 'Rendah',
        'skala_usaha' => 'Menengah',
        'status_penanaman_modal' => 'PMDN',
        'kecamatan_usaha' => 'Cilacap Tengah',
        'kelurahan_usaha' => 'Cilacap',
        'kl_sektor_pembina' => 'Pangan',
        'judul_kbli' => 'Industri Makanan',
        'predicted_cluster' => 1,
        'confidence' => 0.92,
        'prediction_type' => 'single',
    ]);

    echo "✅ SUCCESS! Prediction created:\n";
    echo "   ID: {$prediction->id}\n";
    echo "   Cluster: {$prediction->cluster_label}\n";
    echo "   Confidence: {$prediction->confidence_percentage}\n";
    echo "   Omzet: Rp " . number_format($prediction->omzet, 0, ',', '.') . "\n";
    echo "\n";
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 2: Test ML Service connection
echo "[Test 2] Testing ML Service connection...\n";

try {
    $mlService = app(MLService::class);
    $health = $mlService->healthCheck();

    if ($health['status'] === 'healthy') {
        echo "✅ ML Service is ONLINE\n";
        echo "   Models loaded: " . ($health['data']['models_loaded'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "⚠️  ML Service is {$health['status']}\n";
        if (isset($health['error'])) {
            echo "   Error: {$health['error']}\n";
        }
    }
    echo "\n";
} catch (\Exception $e) {
    echo "❌ ML Service UNREACHABLE: " . $e->getMessage() . "\n\n";
}

// Test 3: Get model info (if trained)
echo "[Test 3] Getting ML model info...\n";

try {
    $mlService = app(MLService::class);
    $info = $mlService->getModelInfo();

    if ($info && isset($info['status']) && $info['status'] === 'trained') {
        echo "✅ Model is TRAINED\n";
        if (isset($info['model_info']['metrics'])) {
            $metrics = $info['model_info']['metrics'];
            if (isset($metrics['kmeans'])) {
                echo "   KMeans Silhouette: " . $metrics['kmeans']['silhouette_score'] . "\n";
            }
            if (isset($metrics['random_forest'])) {
                echo "   RF Accuracy: " . $metrics['random_forest']['accuracy'] . "\n";
            }
        }
    } else {
        echo "⚠️  Model NOT TRAINED yet\n";
        echo "   Run training first: curl -X POST http://localhost:5000/api/train -F \"file=@data.xlsx\"\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "⚠️  Cannot get model info: " . $e->getMessage() . "\n\n";
}

// Test 4: Test prediction with ML (if trained)
echo "[Test 4] Testing ML prediction...\n";

try {
    $mlService = app(MLService::class);

    $testData = [
        'omzet' => 750000000,
        'aset' => 1500000000,
        'modal_kerja' => 400000000,
        'jumlah_investasi' => 1000000000,
        'jumlah_tenaga_kerja' => 35,
        'bangunan_gedung' => 300000000,
        'mesin_peralatan' => 200000000,
        'mesin_peralatan_impor' => 100000000,
        'pembelian_pematangan_tanah' => 150000000,
        'lain_lain' => 75000000,
        'tki' => 0,
        'jenis_perusahaan' => 'PT',
        'risiko_proyek' => 'Rendah',
        'skala_usaha' => 'Menengah',
        'status_penanaman_modal' => 'PMDN',
        'kecamatan_usaha' => 'Cilacap Tengah',
        'kelurahan_usaha' => 'Cilacap',
        'kl_sektor_pembina' => 'Pangan',
        'judul_kbli' => 'Industri Makanan',
    ];

    $result = $mlService->predictSingle($testData);

    if ($result['success']) {
        $pred = $result['data']['prediction'];
        echo "✅ ML Prediction SUCCESS!\n";
        echo "   Predicted Cluster: {$pred['predicted_cluster']}\n";
        echo "   Confidence: " . number_format($pred['confidence'] * 100, 2) . "%\n";
        echo "   Probabilities:\n";
        foreach ($pred['probabilities'] as $cluster => $prob) {
            echo "     - Cluster {$cluster}: " . number_format($prob * 100, 2) . "%\n";
        }
    } else {
        echo "⚠️  ML Prediction FAILED\n";
        echo "   Error: {$result['error']}\n";
        echo "   Kemungkinan: Model belum di-training\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "⚠️  Cannot predict: " . $e->getMessage() . "\n\n";
}

// Test 5: Query predictions
echo "[Test 5] Querying predictions from database...\n";

try {
    $total = MlPrediction::count();
    echo "Total predictions: {$total}\n";

    if ($total > 0) {
        $latest = MlPrediction::latest()->first();
        echo "Latest prediction:\n";
        echo "   ID: {$latest->id}\n";
        echo "   Cluster: {$latest->cluster_label}\n";
        echo "   Confidence: {$latest->confidence_percentage}\n";
        echo "   Created: {$latest->created_at->format('d M Y H:i')}\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . "\n\n";
}

echo "=================================\n";
echo "Test Complete!\n";
echo "=================================\n";
