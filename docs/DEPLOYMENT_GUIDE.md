# 🚀 CISMART Deployment Guide

## 📋 Daftar Isi

1. [Persiapan](#persiapan)
2. [Local Development](#local-development)
3. [Docker Deployment](#docker-deployment)
4. [VPS Deployment (CI/CD)](#vps-deployment-cicd)
5. [ML Model Training](#ml-model-training)
6. [Troubleshooting](#troubleshooting)

---

## 🛠️ Persiapan

### System Requirements:

- **Docker** 20.10+
- **Docker Compose** 2.0+
- **Git** 2.x
- **PHP** 8.4+ (untuk local dev)
- **Python** 3.11+ (untuk ML service)

### Repository:

```bash
git clone https://github.com/adrianramadhan/CISMART.git
cd CISMART/cismart-app
```

---

## 💻 Local Development

### 1. Setup Laravel

```bash
cd laravel_app
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan make:filament-user
php artisan serve
```

### 2. Setup ML Service

```bash
cd ../ml_service
pip install -r requirements.txt
python app.py
```

### 3. Train ML Model (Local)

```bash
cd ml_service
python train_with_real_data.py
```

Output:

```
✅ Data gabungan: 7,548 rows
✅ Training completed in 30.0 seconds
🎉 Training berhasil!
```

### 4. Test

```bash
cd ../laravel_app
php test_filament_create_flow.php
```

---

## 🐳 Docker Deployment

### 1. Build Containers

```bash
docker compose up -d --build
```

Containers yang akan dibuat:

- `laravel_app` - Laravel + Filament (Port 8000)
- `cismart_ml_service` - Python ML Service (Port 5000)
- `laravel_postgres` - PostgreSQL 15 (Port 5433)
- `laravel_pgadmin` - PgAdmin 4 (Port 8001)

### 2. Setup Laravel di Container

```bash
# Composer install
docker compose run --rm app composer install --optimize-autoloader

# Database migration
docker compose run --rm app php artisan migrate --force

# Create admin user
docker compose run --rm app php artisan make:filament-user

# Cache optimization
docker compose run --rm app php artisan config:cache
docker compose run --rm app php artisan route:cache
docker compose run --rm app php artisan filament:optimize
```

### 3. Train ML Model di Container

```bash
# Copy training data ke container
docker cp ml_service/combined_training_data.xlsx cismart_ml_service:/app/

# Execute training script
docker exec cismart_ml_service python -c "
import requests
with open('/app/combined_training_data.xlsx', 'rb') as f:
    files = {'file': ('combined_training_data.xlsx', f)}
    response = requests.post('http://localhost:5000/api/train', files=files, timeout=600)
    print(response.json())
"
```

Atau gunakan script:

```bash
cd ml_service
bash train_in_docker.sh
```

### 4. Verify Deployment

```bash
# Check container status
docker compose ps

# Check ML service health
curl http://localhost:5000/health

# Check model info
curl http://localhost:5000/api/model-info

# Check Laravel
curl http://localhost:8000
```

### 5. View Logs

```bash
# Laravel logs
docker compose logs -f app

# ML service logs
docker compose logs -f ml_service

# PostgreSQL logs
docker compose logs -f postgres
```

---

## 🌐 VPS Deployment (CI/CD)

### GitHub Actions Workflow

Setiap push ke branch `master` akan trigger CI/CD:

**File:** `.github/workflows/docker-vps.yml`

#### Secrets yang Dibutuhkan:

| Secret        | Deskripsi                    | Contoh                |
| ------------- | ---------------------------- | --------------------- |
| `VPS_HOST`    | IP/Hostname VPS              | `192.168.1.100`       |
| `VPS_USER`    | SSH Username                 | `root`                |
| `VPS_SSH_KEY` | Private SSH Key              | `-----BEGIN RSA...`   |
| `VPS_PATH`    | Deploy directory             | `/var/www/cismart`    |
| `GIT_TOKEN`   | GitHub Personal Access Token | `ghp_xxxx`            |
| `LARAVEL_DIR` | Laravel directory            | `laravel_app`         |
| `LARAVEL_ENV` | .env file content            | `APP_NAME=CISMART...` |
| `APP_PORT`    | Laravel port                 | `8000`                |
| `DB_HOST`     | Database host                | `postgres`            |
| `DB_NAME`     | Database name                | `cismart_db`          |
| `DB_USERNAME` | Database user                | `postgres`            |
| `DB_PASSWORD` | Database password            | `secret`              |

#### Workflow Steps:

1. ✅ Checkout repo di VPS
2. ✅ Build Docker containers
3. ✅ Wait for ML service ready
4. ✅ Composer install
5. ✅ Database migration
6. ✅ Create Filament user
7. ✅ Cache optimization
8. ✅ Restart services

#### Manual Deployment di VPS:

```bash
# SSH ke VPS
ssh user@your-vps-ip

# Clone/update repo
cd /var/www/cismart
git pull origin master

# Deploy dengan script
bash deploy_and_train.sh
```

Script `deploy_and_train.sh` akan:

- Build containers
- Setup Laravel
- Train ML model (jika ada training data)
- Restart services
- Show status

---

## 🤖 ML Model Training

### Training Data Sources:

1. `machine_learning/data.xlsx` - 71 rows
2. `machine_learning/data edit 1.xlsx` - 20,786 rows
3. `machine_learning/df_mixed.xlsx` - 3,561 rows

**Combined:** 7,548 rows (after deduplication)

### Training Process:

#### Local Training:

```bash
cd ml_service
python train_with_real_data.py
```

Output:

```
📂 Loading Excel files...
   ✅ data.xlsx: 71 rows
   ✅ data edit 1.xlsx: 20,786 rows
   ✅ df_mixed.xlsx: 3,561 rows

✅ Data gabungan: 7,550 rows
✅ Training data siap: 7,548 rows

📤 Uploading to ML service...
✅ Training completed in 30.0 seconds!

🔹 KMeans Clustering:
   Optimal k: 3
   Silhouette Score: 0.xxxx

🔹 Random Forest Classifier:
   Accuracy: 92.23%
```

#### Docker Training:

```bash
cd ml_service
bash train_in_docker.sh
```

#### VPS Training:

Training otomatis dijalankan oleh CI/CD workflow jika file `combined_training_data.xlsx` ada di `ml_service/`.

### Model Metrics:

| Metric        | Before (50 rows) | After (7,548 rows) | Improvement    |
| ------------- | ---------------- | ------------------ | -------------- |
| Confidence    | 79.67%           | 92.23%             | +12.56% ✅     |
| Data Size     | 50 rows          | 7,548 rows         | +7,498 rows ✅ |
| Training Time | 5s               | 30s                | -              |

---

## 🔍 Troubleshooting

### 1. Container Won't Start

**Problem:** `docker compose up -d` gagal

```bash
# Check logs
docker compose logs

# Rebuild from scratch
docker compose down -v
docker compose up -d --build
```

### 2. ML Service Unreachable

**Problem:** Laravel tidak bisa connect ke ML service

```bash
# Check ML service status
docker compose exec ml_service curl http://localhost:5000/health

# Check network
docker network inspect cismart-app_laravel

# Update Laravel .env
ML_SERVICE_URL=http://ml_service:5000  # Docker hostname
# OR
ML_SERVICE_URL=http://127.0.0.1:5000   # Localhost (dev)
```

### 3. Database Connection Failed

**Problem:** `SQLSTATE[08006] Connection refused`

```bash
# Check postgres status
docker compose ps postgres

# Check postgres logs
docker compose logs postgres

# Test connection
docker compose exec app php artisan db:show
```

### 4. Training Failed

**Problem:** Model training timeout atau error

```bash
# Check ML service logs
docker compose logs ml_service

# Verify training data
ls -lh ml_service/combined_training_data.xlsx

# Test upload manually
curl -X POST http://localhost:5000/api/train \
  -F "file=@ml_service/combined_training_data.xlsx"
```

### 5. Permission Denied (VPS)

**Problem:** CI/CD deployment gagal dengan permission error

```bash
# Fix Docker permissions
sudo usermod -aG docker $USER
newgrp docker

# Fix directory permissions
sudo chown -R $USER:$USER /var/www/cismart
```

### 6. Port Already in Use

**Problem:** Port 8000/5000 sudah digunakan

```bash
# Check what's using the port
sudo lsof -i :8000
sudo lsof -i :5000

# Kill process
sudo kill -9 <PID>

# Or change port in .env.deploy
APP_PORT=8080
```

---

## 📊 Health Checks

### Laravel App:

```bash
curl http://localhost:8000
curl http://localhost:8000/admin
```

### ML Service:

```bash
curl http://localhost:5000/health
curl http://localhost:5000/api/info
curl http://localhost:5000/api/model-info
```

### Database:

```bash
docker compose exec postgres psql -U postgres -d cismart_db -c "\dt"
```

### PgAdmin:

```
URL: http://localhost:8001
Email: admin@example.com
Password: admin
```

---

## 🎯 Post-Deployment Checklist

- [ ] Containers running (4/4)
- [ ] Laravel accessible via browser
- [ ] ML service health check passes
- [ ] Database migration completed
- [ ] Filament admin user created
- [ ] ML model trained (check `/api/model-info`)
- [ ] Test prediction via Filament UI
- [ ] SSL certificate configured (production)
- [ ] Backup strategy implemented
- [ ] Monitoring setup (logs, metrics)

---

## 📚 Additional Resources

- **Laravel Docs:** https://laravel.com/docs
- **Filament Docs:** https://filamentphp.com/docs
- **Docker Compose:** https://docs.docker.com/compose/
- **Flask ML Service:** `ml_service/README.md`

---

## 🆘 Support

Jika mengalami masalah:

1. Check logs: `docker compose logs -f`
2. Verify .env configuration
3. Review troubleshooting section above
4. Contact: laurasari@pnc.ac.id

---

**Last Updated:** 21 November 2025  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
