#!/bin/bash

# Test Script Fase 1: Fitur Pengajuan

echo "=== Test Fase 1: Fitur Pengajuan ==="

# Test 1: Verifikasi Environment Variables
echo "\n1. Testing Environment Variables..."
if grep -q "APP_FEATURE_APPLICATIONS=0" .env; then
    echo "✓ APP_FEATURE_APPLICATIONS=0 (Applications disabled)"
else
    echo "✗ APP_FEATURE_APPLICATIONS=0 tidak ditemukan di .env"
fi

if grep -q "APP_FEATURE_SURVEY=1" .env; then
    echo "✓ APP_FEATURE_SURVEY=1 (Survey enabled)"
else
    echo "✗ APP_FEATURE_SURVEY=1 tidak ditemukan di .env"
fi

# Test 2: Verifikasi File Middleware

echo "\n2. Testing Middleware Files..."
if [ -f "app/Http/Middleware/FeatureToggleMiddleware.php" ]; then
    echo "✓ FeatureToggleMiddleware.php exists"
else
    echo "✗ FeatureToggleMiddleware.php missing"
fi

if [ -f "app/Http/Controllers/FeatureToggleController.php" ]; then
    echo "✓ FeatureToggleController.php exists"
else
    echo "✗ FeatureToggleController.php missing"
fi

# Test 3: Verifikasi Routes

echo "\n3. Testing Routes..."
php artisan route:list 2>/dev/null | grep -q "feature.disabled" && echo "✓ Feature disabled route exists" || echo "✗ Feature disabled route missing"
php artisan route:list 2>/dev/null | grep -q "survey/{token}" && echo "✓ Survey route exists" || echo "✗ Survey route missing"

# Test 4: Verifikasi Views

echo "\n4. Testing Views..."
php artisan view:cache 2>/dev/null && echo "✓ Views compiled successfully" || echo "✗ View compilation failed"
if [ -f "resources/views/feature-toggle/disabled.blade.php" ]; then
    echo "✓ Feature toggle view exists"
else
    echo "✗ Feature toggle view missing"
fi

# Test 5: Verifikasi Structure Files

echo "\n5. Testing Project Structure..."
if [ -f "README_Phase1.md" ]; then
    echo "✓ README_Phase1.md exists"
else
    echo "✗ README_Phase1.md missing"
fi

if [ -f "backup_commands.md" ]; then
    echo "✓ backup_commands.md exists"
else
    echo "✗ backup_commands.md missing"
fi

# Test 6: Verifikasi Implementation

echo "\n6. Testing Implementation Features..."
# Check middleware pattern in routes
if grep -q "feature.toggle" routes/web.php; then
    echo "✓ Feature toggle middleware referenced in routes"
else
    echo "✗ Feature toggle middleware not referenced in routes"
fi

# Check feature disabled route
if grep -q "feature.disabled" routes/web.php; then
    echo "✓ Feature disabled route referenced in routes"
else
    echo "✗ Feature disabled route not referenced in routes"
fi

# Check middleware alias in bootstrap config
if grep -q "feature.toggle.*FeatureToggleMiddleware" bootstrap/app.php; then
    echo "✓ Middleware alias configured in bootstrap"
else
    echo "✗ Middleware alias not configured in bootstrap"
fi

echo "\n=== Tes Selesai ==="
