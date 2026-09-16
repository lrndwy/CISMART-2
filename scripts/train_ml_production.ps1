# PowerShell Script untuk manual training ML model di production
# Gunakan jika automatic training di CI/CD gagal

Write-Host "🤖 Manual ML Model Training for Production" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Check if running in correct directory
if (-not (Test-Path "docker-compose.yml")) {
    Write-Host "❌ Error: docker-compose.yml not found!" -ForegroundColor Red
    Write-Host "   Please run this script from the project root directory" -ForegroundColor Yellow
    exit 1
}

# Check if ML service container is running
$mlContainer = docker ps --format "{{.Names}}" | Select-String "cismart_ml_service"
if (-not $mlContainer) {
    Write-Host "❌ Error: ML service container not running!" -ForegroundColor Red
    Write-Host "   Start containers: docker compose up -d" -ForegroundColor Yellow
    exit 1
}

# Check if training data exists
$trainingFile = "ml_service\combined_training_data.xlsx"
if (-not (Test-Path $trainingFile)) {
    Write-Host "❌ Error: Training data not found: $trainingFile" -ForegroundColor Red
    Write-Host "   Please ensure the training data file exists" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Checks passed. Starting training..." -ForegroundColor Green
Write-Host ""

# Copy training data to container
Write-Host "📂 Copying training data to container..." -ForegroundColor Yellow
docker cp $trainingFile cismart_ml_service:/app/combined_training_data.xlsx
Write-Host "✅ File copied" -ForegroundColor Green
Write-Host ""

# Train model
Write-Host "🔄 Training model (this may take 30-60 seconds)..." -ForegroundColor Yellow
Write-Host "   Dataset: 7,548 rows" -ForegroundColor Gray
Write-Host "   Features: 19 (11 numeric + 8 categorical)" -ForegroundColor Gray
Write-Host ""

$trainingResult = docker exec cismart_ml_service bash -c @"
    curl -X POST \
      -F 'file=@/app/combined_training_data.xlsx' \
      http://localhost:5000/api/train \
      --max-time 300 \
      --silent \
      --show-error \
      --write-out '\nHTTP Status: %{http_code}\n'
"@

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Training completed!" -ForegroundColor Green
    Write-Host ""
    
    # Verify model status
    Write-Host "🔍 Verifying model..." -ForegroundColor Yellow
    docker exec cismart_ml_service curl -s http://localhost:5000/api/model-info
    
    Write-Host ""
    Write-Host "📊 Get full model info:" -ForegroundColor Cyan
    Write-Host "   curl http://localhost:5000/api/model-info" -ForegroundColor Gray
    Write-Host ""
    Write-Host "✅ Done! ML model is ready for predictions." -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "❌ Training failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "📋 Check ML service logs:" -ForegroundColor Yellow
    docker compose logs --tail 50 ml_service
    exit 1
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "✅ Manual training completed successfully" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
