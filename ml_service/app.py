"""
CISMART ML Service - UMKM Growth Forecasting & Stratification System
Penelitian: PDP Dosen Pemula - 166PL43AL.04 2025
"""

from flask import Flask, request, jsonify, Response, send_from_directory
from flask_cors import CORS
from functools import wraps
import glob
import os
import threading
import traceback
import uuid
from datetime import datetime
from werkzeug.utils import secure_filename
import pandas as pd
import numpy as np
import joblib
import time
from ml_engine import UMKMClusteringEngine
from validators import validate_umkm_input, validate_batch_input
from prometheus_client import Counter, Histogram, generate_latest, CONTENT_TYPE_LATEST

import logging
from pythonjsonlogger import jsonlogger

app = Flask(__name__)

# Token internal: hanya Laravel (X-Internal-Token) yang boleh memicu training
# atau prediksi. Bila INTERNAL_TOKEN kosong, endpoint tetap terbuka (mode dev)
# tetapi menulis peringatan.
INTERNAL_TOKEN = os.environ.get('INTERNAL_TOKEN', '').strip()
CORS_ORIGINS = [o.strip() for o in os.environ.get('CORS_ORIGINS', '').split(',') if o.strip()]
CORS(app, origins=CORS_ORIGINS or ['http://localhost:8000', 'http://127.0.0.1:8000'])

MAX_KEPT_TRAINING_FILES = int(os.environ.get('ML_MAX_TRAINING_FILES', '10'))

if not INTERNAL_TOKEN:
    app.logger.warning('INTERNAL_TOKEN kosong: endpoint ML terbuka tanpa autentikasi. Set INTERNAL_TOKEN untuk produksi.')


def require_token(fn):
    """Wajib header X-Internal-Token bila INTERNAL_TOKEN dikonfigurasi."""
    @wraps(fn)
    def wrapper(*args, **kwargs):
        if INTERNAL_TOKEN and request.headers.get('X-Internal-Token') != INTERNAL_TOKEN:
            return jsonify({'error': 'Unauthorized'}), 401
        return fn(*args, **kwargs)
    return wrapper


def cleanup_training_files():
    """Simpan hanya N file training terakhir (volume ml_data tidak tumbuh tanpa batas)."""
    files = sorted(glob.glob(os.path.join(DATA_DIR, 'training_data_*')))
    for path in files[:-MAX_KEPT_TRAINING_FILES]:
        try:
            os.remove(path)
        except OSError as exc:  # pragma: no cover
            app.logger.warning(f'Gagal menghapus file training lama {path}: {exc}')

# Setup structured JSON logging
handler = logging.StreamHandler()
formatter = jsonlogger.JsonFormatter(
    fmt='%(asctime)s %(name)s %(levelname)s %(message)s',
    datefmt='%Y-%m-%dT%H:%M:%S'
)
handler.setFormatter(formatter)

app.logger.handlers.clear()
app.logger.addHandler(handler)
app.logger.setLevel(logging.DEBUG)

# Prometheus Metrics
REQUEST_COUNT = Counter(
    'http_requests_total', 'Total HTTP requests',
    ['method', 'endpoint', 'http_status']
)
REQUEST_LATENCY = Histogram(
    'http_request_duration_seconds', 'HTTP request duration',
    ['method', 'endpoint']
)

@app.before_request
def before_request():
    request.start_time = time.time()

@app.after_request
def after_request(response):
    if hasattr(request, 'start_time'):
        latency = time.time() - request.start_time
        # Use request.url_rule to get the parameterized route if available
        endpoint = request.url_rule.rule if request.url_rule else request.path
        REQUEST_LATENCY.labels(request.method, endpoint).observe(latency)
        REQUEST_COUNT.labels(request.method, endpoint, response.status_code).inc()
    return response

@app.route('/metrics', methods=['GET'])
def metrics():
    return Response(generate_latest(), mimetype=CONTENT_TYPE_LATEST)

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
@require_token
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
        file_ext = os.path.splitext(file.filename)[1].lstrip('.').lower()
        if file_ext not in allowed_extensions:
            return jsonify({'error': f'Invalid file type. Allowed: {allowed_extensions}'}), 400
        
        # Save uploaded file
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f'training_data_{timestamp}.{file_ext}'
        filepath = os.path.join(DATA_DIR, filename)
        file.save(filepath)

        cleanup_training_files()

        # Training berjalan di thread terpisah: request tidak menunggu 5 menit
        # (gunicorn timeout) dan pemanggil bisa memantau progres.
        job_id = uuid.uuid4().hex
        TRAINING_JOBS[job_id] = {
            'status': 'running',
            'file': filename,
            'started_at': datetime.now().isoformat(),
        }
        app.logger.info(f"Training job {job_id} started with file: {filename}")
        threading.Thread(target=_run_training_job, args=(job_id, filepath), daemon=True).start()

        return jsonify({
            'status': 'accepted',
            'message': 'Training started',
            'job_id': job_id,
            'status_url': f'/api/train/{job_id}',
            'timestamp': datetime.now().isoformat()
        }), 202
        
    except Exception as e:
        app.logger.error(f"Training error: {str(e)}\n{traceback.format_exc()}")
        return jsonify({
            'error': 'Training failed',
            'message': str(e)
        }), 500

TRAINING_JOBS = {}
TRAINING_LOCK = threading.Lock()


def _run_training_job(job_id, filepath):
    """Jalankan training dengan lock agar dua job tidak menimpa model bersamaan."""
    with TRAINING_LOCK:
        try:
            result = ml_engine.train(filepath)
            TRAINING_JOBS[job_id].update({
                'status': 'completed',
                'result': result,
                'finished_at': datetime.now().isoformat(),
            })
        except Exception as e:  # pragma: no cover - dilaporkan ke pemanggil
            app.logger.error(f"Training job {job_id} failed: {e}\n{traceback.format_exc()}")
            TRAINING_JOBS[job_id].update({
                'status': 'failed',
                'error': str(e),
                'finished_at': datetime.now().isoformat(),
            })


@app.route('/api/train/<job_id>', methods=['GET'])
@require_token
def training_status(job_id):
    """Status job training yang dijalankan lewat POST /api/train."""
    job = TRAINING_JOBS.get(job_id)

    if not job:
        return jsonify({'error': 'Job not found'}), 404

    return jsonify({'job_id': job_id, **job}), 200


@app.route('/api/predict', methods=['POST'])
@require_token
def predict_cluster():
    """
    Predict cluster for single UMKM
    Expects: JSON with UMKM features
    """
    try:
        # Worker lain mungkin sudah menerima model hasil training terbaru:
        # muat dari disk bila file model tersedia tapi memori belum terisi.
        if ml_engine and not ml_engine.is_trained and os.path.exists(f'{MODELS_DIR}/rf_best_model.joblib'):
            try:
                ml_engine.load_models()
            except Exception as exc:  # pragma: no cover - tergantung isi disk
                app.logger.warning(f'Gagal memuat model dari disk: {exc}')

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
@require_token
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
        file_ext = os.path.splitext(file.filename)[1].lstrip('.').lower()
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
@require_token
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
@require_token
def download_file(filename):
    """Download result files"""
    try:
        safe_name = secure_filename(filename)
        filepath = os.path.join(DATA_DIR, safe_name)

        if not os.path.isfile(filepath):
            return jsonify({'error': 'File not found'}), 404

        return send_from_directory(DATA_DIR, safe_name, as_attachment=True)
        
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
