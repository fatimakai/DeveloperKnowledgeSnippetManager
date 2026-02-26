#!/bin/bash

# Developer Knowledge Snippet Manager - Explain Feature Setup Script
# This script completes the setup for the "Explain This Code" feature

echo "======================================"
echo "Explain Feature - Final Setup"
echo "======================================"
echo ""

# Step 1: Run migrations
echo "Step 1: Running database migrations..."
echo "This will create the snippet_explanations table"
docker compose exec laravel.test php artisan migrate

if [ $? -ne 0 ]; then
    echo "❌ Migration failed. Check logs for details."
    exit 1
fi
echo "✅ Database migration completed"
echo ""

# Step 2: Clear caches
echo "Step 2: Clearing application caches..."
docker compose exec laravel.test php artisan cache:clear
docker compose exec laravel.test php artisan view:clear
docker compose exec laravel.test php artisan config:cache
echo "✅ Caches cleared"
echo ""

# Step 3: Verify configuration
echo "Step 3: Verifying OpenRouter configuration..."
docker compose exec laravel.test php artisan tinker << 'EOF'
echo "OpenRouter API Key: " . (config('services.openrouter.api_key') ? '✅ Set' : '❌ Missing');
echo "\nOpenRouter Model: " . config('services.openrouter.model');
echo "\nOpenRouter Base URL: " . config('services.openrouter.base_url');
exit;
EOF
echo ""

# Step 4: Start queue worker
echo "Step 4: Starting queue worker (in background)..."
echo "Note: Queue worker will run indefinitely. Press Ctrl+C to stop."
echo ""
docker compose exec laravel.test php artisan queue:work

