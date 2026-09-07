# CISMART ML Service

## Intelligent Micro Enterprise Growth Forecasting and Stratification System

**Penelitian PDP Dosen Pemula - 166PL43AL.04 2025**

Sistem ML untuk prediksi dan klasifikasi UMKM Pangan berbasis Machine Learning menggunakan clustering (KMeans) dan classification (Random Forest).

---

## 📊 Fitur Utama

1. **Clustering UMKM** - Stratifikasi UMKM ke dalam 3 cluster berdasarkan karakteristik bisnis
2. **Growth Forecasting** - Prediksi cluster untuk UMKM baru menggunakan Random Forest
3. **Batch Prediction** - Prediksi massal untuk banyak UMKM sekaligus
4. **Model Training** - Training ulang model dengan data baru

---

## 🏗️ Arsitektur

```
cismart-app/
├── ml_service/                 # Python Flask ML Service
│   ├── app.py                 # Flask API endpoints
│   ├── ml_engine.py           # ML training & prediction logic
│   ├── validators.py          # Input validation
│   ├── requirements.txt       # Python dependencies
│   ├── Dockerfile            # Docker container config
│   └── .dockerignore
├── laravel_app/
│   ├── app/
│   │   ├── Services/
│   │   │   └── MLService.php  # Laravel service untuk ML API
│   │   ├── Models/
│   │   │   └── MlPrediction.php
│   │   └── Filament/Resources/
│   │       └── MlForecastingResource.php
│   └── database/migrations/
│       └── 2025_11_21_*_create_ml_predictions_table.php
└── docker-compose.yml         # Orchestration

```

---

## 🚀 Cara Penggunaan

### 1. **Setup & Deploy**

```bash
# Build dan jalankan semua services
docker compose up -d --build

# Cek health ML service
curl http://localhost:5000/health

# Output:
# {
#   "status": "healthy",
#   "service": "CISMART ML Service",
#   "models_loaded": false
# }
```

### 2. **Training Model** (First Time)

**Via Laravel Filament Admin:**

1. Login ke admin panel: `http://localhost:8000/admin`
2. Navigasi ke **Riset & Analisis → Forecasting UMKM**
3. Klik **Train Models**
4. Upload file Excel/CSV dengan data training UMKM
5. Tunggu proses training selesai (~2-5 menit)

**Via API:**

```bash
curl -X POST http://localhost:5000/api/train \
  -F "file=@data_umkm.xlsx"
```

**Format Data Training:**

| Kolom               | Tipe    | Wajib | Contoh     |
| ------------------- | ------- | ----- | ---------- |
| omzet               | numeric | ✅    | 500000000  |
| aset                | numeric | ✅    | 1000000000 |
| modal_kerja         | numeric | ✅    | 300000000  |
| jumlah_investasi    | numeric | ✅    | 800000000  |
| jumlah_tenaga_kerja | numeric | ✅    | 25         |
| bangunan_gedung     | numeric | ⚠️    | 200000000  |
| mesin_peralatan     | numeric | ⚠️    | 150000000  |
| jenis_perusahaan    | string  | ⚠️    | PT         |
| skala_usaha         | string  | ⚠️    | Menengah   |
| ...                 | ...     | ...   | ...        |

### 3. **Prediksi Single UMKM**

**Via Filament Admin:**

1. Klik **Create** di halaman Forecasting UMKM
2. Pilih UMKM/Toko dari dropdown
3. Isi data finansial dan karakteristik usaha
4. Klik **Create** untuk mendapatkan prediksi cluster

**Via API:**

```bash
curl -X POST http://localhost:5000/api/predict \
  -H "Content-Type: application/json" \
  -d '{
    "omzet": 500000000,
    "aset": 1000000000,
    "modal_kerja": 300000000,
    "jumlah_investasi": 800000000,
    "jumlah_tenaga_kerja": 25,
    "jenis_perusahaan": "PT",
    "skala_usaha": "Menengah"
  }'
```

**Response:**

```json
{
  "status": "success",
  "prediction": {
    "predicted_cluster": 1,
    "confidence": 0.9245,
    "probabilities": {
      "0": 0.05,
      "1": 0.92,
      "2": 0.03
    },
    "cluster_profile": {
      "cluster_id": 1,
      "size": 1500,
      "percentage": 42.3,
      "numeric_stats": {
        "omzet": {
          "mean": 1500000000,
          "median": 1200000000
        }
      }
    }
  }
}
```

### 4. **Batch Prediction**

**Via Filament Admin:**

1. Klik **Batch Predict** di halaman Forecasting
2. Upload file Excel/CSV dengan data UMKM
3. Download hasil prediksi

**Via API:**

```bash
curl -X POST http://localhost:5000/api/predict-batch \
  -F "file=@umkm_batch.xlsx"
```

---

## 📈 Interpretasi Hasil

### **Cluster Stratifikasi:**

| Cluster | Label                | Karakteristik                                                |
| ------- | -------------------- | ------------------------------------------------------------ |
| **0**   | Cluster A - Kecil    | Omzet < 500 juta, Tenaga Kerja < 10 orang, Aset minimal      |
| **1**   | Cluster B - Menengah | Omzet 500 juta - 2 M, Tenaga Kerja 10-50 orang, Aset moderat |
| **2**   | Cluster C - Besar    | Omzet > 2 M, Tenaga Kerja > 50 orang, Aset signifikan        |

### **Confidence Score:**

- **90-100%**: Prediksi sangat akurat, UMKM memiliki karakteristik khas cluster
- **70-89%**: Prediksi akurat, beberapa overlap dengan cluster lain
- **< 70%**: Prediksi kurang yakin, UMKM memiliki karakteristik campuran

---

## 🔧 Configuration

### **Environment Variables**

```env
# Laravel .env
ML_SERVICE_URL=http://ml_service:5000
ML_SERVICE_TIMEOUT=60

# Docker Compose .env.deploy
APP_PORT=8000
DB_HOST=postgres
DB_NAME=cismart_db
DB_USERNAME=postgres
DB_PASSWORD=root
```

### **Model Persistence**

Models disimpan di Docker volume:

- `ml_models/` - Trained models (.joblib files)
- `ml_data/` - Training & prediction data
- `ml_logs/` - Application logs

---

## 🧪 Testing

### **Test ML Service**

```bash
# Health check
curl http://localhost:5000/health

# Get service info
curl http://localhost:5000/api/info

# Get model info (after training)
curl http://localhost:5000/api/model-info
```

### **Test Laravel Integration**

```bash
# Run migrations
docker compose exec app php artisan migrate

# Test MLService connection
docker compose exec app php artisan tinker
>>> $ml = app(\App\Services\MLService::class);
>>> $ml->healthCheck();
```

---

## 📊 Metrik Model

Dari penelitian (berdasarkan notebook):

| Metrik                      | Nilai                           |
| --------------------------- | ------------------------------- |
| **KMeans Silhouette Score** | 0.9774                          |
| **Random Forest Accuracy**  | 98%+                            |
| **Cross-validation Score**  | 5-fold CV                       |
| **SMOTE Applied**           | Yes (k_neighbors=2)             |
| **Features**                | 19 (11 numeric + 8 categorical) |

---

## 🛠️ Troubleshooting

### **ML Service tidak bisa diakses**

```bash
# Cek status container
docker compose ps

# Cek logs ML service
docker compose logs ml_service

# Restart ML service
docker compose restart ml_service
```

### **Training gagal**

- Pastikan file Excel/CSV valid
- Minimal 50 rows data
- Semua kolom numerik wajib ada
- Tidak ada nilai missing pada kolom numerik

### **Prediksi error "Models not trained"**

```bash
# Train models dulu via API atau admin panel
curl -X POST http://localhost:5000/api/train \
  -F "file=@data_training.xlsx"
```

---

## 📚 Referensi

- **Penelitian**: PDP Dosen Pemula - 166PL43AL.04 2025
- **Notebook**: `machine_learning/fix_hasil_perbanding_metode_clustering_lengkap.ipynb`
- **Libraries**:
  - scikit-learn 1.3.2
  - pandas 2.0.3
  - Flask 3.0.0
  - gower (Gower distance matrix)
  - imbalanced-learn (SMOTE)

---

## 👥 Support

Untuk pertanyaan atau issue:

1. Check logs: `docker compose logs ml_service`
2. Check Laravel logs: `laravel_app/storage/logs/`
3. Review API documentation: `http://localhost:5000/api/info`

---

**Built with ❤️ for CISMART - Cilacap Smart Marketplace**
