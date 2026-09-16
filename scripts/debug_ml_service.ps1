# PowerShell script untuk debug ML service container yang gagal

Write-Host "🔍 Debugging ML Service Container" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan

# Check if container exists
$containerExists = docker ps -a --format "{{.Names}}" | Select-String "cismart_ml_service"

if (-not $containerExists) {
    Write-Host "❌ Container cismart_ml_service tidak ditemukan" -ForegroundColor Red
    Write-Host "   Jalankan: docker compose up -d --build" -ForegroundColor Yellow
    exit 1
}

# Get container status
Write-Host "`n📊 Container Status:" -ForegroundColor Green
docker ps -a | Select-String "cismart_ml_service"

# Check if container is running
$containerRunning = docker ps --format "{{.Names}}" | Select-String "cismart_ml_service"

if ($containerRunning) {
    Write-Host "`n✅ Container running" -ForegroundColor Green
    
    # Test health endpoint
    Write-Host "`n🏥 Testing health endpoint..." -ForegroundColor Yellow
    docker exec cismart_ml_service curl -f http://localhost:5000/health
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Health check failed" -ForegroundColor Red
    }
    
    # Show last 50 lines of logs
    Write-Host "`n📋 Last 50 lines of logs:" -ForegroundColor Yellow
    docker logs --tail 50 cismart_ml_service
    
    # Test from inside container
    Write-Host "`n🧪 Testing Python imports..." -ForegroundColor Yellow
    docker exec cismart_ml_service python -c "import flask; import sklearn; print('✅ Imports OK')"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Import failed" -ForegroundColor Red
    }
    
    # Check gunicorn process
    Write-Host "`n🔧 Checking gunicorn process..." -ForegroundColor Yellow
    docker exec cismart_ml_service ps aux
    
} else {
    Write-Host "`n❌ Container not running" -ForegroundColor Red
    
    # Show full logs
    Write-Host "`n📋 Full logs:" -ForegroundColor Yellow
    docker logs cismart_ml_service
    
    # Try to start container
    Write-Host "`n🔄 Attempting to start container..." -ForegroundColor Yellow
    docker start cismart_ml_service
    
    Start-Sleep -Seconds 5
    
    # Show logs after start attempt
    Write-Host "`n📋 Logs after start attempt:" -ForegroundColor Yellow
    docker logs --tail 30 cismart_ml_service
}

Write-Host "`n==================================" -ForegroundColor Cyan
Write-Host "🛠️  Troubleshooting Tips:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Rebuild container:" -ForegroundColor White
Write-Host "   docker compose down" -ForegroundColor Gray
Write-Host "   docker compose up -d --build ml_service" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Check requirements.txt:" -ForegroundColor White
Write-Host "   cat ml_service/requirements.txt" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Test locally without Docker:" -ForegroundColor White
Write-Host "   cd ml_service" -ForegroundColor Gray
Write-Host "   pip install -r requirements.txt" -ForegroundColor Gray
Write-Host "   python app.py" -ForegroundColor Gray
Write-Host ""
Write-Host "4. View real-time logs:" -ForegroundColor White
Write-Host "   docker compose logs -f ml_service" -ForegroundColor Gray
