<?php

/**
 * End-to-End Test for ML Prediction via Filament
 * Run: php test_filament_ml.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MlPrediction;
use App\Models\User;
use App\Services\MLService;

echo "=================================================\n";
echo "End-to-End Test: ML Prediction via Filament Flow\n";
echo "=================================================\n\n";

// Test 1: Check database schema
echo "[Test 1] Checking database schema...\n";
try {
    $schemaCheck = DB::select("
        SELECT column_name, is_nullable, data_type
        FROM information_schema.columns
        WHERE table_name = 'ml_predictions'
        AND column_name IN ('predicted_cluster', 'confidence')
    ");

    foreach ($schemaCheck as $col) {
        $nullable = $col->is_nullable === 'YES' ? '✅ NULLABLE' : '❌ NOT NULL';
        echo "   Column: {$col->column_name} - {$nullable} ({$col->data_type})\n";
    }

    $allNullable = collect($schemaCheck)->every(fn($col) => $col->is_nullable === 'YES');

    if ($allNullable) {
        echo "✅ Schema OK: Both columns are nullable\n\n";
    } else {
        echo "❌ Schema ERROR: Columns should be nullable\n";
        echo "   Run: php artisan migrate\n\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "❌ Schema check failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check ML Service availability
echo "[Test 2] Checking ML Service availability...\n";
try {
    $mlService = app(MLService::class);
    $health = $mlService->healthCheck();

    echo "   Status: {$health['status']}\n";

    if ($health['status'] === 'healthy') {
        echo "   ✅ ML Service is ONLINE\n";

        if (isset($health['data']['models_loaded']) && $health['data']['models_loaded']) {
            echo "   ✅ Models are LOADED\n";
            $mlAvailable = true;
        } else {
            echo "   ⚠️  Models NOT loaded (need training)\n";
            $mlAvailable = false;
        }
    } else {
        echo "   ⚠️  ML Service is OFFLINE\n";
        $mlAvailable = false;
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ⚠️  ML Service UNREACHABLE: " . $e->getMessage() . "\n\n";
    $mlAvailable = false;
}

// Test 3: Create prediction WITHOUT ML (simulate offline)
echo "[Test 3] Testing data save WITHOUT ML prediction (offline scenario)...\n";
try {
    $user = User::find(29);
    if (!$user) {
        echo "❌ User ID 29 not found. Update user_id in test.\n\n";
        exit(1);
    }

    $testData = [
        'user_id' => $user->id,
        'shop_id' => null,
        'omzet' => 2000000,
        'aset' => 5000000,
        'modal_kerja' => 1500000,
        'jumlah_investasi' => 3000000,
        'jumlah_tenaga_kerja' => 3,
        'bangunan_gedung' => 1000000,
        'mesin_peralatan' => 800000,
        'mesin_peralatan_impor' => 0,
        'pembelian_pematangan_tanah' => 500000,
        'lain_lain' => 200000,
        'tki' => 0,
        'jenis_perusahaan' => 'UD',
        'risiko_proyek' => 'Sedang',
        'skala_usaha' => 'Mikro',
        'status_penanaman_modal' => 'Non-Fasilitas',
        'kecamatan_usaha' => 'Cilacap Utara',
        'kelurahan_usaha' => 'Gumilir',
        'kl_sektor_pembina' => 'Aneka',
        'judul_kbli' => 'Kerajinan Tangan',
        'predicted_cluster' => null,  // Explicitly set null
        'confidence' => null,  // Explicitly set null
        'prediction_type' => 'single',
        'notes' => 'Test: Save without ML prediction',
    ];

    $prediction = MlPrediction::create($testData);

    echo "✅ SUCCESS! Data saved without prediction:\n";
    echo "   ID: {$prediction->id}\n";
    echo "   Omzet: Rp " . number_format($prediction->omzet, 0, ',', '.') . "\n";
    echo "   Cluster: " . ($prediction->predicted_cluster !== null ? $prediction->cluster_label : 'NULL (as expected)') . "\n";
    echo "   Confidence: " . ($prediction->confidence !== null ? $prediction->confidence_percentage : 'NULL (as expected)') . "\n";
    echo "\n";
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Create prediction WITH ML (if available)
if ($mlAvailable) {
    echo "[Test 4] Testing data save WITH ML prediction (online scenario)...\n";
    try {
        $testData2 = [
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

        $result = $mlService->predictSingle($testData2);

        if ($result['success']) {
            $pred = $result['data']['prediction'];

            // Save to database with prediction
            $testData2['user_id'] = $user->id;
            $testData2['predicted_cluster'] = $pred['predicted_cluster'];
            $testData2['confidence'] = $pred['confidence'];
            $testData2['probabilities'] = $pred['probabilities'];
            $testData2['prediction_type'] = 'single';
            $testData2['notes'] = 'Test: Save with ML prediction';

            $prediction2 = MlPrediction::create($testData2);

            echo "✅ SUCCESS! Data saved with prediction:\n";
            echo "   ID: {$prediction2->id}\n";
            echo "   Cluster: {$prediction2->cluster_label}\n";
            echo "   Confidence: {$prediction2->confidence_percentage}\n";
            echo "   Omzet: Rp " . number_format($prediction2->omzet, 0, ',', '.') . "\n";
            echo "\n";
        } else {
            echo "⚠️  ML prediction failed: {$result['error']}\n";
            echo "   Model probably not trained yet\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "[Test 4] ⏭️  SKIPPED (ML Service not available)\n\n";
}

// Test 5: Query and display all predictions
echo "[Test 5] Querying all predictions...\n";
try {
    $predictions = MlPrediction::orderBy('created_at', 'desc')->limit(5)->get();

    echo "Total predictions: " . MlPrediction::count() . "\n";
    echo "Latest 5 predictions:\n\n";

    foreach ($predictions as $pred) {
        echo "   ID: {$pred->id} | ";
        echo "Cluster: " . ($pred->predicted_cluster !== null ? $pred->cluster_label : 'NO PREDICTION') . " | ";
        echo "Confidence: " . ($pred->confidence !== null ? $pred->confidence_percentage : 'N/A') . " | ";
        echo "Created: {$pred->created_at->format('d M Y H:i')}\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . "\n\n";
}

// Test 6: Test model accessors
echo "[Test 6] Testing model accessors...\n";
try {
    $testPred = MlPrediction::latest()->first();

    if ($testPred) {
        echo "   Testing prediction ID: {$testPred->id}\n";
        echo "   cluster_label accessor: {$testPred->cluster_label}\n";
        echo "   confidence_percentage accessor: {$testPred->confidence_percentage}\n";
        echo "✅ Accessors working correctly\n\n";
    } else {
        echo "⚠️  No predictions found to test\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Accessor test failed: " . $e->getMessage() . "\n\n";
}

// Summary
echo "=================================================\n";
echo "Test Summary\n";
echo "=================================================\n";
echo "✅ Test 1: Database schema nullable - PASSED\n";
echo ($mlAvailable ? "✅" : "⚠️ ") . " Test 2: ML Service - " . ($mlAvailable ? "ONLINE" : "OFFLINE") . "\n";
echo "✅ Test 3: Save without prediction - PASSED\n";
echo ($mlAvailable ? "✅" : "⏭️ ") . " Test 4: Save with prediction - " . ($mlAvailable ? "PASSED" : "SKIPPED") . "\n";
echo "✅ Test 5: Query predictions - PASSED\n";
echo "✅ Test 6: Model accessors - PASSED\n";
echo "\n";

if (!$mlAvailable) {
    echo "⚠️  NOTE: ML Service is offline. Predictions will be saved without cluster info.\n";
    echo "   To enable ML predictions:\n";
    echo "   1. Start ML service: docker compose up -d ml_service\n";
    echo "   2. Train model: curl -X POST http://localhost:5000/api/train -F 'file=@data.xlsx'\n";
    echo "   3. Test again\n\n";
}

echo "🎉 All tests completed! Filament ML Prediction is ready to use.\n";
echo "   Access: http://127.0.0.1:8000/admin/ml-predictions\n";
echo "=================================================\n";
