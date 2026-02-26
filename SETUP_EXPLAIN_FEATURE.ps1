# Developer Knowledge Snippet Manager - Explain Feature Setup Script (Windows)
# This script completes the setup for the "Explain This Code" feature

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Explain Feature - Final Setup" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Run migrations
Write-Host "Step 1: Running database migrations..." -ForegroundColor Yellow
Write-Host "This will create the snippet_explanations table"
docker compose exec laravel.test php artisan migrate

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Migration failed. Check logs for details." -ForegroundColor Red
    exit 1
}
Write-Host "✅ Database migration completed" -ForegroundColor Green
Write-Host ""

# Step 2: Clear caches
Write-Host "Step 2: Clearing application caches..." -ForegroundColor Yellow
docker compose exec laravel.test php artisan cache:clear
docker compose exec laravel.test php artisan view:clear
docker compose exec laravel.test php artisan config:cache
Write-Host "✅ Caches cleared" -ForegroundColor Green
Write-Host ""

# Step 3: Verify configuration
Write-Host "Step 3: Verifying OpenRouter configuration..." -ForegroundColor Yellow
$config = docker compose exec laravel.test php artisan tinker <<'EOF'
echo "OpenRouter API Key: " . (config('services.openrouter.api_key') ? '✅ Set' : '❌ Missing');
echo "\nOpenRouter Model: " . config('services.openrouter.model');
echo "\nOpenRouter Base URL: " . config('services.openrouter.base_url');
exit;
EOF
Write-Host $config -ForegroundColor White
Write-Host ""

# Step 4: Start queue worker
Write-Host "Step 4: Starting queue worker..." -ForegroundColor Yellow
Write-Host "Queue worker will process explanation requests. Keep running in background." -ForegroundColor Cyan
Write-Host ""
docker compose exec laravel.test php artisan queue:work

