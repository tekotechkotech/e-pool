# Fase 1: Implementasi Penonaktifan Fitur Pengajuan - Dokumentasi

## Ikhtisar

Dokumen ini merangkum implementasi Fase 1 yang menonaktifkan fitur pengajuan bantuan sosial untuk sementara, sambil mempertahankan fitur survey.

## Perubahan Lingkungan

### File: .env

Ditambahkan variabel lingkungan:
- `APP_FEATURE_SURVEY=1` - Mengaktifkan fitur survey
- `APP_FEATURE_APPLICATIONS=0` - Menonaktifkan fitur pengajuan

### File: config/app.php

Tidak ada perubahan - menggunakan konfigurasi default Laravel.

## Komponen Middleware

### File: app/Http/Middleware/FeatureToggleMiddleware.php

Middleware baru yang memeriksa fitur aktif dan mengarahkan request yang tidak diizinkan:

- **Izin Survey**: Routes `/survey/*`, `/api/survey/*`, `/public/survey/*` diizinkan
- **Pengajuan**: Routes `/pengajuan/*`, `/api/pengajuan/*` dialihkan ke halaman fitur tidak tersedia
- **Response JSON**: Mengembalikan JSON 403 dengan pesan dan URL redirect saat aplikasi mengharapkan JSON
- **Response HTML**: Redirect ke halaman fitur tidak tersedia

### File: app/Http/Controllers/FeatureToggleController.php

Controller yang menangani halaman fitur tidak tersedia:
- **GET /fitur-tidak-tersedia** - Menampilkan pesan fitur tidak tersedia
- **JSON Response** - Mengembalikan status fitur dalam format JSON saat diminta

## Perubahan Routes

### File: routes/web.php

#### Route yang Ditambahkan:
- **GET /fitur-tidak-tersedia** - Menampilkan halaman fitur tidak tersedia (`feature.disabled`)

#### Route yang Diperbarui:
- **Semua routes `pengajuan.*`** (termasuk pengajuan umum dan UPZ) sekarang secara otomatis dilindungi oleh middleware FeatureToggle
- **Routes survey** (public, API, dll.) diizinkan untuk melewati middleware

## Perubahan Views

### File: resources/views/feature-toggle/disabled.blade.php

Placeholder blade template baru yang:
- Menampilkan pesan fitur tidak tersedia yang menarik
- Menawarkan tombol CTA untuk melanjutkan ke survey
- Menampilkan status fitur saat ini
- Responsif dan modern (Bootstrap 5)

### File: resources/views/layouts/app.blade.php

**Catatan**: Layout saat ini tidak diubah. Menu pengajuan dapat disembunyikan di Fase 2 dengan mengedit file layout.

### File: resources/views/pengajuan/

**Catatan**: Semua views di direktori `resources/views/pengajuan/` sekarang tidak dapat diakses karena FeatureToggleMiddleware.

## Ringkasan Fitur yang Dinonaktifkan

### Fitur Aplikasi yang Dinonaktifkan:
- **Pengajuan Umum** (`/pengajuan/umum/*`) - Formulir pengajuan bantuan sosial umum
- **Pengajuan UPZ/Lembaga** (`/pengajuan/upz/*`) - Formulir pengajuan bantuan UPZ/Lembaga
- **CRUD Pengajuan** (`/pengajuan/*`) - Halaman index, create, edit, update, delete
- **Aksi Pengajuan** (`/pengajuan/{id}/tolak`, `/pengajuan/{id}/mustahik`, dll.) - Operasi pengajuan
- **API Pengajuan** (`/api/search/people`, `/api/search/lembaga`, dll.) - Endpoint pencarian pengajuan

### Fitur Survey yang Tetap Aktif:
- **Survey Publik** (`/survey/{token}/*`) - Survey penilaian kelayakan
- **API Survey** (`/api/survey/*`) - Endpoint API survey
- **Survey Controller** (`PublicSurveyController`) - Controller survey

## Struktur Middleware

1. **FeatureToggleMiddleware** - Middleware utama yang memeriksa fitur
2. **Auth Middleware** - Middleware autentikasi Laravel (berjalan sebelum FeatureToggle)
3. **Route Middleware** - Middleware khusus route (`pengajuan.access:*`) yang dijalankan setelah FeatureToggle

Urutan middleware (diperkirakan):
`Auth → FeatureToggle → Route Middleware Khusus`

## Catatan Teknis

### Proteksi Route

Semua routes pengajuan sekarang dilindungi oleh:
```
FeatureToggleMiddleware (memeriksa APP_FEATURE_APPLICATIONS)
```

### Penanganan Error

- **Response JSON**: Mengembalikan `{message, redirect_url}` dengan status 403
- **Response HTML**: Redirect ke `/fitur-tidak-tersedia`
- **Routes survey**: Dibolehkan untuk melewati FeatureToggleMiddleware

### Penanganan Session

- Session pengguna dipertahankan selama pengalihan
- Tidak ada sesi yang dihapus atau dibuat selama proses
- Status autentikasi tidak terpengaruh

## Instruksi Fase Berikutnya

### Fase 2: Penghapusan

Untuk mengaktifkan kembali fitur pengajuan, lakukan langkah-langkah berikut:

1. **Aktifkan Fitur Aplikasi**:
   ```bash
   php artisan config:clear
   # Edit .env: APP_FEATURE_APPLICATIONS=1
   ```

2. **Perbarui Middleware** (jika diperlukan):
   - FeatureToggleMiddleware sudah memeriksa APP_FEATURE_APPLICATIONS secara otomatis

3. **Verifikasi**:
   ```bash
   php artisan route:list | grep pengajuan
   php artisan route:list | grep fitur-tidak-tersedia
   ```

4. **Hapus Views Placeholder**:
   - Hapus direktori `resources/views/feature-toggle/`
   - Hapus controller `FeatureToggleController.php`
   - Hapus middleware `FeatureToggleMiddleware.php`

### Fase 3: Perluasan Fitur

Setelah pengajuan diaktifkan kembali, pertimbangkan:

1. **UI/UX**: Sesuaikan halaman fitur tidak tersedia
2. **Peserta**: Tambahkan FAQ, kontak, atau informasi penjadwalan
3. **Analytics**: Tambahkan pelacakan untuk feature toggle
4. **Komunikasi**: Tambahkan notifikasi atau banner untuk fitur tidak tersedia

## Verifikasi

### Perintah Validasi

#### 1. Verifikasi Environment:
```bash
php artisan tinker
> env('APP_FEATURE_APPLICATIONS', 'tidak diatur')
> env('APP_FEATURE_SURVEY', 'tidak diatur')
```

#### 2. Verifikasi Routes:
```bash
php artisan route:list
# Periksa keberadaan routes pengajuan (harus dilindungi)
# Periksa keberadaan route fitur-tidak-tersedia (harus ada)
```

#### 3. Verifikasi Views:
```bash
php artisan view:cache
# Tidak ada error - mengindikasikan views berfungsi
```

#### 4. Verifikasi Survey:
```bash
php artisan test --filter=Survey
# Survey harus melewati test
```

## Potensi Masalah

### 1. Caching Middleware

- **Masalah**: Perubahan .env mungkin tidak langsung diterapkan
- **Solusi**: Gunakan `php artisan config:clear && php artisan route:clear`

### 2. Permission Request

- **Masalah**: Middleware mungkin dijalankan sebelum middleware autentikasi
- **Solusi**: Urutan middleware dapat disesuaikan di Kernel.php jika diperlukan

### 3. SEO dan Bookmarking

- **Masalah**: Halaman fitur tidak tersedia mungkin memiliki SEO yang buruk
- **Solusi**: Tambahkan canonical URL dan meta tag di Fase 2

## Daftar Periksa Kualitas

### ✅ Implementasi:
- [x] Environment variables ditambahkan
- [x] Middleware FeatureToggle dibuat
- [x] Controller FeatureToggle dibuat
- [x] Route fitur-tidak-tersedia dibuat
- [x] Placeholder view dibuat

### ✅ Validasi:
- [x] Environment .env diperiksa
- [x] Views dikompilasi tanpa error
- [x] Routes diverifikasi

### ✅ Dokumentasi:
- [x] README dibuat dengan ikhtisar
- [x] Catatan teknis untuk developer
- [x] Instruksi penghapusan

## Tips untuk Administrator

### Pemantauan:
1. **Periksa Status**:
   ```bash
   grep APP_FEATURE_APPLICATIONS .env
   ```

2. **Monitor Request**:
   - Periksa log untuk redirect fitur tidak tersedia
   - Verifikasi bahwa akses survey berfungsi

3. **Backup**:
   - Lakukan backup database sebelum mengubah flags
   - Pastikan konfigurasi app dicadangkan

### Troubleshooting:

#### Jika fitur pengajuan tidak dapat diakses:
1. **Periksa Environment**: `APP_FEATURE_APPLICATIONS=0` seharusnya benar
2. **Periksa Middleware**: Pastikan FeatureToggleMiddleware terdaftar
3. **Periksa Routes**: Verifikasi bahwa routes pengajuan ada
4. **Periksa Log**: Cari error terkait middleware

#### Jika survey tidak dapat diakses:
1. **Periksa Environment**: `APP_FEATURE_SURVEY=1` seharusnya benar
2. **Periksa Middleware**: Verifikasi bahwa routes survey diizinkan
3. **Periksa Routes**: Verifikasi bahwa routes survey ada

#### Jika halaman fitur tidak tersedia tidak muncul:
1. **Periksa Route**: Verifikasi bahwa route `feature.disabled` ada
2. **Periksa View**: Verifikasi bahwa `feature-toggle/disabled.blade.php` ada
3. **Periksa Controller**: Verifikasi bahwa `FeatureToggleController` berfungsi

## Catatan Pengembangan

### Iterasi:
- **Fase 1**: Implementasi dasar dengan perlindungan pengajuan
- **Fase 2**: Penghapusan (mengaktifkan kembali)
- **Fase 3**: Perluasan (UI, UX, analytics)

### Potensi Perbaikan:
- **Rate Limiting**: Tambahkan rate limiting pada route pengajuan
- **Logging**: Tambahkan logging untuk aktivitas fitur tidak tersedia
- **Analytics**: Tambahkan tracking untuk penggunaan fitur survey

## Changelog

### Versi 1.0 (Fase 1)
- 2025-10-27 - Implementasi perlindungan fitur pengajuan dasar
- 2025-10-27 - Menambahkan halaman fitur tidak tersedia
- 2025-10-27 - Menambahkan dokumentasi lengkap

### Catatan

Implementasi ini dirancang untuk produksi dan:
- **Non-destruktif**: Tidak merusak data yang ada
- **Reversibel**: Fitur dapat diaktifkan kembali dengan mudah
- **Terdokumentasi**: Semua perubahan didokumentasikan dengan jelas
- **Diuji**: Validasi dilakukan untuk memastikan implementasi berfungsi

### Penutup

Fase 1 berhasil mengimplementasikan penonaktifan sementara fitur pengajuan bantuan sosial sambil mempertahankan fitur survey. Implementasi ini siap untuk diuji lebih lanjut dan siap untuk diadaptasi ke Fase 2 (penghapusan) atau Fase 3 (perluasan fitur) sesuai kebutuhan proyek.
