# Perintah Backup Fase 1

## 1. Backup Data Penting

### 1.1 Export Survey Baseline
```bash
php artisan db:export --include=pengajuan_surveys,surveys,persons --filename=backup_surveys_$(date +%Y%m%d_%H%M%S).json
```

### 1.2 Backup Seeders yang Relevan
```bash
php artisan db:dump --include=lembagas,muzakkis,setoran_upzs,dana_transaksis --filename=backup_seeders_$(date +%Y%m%d_%H%M%S).sql
```

## 2. Validasi Non-Aktifitas

### 2.1 Verifikasi Route List
```bash
php artisan route:list | grep -E "(pengajuan|feature.disabled)" | head -20
```

### 2.2 Verifikasi View Cache
```bash
php artisan view:cache
```

### 2.3 Verifikasi Survey
```bash
php artisan test --filter=Survey
```

## 3. Tampilan Hasil

### 3.1 Daftar Route
```bash
php artisan route:list --group web | grep -E "(pengajuan|feature.disabled)" | column -t -s ' '
```

### 3.2 File Environment
```bash
cat .env | grep -E "(APP_FEATURE|APP_ENV)"
```

## 4. Checklist Verifikasi

### Checklist Backend:
- [ ] Environment variables APP_FEATURE_SURVEY dan APP_FEATURE_APPLICATIONS diatur dengan benar
- [ ] Middleware FeatureToggleMiddleware sudah terdaftar
- [ ] Route /fitur-tidak-tersedia berfungsi
- [ ] Views di-cached tanpa error
- [ ] Survey routes masih dapat diakses

### Checklist Frontend:
- [ ] Halaman fitur tidak tersedia dapat diakses di /fitur-tidak-tersedia
- [ ] Tombol CTA survey berfungsi
- [ ] Tidak ada error browser saat mengakses routes pengajuan

## 5. Perintah Admin untuk Pemantauan

### 5.1 Log Middleware
```bash
grep -r "Fitur pengajuan bantuan sementara tidak tersedia" storage/logs/laravel.log
```

### 5.2 Route yang Diizinkan
```bash
php artisan route:list --group web | grep -E "(survey|public.survey)" | column -t -s ' '
```

## 6. Troubleshooting

### Masalah Umum:

#### Jika FeatureToggleMiddleware tidak bekerja:
1. Pastikan file middleware berada di `app/Http/Middleware/FeatureToggleMiddleware.php`
2. Pastikan middleware sudah terdaftar di config/app.php
3. Clear cache: `php artisan cache:clear`

#### Jika Route /fitur-tidak-tersedia tidak ada:
1. Verifikasi bahwa route ditambahkan di routes/web.php
2. Pastikan Controller FeatureToggleController dibuat
3. Clear route cache: `php artisan route:clear`

#### Jika Survey tidak dapat diakses:
1. Pastikan `APP_FEATURE_SURVEY=1` di .env
2. Verifikasi bahwa survey routes tidak terpengaruh oleh FeatureToggleMiddleware
3. Periksa middleware order

### Verifikasi Fast:
```bash
# 1. Check environment
[[ $(grep APP_FEATURE_APPLICATIONS .env) == "APP_FEATURE_APPLICATIONS=0" ]] && echo "✓ Applications disabled"
[[ $(grep APP_FEATURE_SURVEY .env) == "APP_FEATURE_SURVEY=1" ]] && echo "✓ Survey enabled"

# 2. Check routes
php artisan route:list | grep -q "feature.disabled" && echo "✓ Feature disabled route exists" || echo "✗ Feature disabled route missing"
php artisan route:list | grep -q "survey" && echo "✓ Survey routes exist" || echo "✗ Survey routes missing"

# 3. Check views
php artisan view:cache && echo "✓ Views compiled successfully" || echo "✗ View compilation failed"

# 4. Check middleware
php artisan tinker --execute="\App\Http\Middleware\FeatureToggleMiddleware::class" 2>/dev/null && echo "✓ FeatureToggleMiddleware exists" || echo "✗ FeatureToggleMiddleware missing"
```
