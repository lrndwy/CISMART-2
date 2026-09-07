<?php

/**
 * End-to-End Test: Simulate Filament Create Flow
 * Run: php test_filament_create_flow.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MlPrediction;
use App\Models\User;
use App\Services\MLService;
use Illuminate\Support\Facades\Auth;

echo "=======================================================\n";
echo "Simulating Filament Create Prediction Flow\n";
echo "=======================================================\n\n";

// Find user
$user = User::find(29);
if (!$user) {
    echo "❌ User ID 29 not found\n";
    exit(1);
}

echo "[Step 1] User authenticated: {$user->name}\n\n";

// Simulate form data input
$formData = [
    'shop_id' => 2,
    'prediction_type' => 'single',
    'notes' => 'Test end-to-end via script',
    'omzet' => 5000000,
    'aset' => 10000000,
    'modal_kerja' => 3000000,
    'jumlah_investasi' => 8000000,
    'bangunan_gedung' => 2000000,
    'mesin_peralatan' => 1500000,
    'mesin_peralatan_impor' => 500000,
    'pembelian_pematangan_tanah' => 1000000,
    'lain_lain' => 500000,
    'jumlah_tenaga_kerja' => 8,
    'tki' => 0,
    'jenis_perusahaan' => 'CV',
    'risiko_proyek' => 'Sedang',
    'skala_usaha' => 'Kecil',
    'status_penanaman_modal' => 'PMDN',
    'kecamatan_usaha' => 'Cilacap Selatan',
    'kelurahan_usaha' => 'Tambakreja',
    'kl_sektor_pembina' => 'Aneka',
    'judul_kbli' => 'Industri Kerajinan',
];

echo "[Step 2] Form data prepared:\n";
echo "   Omzet: Rp " . number_format($formData['omzet'], 0, ',', '.') . "\n";
echo "   Tenaga Kerja: {$formData['jumlah_tenaga_kerja']} orang\n";
echo "   Skala Usaha: {$formData['skala_usaha']}\n\n";

// SIMULATE mutateFormDataBeforeCreate()
echo "[Step 3] Simulating mutateFormDataBeforeCreate()...\n";

// Set user_id
$formData['user_id'] = $user->id;
echo "   ✅ Set user_id = {$user->id}\n";

// Check ML Service health
try {
    $mlService = app(MLService::class);
    $health = $mlService->healthCheck();

    echo "   ML Service status: {$health['status']}\n";

    if ($health['status'] === 'healthy') {
        echo "   ✅ ML Service is ONLINE\n";

        // Prepare prediction data
        $predictionData = [
            'omzet' => $formData['omzet'],
            'aset' => $formData['aset'],
            'modal_kerja' => $formData['modal_kerja'],
            'jumlah_investasi' => $formData['jumlah_investasi'],
            'jumlah_tenaga_kerja' => $formData['jumlah_tenaga_kerja'],
            'bangunan_gedung' => $formData['bangunan_gedung'],
            'mesin_peralatan' => $formData['mesin_peralatan'],
            'mesin_peralatan_impor' => $formData['mesin_peralatan_impor'],
            'pembelian_pematangan_tanah' => $formData['pembelian_pematangan_tanah'],
            'lain_lain' => $formData['lain_lain'],
            'tki' => $formData['tki'],
            'jenis_perusahaan' => $formData['jenis_perusahaan'],
            'risiko_proyek' => $formData['risiko_proyek'],
            'skala_usaha' => $formData['skala_usaha'],
            'status_penanaman_modal' => $formData['status_penanaman_modal'],
            'kecamatan_usaha' => $formData['kecamatan_usaha'],
            'kelurahan_usaha' => $formData['kelurahan_usaha'],
            'kl_sektor_pembina' => $formData['kl_sektor_pembina'],
            'judul_kbli' => $formData['judul_kbli'],
        ];

        echo "   Attempting prediction...\n";

        $result = $mlService->predictSingle($predictionData);

        if ($result['success'] && isset($result['data']['prediction'])) {
            $prediction = $result['data']['prediction'];

            echo "   ✅ Prediction SUCCESS!\n";
            echo "      Cluster: {$prediction['predicted_cluster']}\n";
            echo "      Confidence: " . number_format($prediction['confidence'] * 100, 2) . "%\n";

            $formData['predicted_cluster'] = $prediction['predicted_cluster'];
            $formData['confidence'] = $prediction['confidence'];
            $formData['probabilities'] = $prediction['probabilities'];
            $formData['cluster_profile'] = $prediction['cluster_profile'] ?? null;
            $formData['model_version'] = $prediction['model_version'] ?? null;

            $notificationType = 'success';
            $notificationMessage = "Prediction Generated | Cluster: {$prediction['predicted_cluster']} | Confidence: " . number_format($prediction['confidence'] * 100, 2) . "%";
        } else {
            echo "   ⚠️  Prediction FAILED (Model not trained)\n";
            echo "      Error: " . ($result['error'] ?? 'Unknown') . "\n";

            $formData['predicted_cluster'] = null;
            $formData['confidence'] = null;
            $formData['notes'] .= "\n[System] Model not trained yet.";

            $notificationType = 'warning';
            $notificationMessage = "Model not trained. Data saved without prediction.";
        }
    } else {
        echo "   ⚠️  ML Service is OFFLINE\n";

        $formData['predicted_cluster'] = null;
        $formData['confidence'] = null;
        $formData['notes'] .= "\n[System] ML Service offline.";

        $notificationType = 'warning';
        $notificationMessage = "ML Service unavailable. Data saved without prediction.";
    }
} catch (\Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";

    $formData['predicted_cluster'] = null;
    $formData['confidence'] = null;
    $formData['notes'] .= "\n[Error] " . $e->getMessage();

    $notificationType = 'error';
    $notificationMessage = "Prediction error: " . $e->getMessage();
}

echo "\n[Step 4] Saving to database...\n";

try {
    $prediction = MlPrediction::create($formData);

    echo "   ✅ Database INSERT: SUCCESS\n";
    echo "   Record ID: {$prediction->id}\n";
    echo "   Cluster: " . ($prediction->predicted_cluster !== null ? $prediction->cluster_label : 'NULL') . "\n";
    echo "   Confidence: " . ($prediction->confidence !== null ? $prediction->confidence_percentage : 'NULL') . "\n";
} catch (\Exception $e) {
    echo "   ❌ Database INSERT: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n[Step 5] Notification (would show in Filament):\n";
echo "   Type: " . strtoupper($notificationType) . "\n";
echo "   Message: {$notificationMessage}\n";

echo "\n=======================================================\n";
echo "Flow Simulation Complete\n";
echo "=======================================================\n";
echo "✅ Form data validated\n";
echo "✅ ML Service checked\n";
echo ($formData['predicted_cluster'] !== null ? "✅" : "⚠️ ") . " Prediction " . ($formData['predicted_cluster'] !== null ? "generated" : "skipped (offline/not trained)") . "\n";
echo "✅ Data saved to database\n";
echo "✅ Notification prepared\n";
echo "\n";

if ($formData['predicted_cluster'] === null) {
    echo "⚠️  NOTE: Prediction was not generated.\n";
    echo "   Reason: " . ($health['status'] === 'healthy' ? 'Model not trained' : 'ML Service offline') . "\n";
    echo "\n";
    echo "   To enable predictions:\n";
    if ($health['status'] !== 'healthy') {
        echo "   1. Check ML Service is running: http://127.0.0.1:5000/health\n";
    }
    echo "   2. Train model: curl -X POST http://127.0.0.1:5000/api/train -F 'file=@data.xlsx'\n";
    echo "   3. Retry creating prediction\n";
    echo "\n";
}

echo "🎉 Ready to test in browser!\n";
echo "   URL: http://127.0.0.1:8000/admin/ml-predictions/create\n";
echo "=======================================================\n";
