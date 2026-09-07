"""
ML Engine for UMKM Clustering and Prediction
Based on research notebook: fix_hasil_perbanding_metode_clustering_lengkap.ipynb
"""

import pandas as pd
import numpy as np
import joblib
import os
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.ensemble import RandomForestClassifier
from sklearn.cluster import KMeans
from sklearn.model_selection import train_test_split, RandomizedSearchCV
from sklearn.metrics import silhouette_score, accuracy_score, classification_report
from imblearn.over_sampling import SMOTE
import gower
from datetime import datetime
import warnings
warnings.filterwarnings('ignore')

class UMKMClusteringEngine:
    """
    Engine for UMKM clustering and growth prediction
    Implements KMeans clustering and Random Forest classification
    """
    
    def __init__(self, models_dir='/app/models', data_dir='/app/data'):
        self.models_dir = models_dir
        self.data_dir = data_dir
        self.is_trained = False
        
        # Model components
        self.kmeans_model = None
        self.rf_classifier = None
        self.scaler = None
        self.label_encoders = {}
        self.feature_names = None
        self.cluster_profiles = None
        self.model_metrics = {}
        
        # Feature definitions (from notebook)
        self.numeric_features = [
            'aset', 'bangunan_gedung', 'jumlah_investasi', 'jumlah_tenaga_kerja',
            'lain_lain', 'mesin_peralatan', 'mesin_peralatan_impor', 'modal_kerja',
            'omzet', 'pembelian_pematangan_tanah', 'tki'
        ]
        
        self.categorical_features = [
            'jenis_perusahaan', 'risiko_proyek', 'skala_usaha', 
            'status_penanaman_modal', 'kecamatan_usaha', 'kelurahan_usaha',
            'kl_sektor_pembina', 'judul_kbli'
        ]
        
    def load_and_preprocess_data(self, filepath):
        """Load and preprocess data from Excel/CSV"""
        # Load data
        if filepath.endswith('.csv'):
            df = pd.read_csv(filepath)
        else:
            df = pd.read_excel(filepath)
        
        print(f"Loaded data shape: {df.shape}")
        
        # Convert numeric columns
        for col in self.numeric_features:
            if col in df.columns:
                df[col] = pd.to_numeric(df[col], errors='coerce')
        
        # Drop rows with missing numeric values
        df = df.dropna(subset=self.numeric_features, how='any')
        
        # Fill missing categorical values
        for col in self.categorical_features:
            if col in df.columns:
                df[col] = df[col].fillna('Unknown')
                df[col] = df[col].astype(str).str.strip()
        
        # Remove duplicates
        df = df.drop_duplicates()
        
        print(f"After preprocessing: {df.shape}")
        return df
    
    def prepare_features(self, df, fit=True):
        """Prepare features for modeling"""
        X = df.copy()
        
        # Encode categorical features
        for col in self.categorical_features:
            if col in X.columns:
                if fit:
                    self.label_encoders[col] = LabelEncoder()
                    X[col] = self.label_encoders[col].fit_transform(X[col])
                else:
                    if col in self.label_encoders:
                        # Handle unseen labels
                        le = self.label_encoders[col]
                        X[col] = X[col].apply(lambda x: x if x in le.classes_ else 'Unknown')
                        if 'Unknown' not in le.classes_:
                            le.classes_ = np.append(le.classes_, 'Unknown')
                        X[col] = le.transform(X[col])
        
        # Select features in order
        all_features = self.numeric_features + self.categorical_features
        available_features = [f for f in all_features if f in X.columns]
        X = X[available_features]
        
        if fit:
            self.feature_names = available_features
        
        # Scale numeric features
        if fit:
            self.scaler = StandardScaler()
            X[self.numeric_features] = self.scaler.fit_transform(X[self.numeric_features])
        else:
            numeric_in_df = [f for f in self.numeric_features if f in X.columns]
            X[numeric_in_df] = self.scaler.transform(X[numeric_in_df])
        
        return X
    
    def train(self, filepath):
        """
        Train clustering and classification models
        Returns metrics and cluster information
        """
        print(f"Starting training with file: {filepath}")
        
        # Load and preprocess data
        df = self.load_and_preprocess_data(filepath)
        
        if len(df) < 50:
            raise ValueError(f"Insufficient data: {len(df)} rows. Need at least 50 rows.")
        
        # Prepare features
        X = self.prepare_features(df, fit=True)
        
        # PHASE 1: KMeans Clustering
        print("\n=== Phase 1: KMeans Clustering ===")
        optimal_k = 3  # From notebook analysis
        
        self.kmeans_model = KMeans(
            n_clusters=optimal_k,
            init='k-means++',
            n_init=10,
            max_iter=300,
            random_state=42
        )
        
        cluster_labels = self.kmeans_model.fit_predict(X)
        df['cluster'] = cluster_labels
        
        # Calculate silhouette score using Gower distance
        gower_dist = gower.gower_matrix(X)
        silhouette_avg = silhouette_score(gower_dist, cluster_labels, metric='precomputed')
        
        print(f"KMeans trained with k={optimal_k}")
        print(f"Silhouette Score (Gower): {silhouette_avg:.4f}")
        
        # Analyze cluster profiles
        self.cluster_profiles = self._analyze_clusters(df)
        
        # PHASE 2: Random Forest Classification
        print("\n=== Phase 2: Random Forest Classification ===")
        
        # Prepare data for classification
        X_clf = X.copy()
        y_clf = cluster_labels
        
        # Train-test split (stratified)
        X_train, X_test, y_train, y_test = train_test_split(
            X_clf, y_clf, 
            test_size=0.25, 
            random_state=42,
            stratify=y_clf
        )
        
        # Handle class imbalance with SMOTE
        print("Applying SMOTE for class balancing...")
        # Adaptive k_neighbors
        min_samples = min(np.bincount(y_train))
        k_neighbors = min(2, min_samples - 1) if min_samples > 1 else 1
        
        smote = SMOTE(random_state=42, k_neighbors=k_neighbors)
        X_train_balanced, y_train_balanced = smote.fit_resample(X_train, y_train)
        
        print(f"After SMOTE: {X_train_balanced.shape[0]} samples")
        
        # Hyperparameter tuning
        print("Hyperparameter tuning with RandomizedSearchCV...")
        
        param_dist = {
            'n_estimators': [100, 200, 300],
            'max_depth': [None, 8, 12, 16],
            'min_samples_split': [2, 5, 10],
            'min_samples_leaf': [1, 2, 4],
            'class_weight': [None, 'balanced']
        }
        
        rf_base = RandomForestClassifier(random_state=42, n_jobs=-1)
        
        random_search = RandomizedSearchCV(
            rf_base,
            param_distributions=param_dist,
            n_iter=20,
            cv=5,
            scoring='balanced_accuracy',
            random_state=42,
            n_jobs=-1,
            verbose=1
        )
        
        random_search.fit(X_train_balanced, y_train_balanced)
        
        self.rf_classifier = random_search.best_estimator_
        
        print(f"\nBest parameters: {random_search.best_params_}")
        
        # Evaluate on test set
        y_pred = self.rf_classifier.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred)
        
        print(f"\nTest Accuracy: {accuracy:.4f}")
        print("\nClassification Report:")
        print(classification_report(y_test, y_pred))
        
        # Store metrics
        self.model_metrics = {
            'kmeans': {
                'n_clusters': optimal_k,
                'silhouette_score': float(silhouette_avg),
                'cluster_distribution': {int(k): int(v) for k, v in df['cluster'].value_counts().to_dict().items()}
            },
            'random_forest': {
                'accuracy': float(accuracy),
                'best_params': random_search.best_params_,
                'n_features': len(self.feature_names)
            }
        }
        
        # Save models
        self._save_models()
        
        # Save clustered data
        output_file = os.path.join(self.data_dir, f'cluster_results_{datetime.now().strftime("%Y%m%d_%H%M%S")}.csv')
        df.to_csv(output_file, index=False)
        
        self.is_trained = True
        
        return {
            'metrics': self.model_metrics,
            'cluster_profiles': self.cluster_profiles,
            'output_file': output_file
        }
    
    def _analyze_clusters(self, df):
        """Analyze cluster characteristics"""
        profiles = {}
        
        for cluster_id in df['cluster'].unique():
            cluster_data = df[df['cluster'] == cluster_id]
            
            profile = {
                'cluster_id': int(cluster_id),
                'size': len(cluster_data),
                'percentage': round(len(cluster_data) / len(df) * 100, 2),
                'numeric_stats': {}
            }
            
            # Aggregate numeric features
            for col in self.numeric_features:
                if col in cluster_data.columns:
                    profile['numeric_stats'][col] = {
                        'mean': float(cluster_data[col].mean()),
                        'median': float(cluster_data[col].median()),
                        'std': float(cluster_data[col].std())
                    }
            
            # Most common categorical values
            profile['categorical_mode'] = {}
            for col in self.categorical_features:
                if col in cluster_data.columns:
                    mode_value = cluster_data[col].mode()
                    if len(mode_value) > 0:
                        profile['categorical_mode'][col] = str(mode_value.iloc[0])
            
            profiles[int(cluster_id)] = profile
        
        return profiles
    
    def predict_single(self, data):
        """Predict cluster for single UMKM"""
        if not self.is_trained:
            raise ValueError("Models not trained. Call train() first.")
        
        # Convert to DataFrame
        df = pd.DataFrame([data])
        
        # Ensure all features are present
        for col in self.numeric_features:
            if col not in df.columns:
                df[col] = 0
        
        for col in self.categorical_features:
            if col not in df.columns:
                df[col] = 'Unknown'
        
        # Prepare features
        X = self.prepare_features(df, fit=False)
        
        # Predict cluster
        cluster_id = int(self.rf_classifier.predict(X)[0])
        probabilities = self.rf_classifier.predict_proba(X)[0]
        
        # Get cluster profile
        cluster_profile = self.cluster_profiles.get(cluster_id, {})
        
        return {
            'predicted_cluster': cluster_id,
            'confidence': float(max(probabilities)),
            'probabilities': {int(i): float(p) for i, p in enumerate(probabilities)},
            'cluster_profile': cluster_profile
        }
    
    def predict_batch(self, filepath):
        """Predict clusters for batch of UMKMs"""
        if not self.is_trained:
            raise ValueError("Models not trained. Call train() first.")
        
        # Load data
        df = self.load_and_preprocess_data(filepath)
        
        # Prepare features
        X = self.prepare_features(df, fit=False)
        
        # Predict
        predictions = self.rf_classifier.predict(X)
        probabilities = self.rf_classifier.predict_proba(X)
        
        # Add predictions to dataframe
        df['predicted_cluster'] = predictions
        df['prediction_confidence'] = probabilities.max(axis=1)
        
        return df
    
    def _save_models(self):
        """Save trained models to disk"""
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        
        # Save KMeans
        joblib.dump(self.kmeans_model, os.path.join(self.models_dir, 'kmeans_model.joblib'))
        
        # Save Random Forest
        joblib.dump(self.rf_classifier, os.path.join(self.models_dir, 'rf_best_model.joblib'))
        
        # Save preprocessors
        joblib.dump(self.scaler, os.path.join(self.models_dir, 'scaler.joblib'))
        joblib.dump(self.label_encoders, os.path.join(self.models_dir, 'label_encoders.joblib'))
        
        # Save metadata
        metadata = {
            'feature_names': self.feature_names,
            'cluster_profiles': self.cluster_profiles,
            'model_metrics': self.model_metrics,
            'timestamp': timestamp
        }
        joblib.dump(metadata, os.path.join(self.models_dir, 'metadata.joblib'))
        
        print(f"\nModels saved to {self.models_dir}")
    
    def load_models(self):
        """Load trained models from disk"""
        try:
            self.kmeans_model = joblib.load(os.path.join(self.models_dir, 'kmeans_model.joblib'))
            self.rf_classifier = joblib.load(os.path.join(self.models_dir, 'rf_best_model.joblib'))
            self.scaler = joblib.load(os.path.join(self.models_dir, 'scaler.joblib'))
            self.label_encoders = joblib.load(os.path.join(self.models_dir, 'label_encoders.joblib'))
            
            metadata = joblib.load(os.path.join(self.models_dir, 'metadata.joblib'))
            self.feature_names = metadata['feature_names']
            self.cluster_profiles = metadata['cluster_profiles']
            self.model_metrics = metadata['model_metrics']
            
            self.is_trained = True
            print("Models loaded successfully")
            
        except Exception as e:
            print(f"Error loading models: {str(e)}")
            raise
    
    def get_model_info(self):
        """Get information about trained models"""
        if not self.is_trained:
            return {'status': 'not_trained'}
        
        return {
            'status': 'trained',
            'metrics': self.model_metrics,
            'cluster_profiles': self.cluster_profiles,
            'features': {
                'numeric': self.numeric_features,
                'categorical': self.categorical_features,
                'total': len(self.feature_names)
            }
        }
