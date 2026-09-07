"""
CISMART ML Service - UMKM Growth Forecasting & Stratification System
Penelitian: PDP Dosen Pemula - 166PL43AL.04 2025
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
import joblib
import os
from datetime import datetime
import traceback
from ml_engine import UMKMClusteringEngine
from validators import validate_umkm_input, validate_batch_input

app = Flask(__name__)
CORS(app)

# Configuration
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16MB max file size
MODELS_DIR = '/app/models'
DATA_DIR = '/app/data'
LOGS_DIR = '/app/logs'

# Initialize ML Engine
ml_engine = None

def init_ml_engine():
    """Initialize ML engine on startup"""
    global ml_engine
    try:
        ml_engine = UMKMClusteringEngine(models_dir=MODELS_DIR, data_dir=DATA_DIR)
        
        # Load pre-trained models if available
        if os.path.exists(f'{MODELS_DIR}/rf_best_model.joblib'):
            ml_engine.load_models()
            app.logger.info("Pre-trained models loaded successfully")
        else:
            app.logger.warning("No pre-trained models found. Train models first.")
    except Exception as e:
        app.logger.error(f"Failed to initialize ML engine: {str(e)}")
        ml_engine = UMKMClusteringEngine(models_dir=MODELS_DIR, data_dir=DATA_DIR)

@app.before_request
def before_first_request():
    """Initialize on first request"""
    global ml_engine
    if ml_engine is None:
        init_ml_engine()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'CISMART ML Service',
        'timestamp': datetime.now().isoformat(),
        'models_loaded': ml_engine.is_trained if ml_engine else False
    }), 200

@app.route('/api/info', methods=['GET'])
def get_info():
    """Get service information"""
    return jsonify({
        'service': 'UMKM Growth Forecasting & Stratification System',
        'version': '1.0.0',
        'research': 'PDP Dosen Pemula - 166PL43AL.04 2025',
        'models': {
            'clustering': ['KMeans', 'FCM', 'GMM', 'DBSCAN', 'K-Prototypes'],
            'classification': 'Random Forest Classifier'
        },
        'endpoints': {
            'health': 'GET /health',
            'info': 'GET /api/info',
            'train': 'POST /api/train',
            'predict': 'POST /api/predict',
            'predict_batch': 'POST /api/predict-batch',
            'model_info': 'GET /api/model-info'
        }
    }), 200

@app.route('/api/train', methods=['POST'])
def train_model():
    """
    Train clustering and prediction models
    Expects: Excel/CSV file upload with UMKM data
    """
    try:
        if 'file' not in request.files:
            return jsonify({'error': 'No file provided'}), 400
        
        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'Empty filename'}), 400
        
        # Validate file extension
        allowed_extensions = {'xlsx', 'xls', 'csv'}
        file_ext = file.filename.rsplit('.', 1)[1].lower()
        if file_ext not in allowed_extensions:
            return jsonify({'error': f'Invalid file type. Allowed: {allowed_extensions}'}), 400
        
        # Save uploaded file
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f'training_data_{timestamp}.{file_ext}'
        filepath = os.path.join(DATA_DIR, filename)
        file.save(filepath)
        
        # Train models
        app.logger.info(f"Starting training with file: {filename}")
        result = ml_engine.train(filepath)
        
        return jsonify({
            'status': 'success',
            'message': 'Models trained successfully',
            'result': result,
            'timestamp': datetime.now().isoformat()
        }), 200
        
    except Exception as e:
        app.logger.error(f"Training error: {str(e)}\n{traceback.format_exc()}")
        return jsonify({
            'error': 'Training failed',
            'message': str(e)
        }), 500

@app.route('/api/predict', methods=['POST'])
def predict_cluster():
    """
    Predict cluster for single UMKM
    Expects: JSON with UMKM features
    """
    try:
        if not ml_engine or not ml_engine.is_trained:
            return jsonify({'error': 'Models not trained yet'}), 400
        
        # Get JSON data
        data = request.get_json()
        if not data:
            return jsonify({'error': 'No data provided'}), 400
        
        # Validate input
        is_valid, errors = validate_umkm_input(data)
        if not is_valid:
            return jsonify({'error': 'Invalid input', 'details': errors}), 400
        
        # Predict
        result = ml_engine.predict_single(data)
        
        return jsonify({
            'status': 'success',
            'prediction': result,
            'timestamp': datetime.now().isoformat()
        }), 200
        
    except Exception as e:
        app.logger.error(f"Prediction error: {str(e)}\n{traceback.format_exc()}")
        return jsonify({
            'error': 'Prediction failed',
            'message': str(e)
        }), 500

@app.route('/api/predict-batch', methods=['POST'])
def predict_batch():
    """
    Predict clusters for multiple UMKMs
    Expects: Excel/CSV file upload
    Returns: CSV file with predictions
    """
    try:
        if not ml_engine or not ml_engine.is_trained:
            return jsonify({'error': 'Models not trained yet'}), 400
        
        if 'file' not in request.files:
            return jsonify({'error': 'No file provided'}), 400
        
        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'Empty filename'}), 400
        
        # Validate file
        allowed_extensions = {'xlsx', 'xls', 'csv'}
        file_ext = file.filename.rsplit('.', 1)[1].lower()
        if file_ext not in allowed_extensions:
            return jsonify({'error': f'Invalid file type. Allowed: {allowed_extensions}'}), 400
        
        # Save and process
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f'batch_input_{timestamp}.{file_ext}'
        filepath = os.path.join(DATA_DIR, filename)
        file.save(filepath)
        
        # Predict batch
        result_df = ml_engine.predict_batch(filepath)
        
        # Save result
        output_filename = f'batch_predictions_{timestamp}.csv'
        output_path = os.path.join(DATA_DIR, output_filename)
        result_df.to_csv(output_path, index=False)
        
        return jsonify({
            'status': 'success',
            'message': f'Processed {len(result_df)} records',
            'output_file': output_filename,
            'download_url': f'/api/download/{output_filename}',
            'preview': result_df.head(10).to_dict('records'),
            'timestamp': datetime.now().isoformat()
        }), 200
        
    except Exception as e:
        app.logger.error(f"Batch prediction error: {str(e)}\n{traceback.format_exc()}")
        return jsonify({
            'error': 'Batch prediction failed',
            'message': str(e)
        }), 500

@app.route('/api/model-info', methods=['GET'])
def get_model_info():
    """Get trained model information"""
    try:
        if not ml_engine or not ml_engine.is_trained:
            return jsonify({
                'status': 'not_trained',
                'message': 'Models not trained yet'
            }), 200
        
        info = ml_engine.get_model_info()
        return jsonify({
            'status': 'success',
            'model_info': info,
            'timestamp': datetime.now().isoformat()
        }), 200
        
    except Exception as e:
        app.logger.error(f"Model info error: {str(e)}")
        return jsonify({
            'error': 'Failed to get model info',
            'message': str(e)
        }), 500

@app.route('/api/download/<filename>', methods=['GET'])
def download_file(filename):
    """Download result files"""
    try:
        filepath = os.path.join(DATA_DIR, filename)
        if not os.path.exists(filepath):
            return jsonify({'error': 'File not found'}), 404
        
        from flask import send_file
        return send_file(filepath, as_attachment=True)
        
    except Exception as e:
        app.logger.error(f"Download error: {str(e)}")
        return jsonify({
            'error': 'Download failed',
            'message': str(e)
        }), 500

@app.errorhandler(413)
def request_entity_too_large(error):
    """Handle file too large error"""
    return jsonify({
        'error': 'File too large',
        'message': 'Maximum file size is 16MB'
    }), 413

@app.errorhandler(404)
def not_found(error):
    """Handle 404 errors"""
    return jsonify({
        'error': 'Endpoint not found',
        'message': 'The requested endpoint does not exist'
    }), 404

@app.errorhandler(500)
def internal_error(error):
    """Handle 500 errors"""
    app.logger.error(f"Internal server error: {str(error)}")
    return jsonify({
        'error': 'Internal server error',
        'message': 'An unexpected error occurred'
    }), 500

if __name__ == '__main__':
    # Create directories
    os.makedirs(MODELS_DIR, exist_ok=True)
    os.makedirs(DATA_DIR, exist_ok=True)
    os.makedirs(LOGS_DIR, exist_ok=True)
    
    # Initialize ML engine
    init_ml_engine()
    
    # Run Flask app
    app.run(host='0.0.0.0', port=5000, debug=False)
