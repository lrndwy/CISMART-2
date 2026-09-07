# 🧪 ML Prediction - End-to-End Testing Guide

## ⚠️ Note

Resource Filament untuk ML Prediction belum dibuat karena kompleks

itas Filament v4 Schema API. Testing dilakukan via **Tinker** dan **API langsung**.

---

## 🎯 Test Scenarios

### **Scenario 1: Test ML Service (Direct API)**

#### 1.1 Health Check

```bash
curl http://localhost:5000/health

# Expected Output:
# {
#   "status": "healthy",
#   "service": "CISMART ML Service",
#   "models_loaded": false
# }
```

#### 1.2 Get Service Info

```bash
curl http://localhost:5000/api/info

# Expected: Full API documentation
```

---

### **Scenario 2: Test dari Laravel (via Tinker)**

#### 2.1 Test MLService Connection

```bash
php artisan tinker
```

```php
use App\Services\MLService;

$ml = app(MLService::class);
$health = $ml->healthCheck();
print_r($health);

// Expected Output:
// Array
// (
//     [status] => healthy
//     [data] => Array...
// )
```

#### 2.2 Create Test Prediction (Without ML - Database Only)

```php
use App\Models\MlPrediction;

$prediction = MlPrediction::create([
    'user_id' => 29, // Ganti dengan user_id yang login
    'shop_id' => null, // Optional
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
    'predicted_cluster' => 0, // Temporary
    'confidence' => 0, // Temporary
    'prediction_type' => 'single',
]);

echo "Prediction created with ID: {$prediction->id}\n";
```

✅ **Expected**: Record tersimpan ke database tanpa error

#### 2.3 Test dengan ML Prediction (Requires ML Service Trained)

```php
use App\Services\MLService;
use App\Models\MlPrediction;

$ml = app(MLService::class);

// Prepare data
$data = [
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
];

// Call ML service
$result = $ml->predictSingle($data);

if ($result['success']) {
    $pred = $result['data']['prediction'];

    // Save to database
    $prediction = MlPrediction::create([
        'user_id' => auth()->id() ?? 29,
        ...$data,
        'predicted_cluster' => $pred['predicted_cluster'],
        'confidence' => $pred['confidence'],
        'probabilities' => $pred['probabilities'],
        'cluster_profile' => $pred['cluster_profile'],
        'prediction_type' => 'single',
    ]);

    echo "✅ Success!\n";
    echo "Cluster: {$prediction->cluster_label}\n";
    echo "Confidence: {$prediction->confidence_percentage}\n";
    print_r($pred);
} else {
    echo "❌ Error: {$result['error']}\n";
    echo "Kemungkinan: Model belum di-training\n";
}
```

✅ **Expected (if ML service trained)**: Prediction dengan confidence score
⚠️ **Expected (if not trained)**: Error "Models not trained yet"

---

### **Scenario 3: View Predictions**

#### 3.1 Get All Predictions

```php
use App\Models\MlPrediction;

$predictions = MlPrediction::with('shop', 'user')->latest()->take(10)->get();

foreach ($predictions as $p) {
    echo "ID: {$p->id}\n";
    echo "Shop: " . ($p->shop->name ?? 'N/A') . "\n";
    echo "Cluster: {$p->cluster_label}\n";
    echo "Confidence: {$p->confidence_percentage}\n";
    echo "Omzet: Rp " . number_format($p->omzet, 0, ',', '.') . "\n";
    echo "---\n";
}
```

#### 3.2 Filter by Cluster

```php
$cluster_b = MlPrediction::where('predicted_cluster', 1)->get();
echo "Total UMKM di Cluster B (Menengah): " . $cluster_b->count() . "\n";
```

#### 3.3 Recent Predictions (Last 7 days)

```php
$recent = MlPrediction::recent(7)->get();
echo "Prediksi 7 hari terakhir: " . $recent->count() . "\n";
```

---

### **Scenario 4: Training ML Model (First Time)**

⚠️ **Requires**: File Excel/CSV dengan data training (minimal 50 rows)

#### 4.1 Via API

```bash
curl -X POST http://localhost:5000/api/train \
  -F "file=@path/to/data_umkm_training.xlsx"

# Tunggu ~2-5 menit
# Expected Output:
# {
#   "status": "success",
#   "result": {
#     "metrics": {
#       "kmeans": {
#         "silhouette_score": 0.9774,
#         "n_clusters": 3
#       },
#       "random_forest": {
#         "accuracy": 0.98
#       }
#     }
#   }
# }
```

#### 4.2 Via Laravel

```php
use App\Services\MLService;

$ml = app(MLService::class);

// Upload file training
$filePath = storage_path('app/data_training.xlsx');

$result = $ml->trainModel($filePath);

if ($result['success']) {
    echo "✅ Training berhasil!\n";
    print_r($result['data']);
} else {
    echo "❌ Training gagal: {$result['error']}\n";
}
```

#### 4.3 Verify Training

```php
$ml = app(MLService::class);
$info = $ml->getModelInfo();

if ($info && $info['status'] === 'trained') {
    echo "✅ Model sudah trained\n";
    echo "Silhouette Score: " . $info['model_info']['metrics']['kmeans']['silhouette_score'] . "\n";
    echo "RF Accuracy: " . $info['model_info']['metrics']['random_forest']['accuracy'] . "\n";
} else {
    echo "⚠️ Model belum trained\n";
}
```

---

## ✅ Test Checklist

### Database Tests

- [x] Migration `create_ml_predictions_table` berhasil
- [ ] Insert manual record ke `ml_predictions` (via Tinker)
- [ ] Query predictions dengan relationships (shop, user)
- [ ] Test model scopes (byCluster, recent, byShop)
- [ ] Test accessors (cluster_label, confidence_percentage)

### ML Service Tests

- [ ] ML service health check response
- [ ] MLService class dapat connect ke ML API
- [ ] Predict single UMKM (setelah training)
- [ ] Handle error ketika model belum trained
- [ ] Predict batch UMKMs

### Integration Tests

- [ ] Create prediction via Tinker → Save to database
- [ ] Create prediction via Tinker → Call ML API → Save with results
- [ ] View predictions in database
- [ ] Filter predictions by cluster
- [ ] Check seller can only see their shop's predictions

### End-to-End (Requires ML Service Running)

- [ ] Start ML service container
- [ ] Upload training data
- [ ] Wait for training complete
- [ ] Create new prediction
- [ ] Verify cluster result makes sense
- [ ] Check confidence score > 70%

---

## 🐛 Common Issues & Solutions

### Issue 1: "Models not trained yet"

**Solution**: Train model first via `/api/train` endpoint

### Issue 2: "ML Service unreachable"

**Solution**:

```bash
docker compose ps
docker compose logs ml_service
docker compose restart ml_service
```

### Issue 3: "user_id null constraint violation"

**Solution**: Sudah diperbaiki - `user_id` auto-filled via `mutateFormDataBeforeCreate`

### Issue 4: Training gagal "Insufficient data"

**Solution**: Pastikan file training minimal 50 rows dengan kolom lengkap

---

## 📊 Expected Results

### Good Prediction Example:

```
Cluster: Cluster B - UMKM Skala Menengah
Confidence: 92.45%
Probabilities:
  - Cluster A: 5%
  - Cluster B: 92%
  - Cluster C: 3%
```

### Warning - Low Confidence:

```
Cluster: Cluster A - UMKM Skala Kecil
Confidence: 65.23%
⚠️ Warning: Confidence rendah, UMKM mungkin di perbatasan cluster
```

---

## 🎓 Manual Testing Steps

1. **Test Database** ✅

   ```bash
   php artisan tinker
   >>> App\Models\MlPrediction::create([...]); # See scenario 2.2
   ```

2. **Test ML Service** (jika ada training data)

   ```bash
   # Start service
   docker compose up -d ml_service

   # Upload training
   curl -X POST http://localhost:5000/api/train -F "file=@data.xlsx"

   # Test prediction
   php artisan tinker
   >>> # See scenario 2.3
   ```

3. **View Results**
   ```bash
   php artisan tinker
   >>> App\Models\MlPrediction::latest()->take(5)->get();
   ```

---

**Status**: ✅ Database & Model siap | ⏳ ML Service perlu training data | ⚠️ Filament UI manual dibuat nanti
