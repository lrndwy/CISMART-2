"""
Train ML Model with Sample Data
Run: python train_model.py
"""

import requests
import os

# Configuration
ML_SERVICE_URL = "http://127.0.0.1:5000"
TRAINING_FILE = "sample_training_data.csv"

print("=" * 60)
print("Training ML Model")
print("=" * 60)
print()

# Check if file exists
if not os.path.exists(TRAINING_FILE):
    print(f"❌ Error: Training file not found: {TRAINING_FILE}")
    exit(1)

print(f"[Step 1] Training file: {TRAINING_FILE}")
print(f"   File size: {os.path.getsize(TRAINING_FILE)} bytes")
print()

# Check ML Service
print("[Step 2] Checking ML Service...")
try:
    response = requests.get(f"{ML_SERVICE_URL}/health", timeout=5)
    if response.status_code == 200:
        health = response.json()
        print(f"   ✅ ML Service is online")
        print(f"   Status: {health['status']}")
        print(f"   Models loaded: {health['models_loaded']}")
    else:
        print(f"   ❌ ML Service returned error: {response.status_code}")
        exit(1)
except Exception as e:
    print(f"   ❌ Cannot connect to ML Service: {e}")
    exit(1)

print()

# Upload and train
print("[Step 3] Uploading training data...")
try:
    with open(TRAINING_FILE, 'rb') as f:
        files = {'file': (TRAINING_FILE, f, 'text/csv')}
        
        print("   Uploading... (this may take 2-5 minutes)")
        response = requests.post(
            f"{ML_SERVICE_URL}/api/train",
            files=files,
            timeout=300  # 5 minutes timeout
        )
        
        if response.status_code == 200:
            result = response.json()
            print("   ✅ Training completed successfully!")
            print()
            print("[Step 4] Training results:")
            
            if 'result' in result and 'metrics' in result['result']:
                metrics = result['result']['metrics']
                
                # KMeans metrics
                if 'kmeans' in metrics:
                    kmeans = metrics['kmeans']
                    print(f"   KMeans Clustering:")
                    print(f"      Clusters: {kmeans['n_clusters']}")
                    print(f"      Silhouette Score: {kmeans['silhouette_score']:.4f}")
                    if 'cluster_distribution' in kmeans:
                        print(f"      Distribution: {kmeans['cluster_distribution']}")
                
                print()
                
                # Random Forest metrics
                if 'random_forest' in metrics:
                    rf = metrics['random_forest']
                    print(f"   Random Forest Classifier:")
                    print(f"      Accuracy: {rf['accuracy']:.4f} ({rf['accuracy']*100:.2f}%)")
                    print(f"      Features: {rf['n_features']}")
                    if 'best_params' in rf:
                        print(f"      Best params: {rf['best_params']}")
            
            print()
            print("=" * 60)
            print("✅ Model Training Success!")
            print("=" * 60)
            print()
            print("Next steps:")
            print("1. Test prediction via Filament:")
            print("   http://127.0.0.1:8000/admin/ml-predictions/create")
            print()
            print("2. Test via API:")
            print("   curl -X POST http://127.0.0.1:5000/api/predict \\")
            print("     -H 'Content-Type: application/json' \\")
            print("     -d @test_data.json")
            print()
            
        else:
            print(f"   ❌ Training failed")
            print(f"   Status code: {response.status_code}")
            print(f"   Response: {response.text}")
            exit(1)
            
except requests.exceptions.Timeout:
    print("   ❌ Training timeout (exceeded 5 minutes)")
    print("   The training process may still be running on the server.")
    exit(1)
except Exception as e:
    print(f"   ❌ Training error: {e}")
    exit(1)
