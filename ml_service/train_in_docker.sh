#!/bin/bash
# Script untuk training ML model di dalam Docker container

set -e

echo "🚀 Training ML Model di Docker Container"
echo "=========================================="

# Check if training data exists
if [ ! -f "combined_training_data.xlsx" ]; then
    echo "❌ File combined_training_data.xlsx tidak ditemukan!"
    echo "   Jalankan: python train_with_real_data.py terlebih dahulu"
    exit 1
fi

echo "✅ File training data ditemukan"

# Copy training data ke container
echo "📤 Copy training data ke ML service container..."
docker cp combined_training_data.xlsx cismart_ml_service:/app/combined_training_data.xlsx

# Train model di container
echo "🔄 Training model..."
docker exec cismart_ml_service python -c "
import requests
import os
import sys

file_path = '/app/combined_training_data.xlsx'
if not os.path.exists(file_path):
    print('❌ File tidak ditemukan di container')
    sys.exit(1)

print('📤 Uploading training data...')
with open(file_path, 'rb') as f:
    files = {'file': ('combined_training_data.xlsx', f)}
    try:
        response = requests.post('http://localhost:5000/api/train', files=files, timeout=600)
        
        if response.status_code == 200:
            result = response.json()
            print('\n✅ Training berhasil!')
            print(f\"KMeans Silhouette: {result.get('kmeans', {}).get('silhouette_score', 'N/A')}\")
            print(f\"Random Forest Accuracy: {result.get('random_forest', {}).get('accuracy', 'N/A')}\")
        else:
            print(f'❌ Training gagal: {response.status_code}')
            print(response.text)
            sys.exit(1)
    except Exception as e:
        print(f'❌ Error: {e}')
        sys.exit(1)
"

echo ""
echo "🎉 Training selesai!"
echo "Cek model info: docker exec cismart_ml_service curl http://localhost:5000/api/model-info"
