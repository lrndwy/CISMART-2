#!/bin/bash
# Script untuk debug ML service container yang gagal

echo "🔍 Debugging ML Service Container"
echo "=================================="

# Check if container exists
if ! docker ps -a | grep -q cismart_ml_service; then
    echo "❌ Container cismart_ml_service tidak ditemukan"
    echo "   Jalankan: docker compose up -d --build"
    exit 1
fi

# Get container status
echo ""
echo "📊 Container Status:"
docker ps -a | grep cismart_ml_service

# Check if container is running
if docker ps | grep -q cismart_ml_service; then
    echo ""
    echo "✅ Container running"
    
    # Test health endpoint
    echo ""
    echo "🏥 Testing health endpoint..."
    docker exec cismart_ml_service curl -f http://localhost:5000/health || echo "❌ Health check failed"
    
    # Show last 50 lines of logs
    echo ""
    echo "📋 Last 50 lines of logs:"
    docker logs --tail 50 cismart_ml_service
    
    # Test from inside container
    echo ""
    echo "🧪 Testing Python imports..."
    docker exec cismart_ml_service python -c "import flask; import sklearn; print('✅ Imports OK')" || echo "❌ Import failed"
    
    # Check gunicorn process
    echo ""
    echo "🔧 Checking gunicorn process..."
    docker exec cismart_ml_service ps aux | grep gunicorn || echo "❌ Gunicorn not running"
    
else
    echo ""
    echo "❌ Container not running"
    
    # Show full logs
    echo ""
    echo "📋 Full logs:"
    docker logs cismart_ml_service
    
    # Try to start container
    echo ""
    echo "🔄 Attempting to start container..."
    docker start cismart_ml_service
    
    sleep 5
    
    # Show logs after start attempt
    echo ""
    echo "📋 Logs after start attempt:"
    docker logs --tail 30 cismart_ml_service
fi

echo ""
echo "=================================="
echo "🛠️  Troubleshooting Tips:"
echo ""
echo "1. Rebuild container:"
echo "   docker compose down"
echo "   docker compose up -d --build ml_service"
echo ""
echo "2. Check requirements.txt:"
echo "   cat ml_service/requirements.txt"
echo ""
echo "3. Test locally without Docker:"
echo "   cd ml_service"
echo "   pip install -r requirements.txt"
echo "   python app.py"
echo ""
echo "4. View real-time logs:"
echo "   docker compose logs -f ml_service"
