#!/bin/bash
# Script untuk manual training ML model di production VPS
# Gunakan jika automatic training di CI/CD gagal

set -e

echo "🤖 Manual ML Model Training for Production"
echo "=========================================="

# Check if running in correct directory
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ Error: docker-compose.yml not found!"
    echo "   Please run this script from the project root directory"
    exit 1
fi

# Check if ML service container is running
if ! docker ps | grep -q cismart_ml_service; then
    echo "❌ Error: ML service container not running!"
    echo "   Start containers: docker compose up -d"
    exit 1
fi

# Check if training data exists
TRAINING_FILE="ml_service/combined_training_data.xlsx"
if [ ! -f "$TRAINING_FILE" ]; then
    echo "❌ Error: Training data not found: $TRAINING_FILE"
    echo "   Please ensure the training data file exists"
    exit 1
fi

echo "✅ Checks passed. Starting training..."
echo ""

# Copy training data to container
echo "📂 Copying training data to container..."
docker cp "$TRAINING_FILE" cismart_ml_service:/app/combined_training_data.xlsx
echo "✅ File copied"
echo ""

# Train model
echo "🔄 Training model (this may take 30-60 seconds)..."
echo "   Dataset: 7,548 rows"
echo "   Features: 19 (11 numeric + 8 categorical)"
echo ""

docker exec cismart_ml_service bash -c "
    curl -X POST \
      -F 'file=@/app/combined_training_data.xlsx' \
      http://localhost:5000/api/train \
      --max-time 300 \
      --silent \
      --show-error \
      --write-out '\nHTTP Status: %{http_code}\n'
"

TRAINING_EXIT_CODE=$?

if [ $TRAINING_EXIT_CODE -eq 0 ]; then
    echo ""
    echo "✅ Training completed!"
    echo ""
    
    # Verify model status
    echo "🔍 Verifying model..."
    docker exec cismart_ml_service curl -s http://localhost:5000/api/model-info | \
        grep -o '"models_loaded":[^,}]*' | head -1
    
    echo ""
    echo "📊 Get full model info:"
    echo "   curl http://localhost:5000/api/model-info"
    echo ""
    echo "✅ Done! ML model is ready for predictions."
else
    echo ""
    echo "❌ Training failed!"
    echo ""
    echo "📋 Check ML service logs:"
    docker compose logs --tail 50 ml_service
    exit 1
fi

echo ""
echo "=========================================="
echo "✅ Manual training completed successfully"
echo "=========================================="
