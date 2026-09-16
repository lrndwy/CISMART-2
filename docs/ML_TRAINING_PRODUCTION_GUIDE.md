# 🤖 ML Model Training - Production Guide

**Date:** 21 November 2025  
**Status:** Automatic Training Enabled in CI/CD ✅

---

## 🎯 Overview

Setiap kali deployment via CI/CD, **ML model akan otomatis ditraining** dengan data dari `ml_service/combined_training_data.xlsx` (7,548 rows).

---

## ✅ Automatic Training (CI/CD)

### Workflow:

1. Code pushed ke GitHub `master` branch
2. GitHub Actions trigger deployment ke VPS
3. Docker containers dibangun
4. **ML service menunggu hingga healthy** (max 2 menit)
5. **Training data di-copy ke container**
6. **Model ditraining otomatis** (30-60 detik)
7. **Status verified**: `models_loaded: true`
8. Application siap menerima prediction requests

### CI/CD Steps:

```yaml
# .github/workflows/docker-vps.yml

# Step 1: Copy training data
docker cp ml_service/combined_training_data.xlsx cismart_ml_service:/app/

# Step 2: Train model
docker exec cismart_ml_service curl -X POST \
  -F 'file=@/app/combined_training_data.xlsx' \
  http://localhost:5000/api/train

# Step 3: Verify
curl http://localhost:5000/api/model-info
```

### Expected Output:

```json
{
  "status": "success",
  "metrics": {
    "random_forest": {
      "accuracy": 0.999, // 99.9%
      "n_features": 19
    },
    "kmeans": {
      "n_clusters": 3,
      "silhouette_score": 0.229,
      "cluster_distribution": {
        "0": 961, // 23.91% - UMKM Mikro/Kecil
        "1": 1506, // 37.46% - UMKM Menengah
        "2": 1553 // 38.63% - UMKM Besar
      }
    }
  }
}
```

---

## 🔧 Manual Training (Jika Diperlukan)

### Scenario:

- Automatic training gagal
- Update training data dengan data baru
- Re-train model tanpa full deployment

### Method 1: Using Script (Recommended)

#### Bash (Linux/Mac/WSL):

```bash
ssh user@vps-ip
cd /var/www/cismart
bash train_ml_production.sh
```

#### PowerShell (Windows):

```powershell
ssh user@vps-ip
cd /var/www/cismart
powershell -File train_ml_production.ps1
```

### Method 2: Manual Commands

```bash
# 1. SSH ke VPS
ssh user@your-vps-ip

# 2. Navigate ke project directory
cd /var/www/cismart

# 3. Copy training data ke container
docker cp ml_service/combined_training_data.xlsx cismart_ml_service:/app/

# 4. Train model
docker exec cismart_ml_service curl -X POST \
  -F 'file=@/app/combined_training_data.xlsx' \
  http://localhost:5000/api/train \
  --max-time 300

# 5. Verify model status
docker exec cismart_ml_service curl http://localhost:5000/api/model-info
```

### Method 3: Via External API (From Local Machine)

```bash
# Upload new training data from your local machine
curl -X POST https://your-domain.com:5000/api/train \
  -F 'file=@/path/to/new_training_data.xlsx' \
  --max-time 600
```

---

## 📊 Training Data

### Current Dataset:

- **File:** `ml_service/combined_training_data.xlsx`
- **Rows:** 7,548 UMKM records
- **Sources:**
  - `data.xlsx` (71 rows)
  - `data edit 1.xlsx` (20,786 rows)
  - `df_mixed.xlsx` (3,561 rows)
- **After deduplication:** 7,548 unique records

### Features (19 total):

#### Numeric (11):

1. omzet
2. aset
3. modal_kerja
4. jumlah_investasi
5. jumlah_tenaga_kerja
6. tki
7. mesin_peralatan
8. mesin_peralatan_impor
9. pembelian_pematangan_tanah
10. bangunan_gedung
11. lain_lain

#### Categorical (8):

1. jenis_perusahaan
2. risiko_proyek
3. skala_usaha
4. status_penanaman_modal
5. kecamatan_usaha
6. kelurahan_usaha
7. kl_sektor_pembina
8. judul_kbli

---

## 🔍 Verify Model Status

### Method 1: API Endpoint

```bash
curl http://localhost:5000/api/model-info
```

### Method 2: Health Check

```bash
curl http://localhost:5000/health
```

**Expected Response:**

```json
{
  "status": "healthy",
  "service": "CISMART ML Service",
  "models_loaded": true, // ✅ Must be true
  "timestamp": "2025-11-21T15:30:00"
}
```

### Method 3: Docker Logs

```bash
docker compose logs ml_service --tail 50
```

Look for:

```
Training completed successfully
Models loaded: KMeans + RandomForest
Ready to accept prediction requests
```

---

## ⚠️ Troubleshooting

### Problem 1: "Model Not Trained" Alert

**Symptoms:**

- Alert muncul di Filament UI
- `models_loaded: false` di health check
- Predictions return null

**Solutions:**

#### A. Check CI/CD Logs

```bash
# Di GitHub Actions
# Check step: "Training ML Model..."
# Look for: "✅ ML Model trained successfully!"
```

#### B. Manual Training via SSH

```bash
ssh user@vps-ip
cd /var/www/cismart
bash train_ml_production.sh
```

#### C. Check Container Status

```bash
docker ps  # Ensure cismart_ml_service is running
docker compose logs ml_service  # Check for errors
```

### Problem 2: Training Timeout

**Symptoms:**

```
curl: (28) Operation timed out after 300000 milliseconds
```

**Solutions:**

#### A. Increase timeout di CI/CD

```yaml
# .github/workflows/docker-vps.yml
--max-time 600 # 10 minutes instead of 5
```

#### B. Check resource usage

```bash
docker stats cismart_ml_service
# CPU should be < 80%
# Memory should have headroom
```

#### C. Reduce dataset size temporarily

```bash
# Use smaller sample for testing
# Then upload full dataset later
```

### Problem 3: Training Data Not Found

**Symptoms:**

```
⚠️  Training data not found: ml_service/combined_training_data.xlsx
```

**Solutions:**

#### A. Verify file in repo

```bash
git ls-files ml_service/combined_training_data.xlsx
# Should return: ml_service/combined_training_data.xlsx
```

#### B. Add to git if missing

```bash
git add ml_service/combined_training_data.xlsx
git commit -m "Add training data"
git push origin master
```

#### C. Check .gitignore

```bash
# Ensure *.xlsx is NOT ignored
cat .gitignore | grep xlsx
# Should return nothing or commented line
```

---

## 📈 Model Performance

### Current Metrics (7,548 rows):

```
Random Forest Classifier:
  ✅ Accuracy: 99.90%
  ✅ Features: 19
  ✅ Best params: balanced class weight, max_depth=16

KMeans Clustering:
  ✅ Clusters: 3
  ✅ Silhouette Score: 0.2293
  ✅ Distance: Gower (mixed data)

Cluster Distribution:
  - Cluster 0 (Mikro/Kecil): 961 records (23.91%)
  - Cluster 1 (Menengah): 1,506 records (37.46%)
  - Cluster 2 (Besar): 1,553 records (38.63%)
```

### Improvement Over Time:

| Version          | Rows  | Accuracy   | Confidence |
| ---------------- | ----- | ---------- | ---------- |
| v1.0 (synthetic) | 50    | 69.23%     | 79.67%     |
| v2.0 (real data) | 7,548 | **99.90%** | **92.23%** |

---

## 🔄 Update Training Data

### When to Update:

- [ ] New UMKM data collected (monthly/quarterly)
- [ ] Data quality improved
- [ ] Business rules changed
- [ ] Model accuracy declining

### Update Process:

#### 1. Prepare New Data

```bash
# Combine old + new data
cd ml_service
python train_with_real_data.py  # Generates combined_training_data.xlsx
```

#### 2. Commit & Push

```bash
git add ml_service/combined_training_data.xlsx
git commit -m "Update training data: [describe changes]"
git push origin master
```

#### 3. Automatic Deployment

- CI/CD akan otomatis deploy
- Model akan retrain dengan data baru
- Verify metrics di logs

#### 4. Verify New Model

```bash
# Check accuracy improvement
curl https://your-domain.com:5000/api/model-info

# Compare with previous metrics
# Ensure accuracy >= previous version
```

---

## 📝 Best Practices

### 1. Training Data Management

- ✅ Keep training data in git (if < 100MB)
- ✅ Use Git LFS for large files (> 100MB)
- ✅ Backup training data regularly
- ✅ Version training data (v1, v2, etc.)

### 2. Model Versioning

- ✅ Tag releases: `git tag v2.0-model-update`
- ✅ Document accuracy changes in CHANGELOG
- ✅ Keep old models for rollback
- ✅ A/B test new models before full deployment

### 3. Monitoring

- ✅ Set up alerts for model failures
- ✅ Monitor prediction accuracy over time
- ✅ Track cluster distribution changes
- ✅ Log all training events

### 4. Security

- ✅ Restrict training endpoint in production
- ✅ Use authentication for sensitive data
- ✅ Encrypt training data at rest
- ✅ Audit training data access

---

## 🆘 Emergency Rollback

### If new model performs poorly:

```bash
# 1. SSH to VPS
ssh user@vps-ip

# 2. Copy old training data
cp ml_service/combined_training_data_backup.xlsx \
   ml_service/combined_training_data.xlsx

# 3. Retrain with old data
bash train_ml_production.sh

# 4. Or rollback Docker container
docker compose down
git checkout v1.0  # previous stable version
docker compose up -d --build
```

---

## 📞 Support

**Issues with training?**

1. Check CI/CD logs di GitHub Actions
2. Run manual training: `bash train_ml_production.sh`
3. Check Docker logs: `docker compose logs ml_service`
4. Contact: laurasari@pnc.ac.id

---

**Last Updated:** 21 November 2025  
**Version:** 2.0  
**Status:** ✅ Automatic Training Enabled
