"""
Train ML Model dengan Real Data dari Excel Files
Menggunakan: data.xlsx, data edit 1.xlsx, dan df_mixed.xlsx
"""

import pandas as pd
import requests
import time
import os
from pathlib import Path

# Konfigurasi
ML_SERVICE_URL = "http://127.0.0.1:5000"
DATA_FOLDER = Path("../machine_learning")

def load_and_combine_data():
    """Load dan gabungkan ketiga file Excel"""
    print("\n📂 Loading Excel files...")
    
    # File paths
    file1 = DATA_FOLDER / "data.xlsx"
    file2 = DATA_FOLDER / "data edit 1.xlsx"
    file3 = DATA_FOLDER / "df_mixed.xlsx"
    
    # Check files exist
    for f in [file1, file2, file3]:
        if not f.exists():
            print(f"❌ File tidak ditemukan: {f}")
            return None
    
    # Load data
    print(f"   Loading {file1.name}...")
    df1 = pd.read_excel(file1)
    print(f"   ✅ {file1.name}: {df1.shape[0]} rows, {df1.shape[1]} columns")
    
    print(f"   Loading {file2.name}...")
    df2 = pd.read_excel(file2)
    print(f"   ✅ {file2.name}: {df2.shape[0]} rows, {df2.shape[1]} columns")
    
    print(f"   Loading {file3.name}...")
    df3 = pd.read_excel(file3)
    print(f"   ✅ {file3.name}: {df3.shape[0]} rows, {df3.shape[1]} columns")
    
    # Standardisasi nama kolom
    rename_map = {
        "Nama Perusahaan": "nama_perusahaan",
        " Omzet ": "omzet",
        "Omzet": "omzet",
        "Aset": "aset",
        "Jumlah tenaga kerja": "jumlah_tenaga_kerja",
        "Uraian Status Penanaman Modal": "status_penanaman_modal",
        "Uraian Jenis Perusahaan": "jenis_perusahaan",
        "Uraian Risiko Proyek": "risiko_proyek",
        "Uraian Skala Usaha": "skala_usaha",
        "kecamatan_usaha": "kecamatan_usaha",
        "kelurahan_usaha": "kelurahan_usaha",
        "KL/Sektor Pembina": "kl_sektor_pembina",
        "Kbli": "kbli",
        "Judul Kbli": "judul_kbli",
        "Modal Kerja": "modal_kerja",
        "Jumlah Investasi": "jumlah_investasi",
        "TKI": "tki",
        "Mesin Peralatan": "mesin_peralatan",
        "Mesin Peralatan Impor": "mesin_peralatan_impor",
        "Pembelian Pematangan Tanah": "pembelian_pematangan_tanah",
        "Bangunan Gedung": "bangunan_gedung",
        "Lain Lain": "lain_lain",
    }
    
    df1.rename(columns=rename_map, inplace=True)
    df2.rename(columns=rename_map, inplace=True)
    df3.rename(columns=rename_map, inplace=True)
    
    # Gabungkan semua kolom
    all_cols = sorted(list(set(df1.columns) | set(df2.columns) | set(df3.columns)))
    df1 = df1.reindex(columns=all_cols)
    df2 = df2.reindex(columns=all_cols)
    df3 = df3.reindex(columns=all_cols)
    
    # Concat all
    df_combined = pd.concat([df1, df2, df3], ignore_index=True)
    
    # Remove duplicates
    df_combined = df_combined.drop_duplicates()
    
    print(f"\n✅ Data gabungan: {df_combined.shape[0]} rows, {df_combined.shape[1]} columns")
    
    return df_combined

def prepare_training_data(df):
    """Prepare data untuk ML training (19 features yang diperlukan)"""
    print("\n🔧 Preparing training data...")
    
    # 19 features yang diperlukan ML Service
    required_features = [
        # Numeric (11)
        'omzet', 'aset', 'modal_kerja', 'jumlah_investasi', 
        'jumlah_tenaga_kerja', 'tki', 'mesin_peralatan', 
        'mesin_peralatan_impor', 'pembelian_pematangan_tanah', 
        'bangunan_gedung', 'lain_lain',
        # Categorical (8)
        'jenis_perusahaan', 'risiko_proyek', 'skala_usaha', 
        'status_penanaman_modal', 'kecamatan_usaha', 'kelurahan_usaha', 
        'kl_sektor_pembina', 'judul_kbli'
    ]
    
    # Check missing columns
    missing_cols = [col for col in required_features if col not in df.columns]
    if missing_cols:
        print(f"⚠️  Missing columns: {missing_cols}")
        # Fill with default values
        for col in missing_cols:
            df[col] = 0 if col in ['tki', 'mesin_peralatan_impor', 'pembelian_pematangan_tanah', 
                                   'bangunan_gedung', 'lain_lain'] else 'Unknown'
    
    # Select only required features
    df_training = df[required_features].copy()
    
    # Clean numeric columns
    numeric_cols = required_features[:11]
    for col in numeric_cols:
        df_training[col] = pd.to_numeric(df_training[col], errors='coerce')
        df_training[col] = df_training[col].fillna(0)
    
    # Clean categorical columns
    categorical_cols = required_features[11:]
    for col in categorical_cols:
        df_training[col] = df_training[col].fillna('Unknown').astype(str)
    
    # Remove rows with all zeros in numeric columns
    df_training = df_training[
        (df_training[['omzet', 'aset', 'jumlah_tenaga_kerja']].sum(axis=1) > 0)
    ]
    
    print(f"✅ Training data siap: {len(df_training)} rows")
    print(f"   Numeric features: {numeric_cols}")
    print(f"   Categorical features: {categorical_cols}")
    
    return df_training

def save_and_upload(df, output_file="combined_training_data.xlsx"):
    """Save to Excel and upload to ML service"""
    print(f"\n💾 Saving to {output_file}...")
    df.to_excel(output_file, index=False)
    print(f"✅ File saved: {output_file}")
    
    # Check ML service
    print(f"\n🔍 Checking ML service at {ML_SERVICE_URL}...")
    try:
        response = requests.get(f"{ML_SERVICE_URL}/health", timeout=5)
        if response.status_code == 200:
            print("✅ ML service is online")
        else:
            print(f"⚠️  ML service returned status: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Cannot connect to ML service: {e}")
        return False
    
    # Upload file
    print(f"\n📤 Uploading {output_file} to ML service...")
    print("   This may take several minutes for large datasets...")
    
    try:
        with open(output_file, 'rb') as f:
            files = {'file': (output_file, f, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')}
            
            start_time = time.time()
            response = requests.post(
                f"{ML_SERVICE_URL}/api/train",
                files=files,
                timeout=600  # 10 minutes timeout
            )
            elapsed = time.time() - start_time
        
        if response.status_code == 200:
            result = response.json()
            print(f"\n✅ Training completed in {elapsed:.1f} seconds!")
            print("\n📊 Training Results:")
            print("=" * 60)
            
            # KMeans results
            kmeans = result.get('kmeans', {})
            print(f"\n🔹 KMeans Clustering:")
            print(f"   Optimal k: {kmeans.get('optimal_k', 'N/A')}")
            print(f"   Silhouette Score: {kmeans.get('silhouette_score', 'N/A')}")
            print(f"   Distance Metric: {kmeans.get('distance_metric', 'N/A')}")
            
            dist = kmeans.get('cluster_distribution', {})
            if dist:
                print(f"   Cluster Distribution:")
                for cluster, count in sorted(dist.items()):
                    print(f"      Cluster {cluster}: {count} records")
            
            # Random Forest results
            rf = result.get('random_forest', {})
            print(f"\n🔹 Random Forest Classifier:")
            print(f"   Accuracy: {rf.get('accuracy', 'N/A')}")
            print(f"   Features: {rf.get('n_features', 'N/A')}")
            
            best_params = rf.get('best_params', {})
            if best_params:
                print(f"   Best Parameters:")
                for param, value in best_params.items():
                    print(f"      {param}: {value}")
            
            print("\n" + "=" * 60)
            return True
        else:
            print(f"\n❌ Training failed!")
            print(f"Status: {response.status_code}")
            print(f"Response: {response.text}")
            return False
            
    except requests.exceptions.Timeout:
        print("\n⏱️  Request timeout after 10 minutes")
        print("   Training may still be running on the server")
        return False
    except Exception as e:
        print(f"\n❌ Upload failed: {e}")
        return False

def main():
    print("=" * 60)
    print("🚀 ML Model Training dengan Real Data")
    print("=" * 60)
    
    # Step 1: Load and combine data
    df_combined = load_and_combine_data()
    if df_combined is None:
        print("\n❌ Gagal load data. Proses dibatalkan.")
        return
    
    # Step 2: Prepare training data
    df_training = prepare_training_data(df_combined)
    
    if len(df_training) < 50:
        print(f"\n⚠️  WARNING: Only {len(df_training)} rows available.")
        print("   ML Service requires minimum 50 rows for training.")
        response = input("   Continue anyway? (y/n): ")
        if response.lower() != 'y':
            print("\n❌ Training cancelled.")
            return
    
    # Step 3: Save and upload
    success = save_and_upload(df_training, "combined_training_data.xlsx")
    
    if success:
        print("\n🎉 Training berhasil!")
        print("\n📝 Next steps:")
        print("   1. Test prediction via browser: http://127.0.0.1:8000/admin/ml-predictions/create")
        print("   2. Check model info: curl http://127.0.0.1:5000/api/model-info")
    else:
        print("\n⚠️  Training gagal atau belum selesai")
        print("   Check ML service logs untuk detail")

if __name__ == "__main__":
    main()
