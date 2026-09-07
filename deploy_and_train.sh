#!/bin/bash
# Script untuk deploy aplikasi dan training ML model
# Untuk dijalankan di VPS setelah git pull

set -e

echo "🚀 CISMART Deployment & ML Training"
echo "===================================="

# 1. Build dan start containers
echo ""
echo "📦 Step 1: Building Docker containers..."
docker compose down
docker compose up -d --build

# 2. Wait for services
echo ""
echo "⏳ Step 2: Waiting for services to be ready..."
for i in {1..30}; do
    if docker compose exec -T ml_service python -c "import requests; requests.get('http://localhost:5000/health')" 2>/dev/null; then
        echo "✅ ML service is ready!"
        break
    fi
    echo "   Waiting for ML service... ($i/30)"
    sleep 2
done

# 3. Laravel setup
echo ""
echo "🔧 Step 3: Setting up Laravel..."
docker compose run --rm app composer install --optimize-autoloader --no-dev
docker compose run --rm app php artisan migrate --force
docker compose run --rm app php artisan config:cache
docker compose run --rm app php artisan route:cache
docker compose run --rm app php artisan view:cache
docker compose run --rm app php artisan filament:optimize

# 4. Train ML model (jika ada training data)
echo ""
echo "🤖 Step 4: Training ML model..."
if [ -f "ml_service/combined_training_data.xlsx" ]; then
    echo "   Training data found, starting training..."
    cd ml_service
    bash train_in_docker.sh || echo "⚠️  Training gagal, lanjutkan dengan model lama"
    cd ..
else
    echo "⚠️  Training data tidak ditemukan di ml_service/combined_training_data.xlsx"
    echo "   Model akan menggunakan data yang sudah ada (jika ada)"
fi

# 5. Restart services
echo ""
echo "🔄 Step 5: Restarting services..."
docker compose restart app

# 6. Show status
echo ""
echo "📊 Step 6: Container status:"
docker compose ps

echo ""
echo "✅ Deployment selesai!"
echo ""
echo "🌐 URLs:"
echo "   - Laravel App: http://$(hostname -I | awk '{print $1}'):${APP_PORT:-8000}"
echo "   - ML Service: http://$(hostname -I | awk '{print $1}'):5000"
echo "   - PgAdmin: http://$(hostname -I | awk '{print $1}'):8001"
echo ""
echo "🔍 Cek logs:"
echo "   docker compose logs -f app"
echo "   docker compose logs -f ml_service"
