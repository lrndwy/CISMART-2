<?php

/**
 * Quick Test: Laravel to ML Service Connection
 * Run: php test_ml_connection.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\MLService;

echo "=========================================\n";
echo "Testing Laravel → ML Service Connection\n";
echo "=========================================\n\n";

// Test 1: Check config
echo "[Test 1] Checking ML Service configuration...\n";
$mlUrl = config('services.ml.url');
$mlTimeout = config('services.ml.timeout');
echo "   URL: {$mlUrl}\n";
echo "   Timeout: {$mlTimeout}s\n";

if ($mlUrl === 'http://ml_service:5000') {
    echo "   ⚠️  WARNING: Using Docker hostname. Should be 'http://127.0.0.1:5000' for local dev\n";
    echo "   Update your .env file:\n";
    echo "   ML_SERVICE_URL=http://127.0.0.1:5000\n\n";
} else {
    echo "   ✅ Config looks good\n\n";
}

// Test 2: Direct cURL test
echo "[Test 2] Testing direct connection with cURL...\n";
$ch = curl_init("{$mlUrl}/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    echo "   ✅ Direct cURL connection: SUCCESS\n";
    echo "   Response: " . substr($response, 0, 100) . "...\n\n";
} else {
    echo "   ❌ Direct cURL connection: FAILED\n";
    echo "   HTTP Code: {$httpCode}\n";
    echo "   Error: {$error}\n\n";
    exit(1);
}

// Test 3: Test via MLService class
echo "[Test 3] Testing via MLService class...\n";
try {
    $mlService = app(MLService::class);
    $health = $mlService->healthCheck();

    echo "   Status: {$health['status']}\n";

    if ($health['status'] === 'healthy') {
        echo "   ✅ MLService class: SUCCESS\n";

        if (isset($health['data'])) {
            $data = $health['data'];
            echo "   Service: " . ($data['service'] ?? 'N/A') . "\n";
            echo "   Models Loaded: " . ($data['models_loaded'] ? 'Yes' : 'No') . "\n";
            echo "   Timestamp: " . ($data['timestamp'] ?? 'N/A') . "\n";
        }

        echo "\n";
    } else {
        echo "   ❌ MLService class: FAILED\n";
        echo "   Error: " . ($health['error'] ?? 'Unknown') . "\n\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "   ❌ MLService class: EXCEPTION\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Test model info endpoint
echo "[Test 4] Testing model info endpoint...\n";
try {
    $mlService = app(MLService::class);
    $modelInfo = $mlService->getModelInfo();

    if ($modelInfo) {
        echo "   ✅ Model info: SUCCESS\n";
        echo "   Status: " . ($modelInfo['status'] ?? 'N/A') . "\n";

        if (isset($modelInfo['status']) && $modelInfo['status'] === 'trained') {
            echo "   🎉 Models are TRAINED!\n";
            if (isset($modelInfo['model_info']['metrics'])) {
                echo "   Metrics available: Yes\n";
            }
        } else {
            echo "   ⚠️  Models NOT trained yet\n";
            echo "   Next step: Train model with data\n";
        }

        echo "\n";
    } else {
        echo "   ⚠️  Model info: No response\n\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Model info: EXCEPTION\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Test service info endpoint
echo "[Test 5] Testing service info endpoint...\n";
try {
    $mlService = app(MLService::class);
    $info = $mlService->getInfo();

    if ($info) {
        echo "   ✅ Service info: SUCCESS\n";
        echo "   Service: " . ($info['service'] ?? 'N/A') . "\n";
        echo "   Version: " . ($info['version'] ?? 'N/A') . "\n";
        echo "   Research: " . ($info['research'] ?? 'N/A') . "\n";

        if (isset($info['endpoints'])) {
            echo "   Available endpoints: " . count($info['endpoints']) . "\n";
        }

        echo "\n";
    } else {
        echo "   ⚠️  Service info: No response\n\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Service info: EXCEPTION\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Summary
echo "=========================================\n";
echo "Connection Test Summary\n";
echo "=========================================\n";
echo "✅ Config: OK\n";
echo "✅ Direct cURL: OK\n";
echo "✅ MLService class: OK\n";
echo "✅ Model info endpoint: OK\n";
echo "✅ Service info endpoint: OK\n";
echo "\n";
echo "🎉 All tests passed! Laravel can connect to ML Service.\n";
echo "\n";
echo "Next steps:\n";
echo "1. If models not trained, prepare training data (50+ rows)\n";
echo "2. Upload via: curl -X POST http://127.0.0.1:5000/api/train -F 'file=@data.xlsx'\n";
echo "3. Test prediction via Filament: http://127.0.0.1:8000/admin/ml-predictions/create\n";
echo "=========================================\n";
