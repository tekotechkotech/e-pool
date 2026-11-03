<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSurveyController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Master\DanaController;
use App\Http\Controllers\Master\BarangController;
use App\Http\Controllers\Master\ProgramController;
use App\Http\Controllers\Manajemen\PenggunaController;
use App\Http\Controllers\Manajemen\MustahikController;
use App\Http\Controllers\Manajemen\KeluargaController;
use App\Http\Controllers\Manajemen\BaznasController;
use App\Http\Controllers\Manajemen\UpzController;
use App\Http\Controllers\Manajemen\LembagaController;
use App\Http\Controllers\FeatureToggleController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\Muzakki\MuzakkiController;
use App\Http\Controllers\Pengumpulan\SetoranUpzController;
use App\Http\Controllers\Simba\SimbaExportController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Wilayah API Routes
Route::prefix('api/wilayah')->name('api.wilayah.')->group(function () {
    Route::get('/provinsi', [WilayahController::class, 'getProvinsi'])->name('provinsi');
    Route::get('/kabupaten/{id?}', [WilayahController::class, 'getKabupaten'])->name('kabupaten');
    Route::get('/kecamatan/{id?}', [WilayahController::class, 'getKecamatan'])->name('kecamatan');
    Route::get('/kelurahan/{id?}', [WilayahController::class, 'getKelurahan'])->name('kelurahan');
    Route::get('/kode-pos', [WilayahController::class, 'getKodePos'])->name('kode_pos');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.submit');
});

// Route Survey Publik (Bisa diakses tanpa login/auth oleh Surveyor)
Route::get('/survey/{token}', [PublicSurveyController::class, 'show'])->name('public.survey');
Route::post('/survey/{token}/surveyor', [PublicSurveyController::class, 'saveSurveyorInfo'])->name('public.survey.surveyor');
Route::post('/survey/{token}/had-kifayah', [PublicSurveyController::class, 'submitHadKifayah'])->name('public.survey.hadkifayah');
Route::post('/survey/{token}/asesmen', [PublicSurveyController::class, 'submitAsesmen'])->name('public.survey.asesmen');
Route::post('/survey/{token}', [PublicSurveyController::class, 'submitFinalSurvey'])->name('public.survey.submit');

// Authenticated Routes
Route::middleware(['auth', 'feature.toggle'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Halaman pilih jabatan
    Route::get('/select-jabatan', [AuthController::class, 'selectJabatan'])->name('select-jabatan');
    Route::post('/select-jabatan', [AuthController::class, 'updateJabatan'])->name('select-jabatan.update');
    Route::post('/select-jabatan/submit', [AuthController::class, 'updateJabatan'])->name('select-jabatan.post');

    // Dashboard & Profile
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Master Dana
    Route::get('/master/danas', [DanaController::class, 'index'])->name('master.danas');
    Route::post('/master/danas', [DanaController::class, 'store'])->name('master.danas.store');
    Route::delete('/master/danas/{id}', [DanaController::class, 'destroy'])->name('master.danas.destroy');
    Route::post('/master/danas/penerimaan', [DanaController::class, 'storePenerimaan'])->name('master.danas.penerimaan');
    Route::post('/master/danas/transaksi', [DanaController::class, 'storeTransaksi'])->name('master.danas.transaksi.store');
    Route::delete('/master/danas/transaksi/{id}', [DanaController::class, 'destroyTransaksi'])->name('master.danas.transaksi.destroy');

    // Master Barang & Pengadaan
    Route::get('/master/barangs', [BarangController::class, 'index'])->name('master.barangs');
    Route::post('/master/barangs', [BarangController::class, 'storeBarang'])->name('master.barangs.store');
    Route::delete('/master/barangs/{id}', [BarangController::class, 'destroyBarang'])->name('master.barangs.destroy');
    Route::post('/master/barangs/pengadaan', [BarangController::class, 'storePengadaan'])->name('master.barangs.pengadaan.store');
    Route::post('/master/barangs/mutasi-keluar', [BarangController::class, 'storeMutasiKeluar'])->name('master.barangs.mutasi-keluar.store');
    Route::delete('/master/barangs/transaksi/{id}', [BarangController::class, 'destroyTransaksi'])->name('master.barangs.transaksi.destroy');

    // Master Program & Asnaf
    Route::get('/master/programs', [ProgramController::class, 'index'])->name('master.programs');
    Route::post('/master/programs/program', [ProgramController::class, 'storeProgram'])->name('master.programs.program.store');
    Route::delete('/master/programs/program/{id}', [ProgramController::class, 'destroyProgram'])->name('master.programs.program.destroy');
    Route::post('/master/programs/subprogram', [ProgramController::class, 'storeSubprogram'])->name('master.programs.subprogram.store');
    Route::delete('/master/programs/subprogram/{id}', [ProgramController::class, 'destroySubprogram'])->name('master.programs.subprogram.destroy');
    Route::post('/master/programs/asnaf/{id}/up', [ProgramController::class, 'moveUpAsnaf'])->name('master.programs.asnaf.up');
    Route::post('/master/programs/asnaf/{id}/down', [ProgramController::class, 'moveDownAsnaf'])->name('master.programs.asnaf.down');

    // Laporan
    Route::get('/laporan/realisasi-dana-export', [LaporanController::class, 'exportRealisasiDana'])->name('laporan.realisasi-dana.export');
    Route::get('/laporan/realisasi-dana/{mode?}', [LaporanController::class, 'realisasiDana'])
        ->where('mode', 'semua|masuk|keluar')
        ->name('laporan.realisasi-dana');
    Route::get('/laporan/program-asnaf', [LaporanController::class, 'programAsnaf'])->name('laporan.program-asnaf');
    Route::get('/laporan/buku-kas/{mode?}', [LaporanController::class, 'bukuKas'])
        ->where('mode', 'semua|masuk|keluar')
        ->name('laporan.buku-kas');

    // Manajemen
    Route::get('/manajemen/pengguna', [PenggunaController::class, 'index'])->name('manajemen.pengguna');
    Route::post('/manajemen/pengguna', [PenggunaController::class, 'store'])->name('manajemen.pengguna.store');
    Route::delete('/manajemen/pengguna/{id}', [PenggunaController::class, 'destroy'])->name('manajemen.pengguna.destroy');

    Route::get('/manajemen/mustahik', [MustahikController::class, 'index'])->name('manajemen.mustahik');
    Route::post('/manajemen/mustahik', [MustahikController::class, 'store'])->name('manajemen.mustahik.store');
    Route::delete('/manajemen/mustahik/{id}', [MustahikController::class, 'destroy'])->name('manajemen.mustahik.destroy');
    Route::get('/manajemen/mustahik/template', [MustahikController::class, 'downloadTemplate'])->name('manajemen.mustahik.template');

    Route::get('/manajemen/keluarga', [KeluargaController::class, 'index'])->name('manajemen.keluarga');

    Route::get('/manajemen/baznas', [BaznasController::class, 'index'])->name('manajemen.baznas');
    Route::post('/manajemen/baznas/pengurus', [BaznasController::class, 'storePengurus'])->name('manajemen.baznas.pengurus.store');
    Route::delete('/manajemen/baznas/pengurus/{id}', [BaznasController::class, 'destroyPengurus'])->name('manajemen.baznas.pengurus.destroy');
    Route::post('/manajemen/baznas/info', [BaznasController::class, 'updateInfo'])->name('manajemen.baznas.info');

    Route::get('/manajemen/upz', [UpzController::class, 'index'])->name('manajemen.upz');
    Route::post('/manajemen/upz', [UpzController::class, 'store'])->name('manajemen.upz.store');
    Route::delete('/manajemen/upz/{id}', [UpzController::class, 'destroy'])->name('manajemen.upz.destroy');
    Route::get('/manajemen/upz/{id}', [UpzController::class, 'show'])->name('manajemen.upz.detail');
    Route::post('/manajemen/upz/{id}/pengurus', [UpzController::class, 'storePengurus'])->name('manajemen.upz.pengurus.store');
    Route::delete('/manajemen/upz/{upzId}/pengurus/{pengurusId}', [UpzController::class, 'destroyPengurus'])->name('manajemen.upz.pengurus.destroy');

    Route::get('/manajemen/lembaga', [LembagaController::class, 'index'])->name('manajemen.lembaga');
    Route::post('/manajemen/lembaga', [LembagaController::class, 'store'])->name('manajemen.lembaga.store');
    Route::delete('/manajemen/lembaga/{id}', [LembagaController::class, 'destroy'])->name('manajemen.lembaga.destroy');
    Route::get('/manajemen/lembaga/{id}', [LembagaController::class, 'show'])->name('manajemen.lembaga.detail');
    Route::post('/manajemen/lembaga/{id}/pengurus', [LembagaController::class, 'storePengurus'])->name('manajemen.lembaga.pengurus.store');
    Route::delete('/manajemen/lembaga/{lembagaId}/pengurus/{pengurusId}', [LembagaController::class, 'destroyPengurus'])->name('manajemen.lembaga.pengurus.destroy');



    // Modul Pengumpulan E-POOL: Muzakki
    Route::prefix('muzakki')->name('muzakki.')->group(function () {
        Route::get('/', [MuzakkiController::class, 'index'])->name('index');
        Route::get('/create', [MuzakkiController::class, 'create'])->name('create');
        Route::post('/', [MuzakkiController::class, 'store'])->name('store');
        Route::get('/template', [MuzakkiController::class, 'downloadTemplate'])->name('template');
        Route::get('/import', [MuzakkiController::class, 'importForm'])->name('import');
        Route::post('/import', [MuzakkiController::class, 'importProcess'])->name('import.process');
        Route::get('/{id}/edit', [MuzakkiController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MuzakkiController::class, 'update'])->name('update');
        Route::delete('/{id}', [MuzakkiController::class, 'destroy'])->name('destroy');
    });

    // Modul Pengumpulan E-POOL: Setoran UPZ & Kas Masuk
    Route::prefix('pengumpulan')->name('pengumpulan.')->group(function () {
        Route::prefix('upz')->name('upz.')->group(function () {
            Route::get('/', [SetoranUpzController::class, 'index'])->name('index');
            Route::get('/create', [SetoranUpzController::class, 'create'])->name('create');
            Route::post('/', [SetoranUpzController::class, 'store'])->name('store');
            Route::get('/search-upz', [SetoranUpzController::class, 'searchUpz'])->name('search-upz');
            Route::post('/parse-inline-excel', [SetoranUpzController::class, 'parseInlineExcel'])->name('parse-inline-excel');
            Route::get('/muzakki-by-upz/{lembagaId}', [SetoranUpzController::class, 'getMuzakkiByUpz'])->name('muzakki-by-upz');
            Route::get('/template/{lembagaId?}', [SetoranUpzController::class, 'downloadTemplate'])->name('template');
            Route::post('/import-preview', [SetoranUpzController::class, 'importPreview'])->name('import-preview');
            Route::post('/quick-store-muzakki', [SetoranUpzController::class, 'quickStoreMuzakki'])->name('quick-store-muzakki');
            Route::post('/import-confirm', [SetoranUpzController::class, 'importConfirm'])->name('import-confirm');
            Route::post('/{id}/upload-bukti', [SetoranUpzController::class, 'uploadBukti'])->name('upload-bukti');
            Route::post('/{id}/verifikasi', [SetoranUpzController::class, 'verifikasi'])->name('verifikasi');
            Route::post('/{id}/tolak', [SetoranUpzController::class, 'tolak'])->name('tolak');
            Route::get('/{id}/edit', [SetoranUpzController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SetoranUpzController::class, 'update'])->name('update');
            Route::get('/{id}', [SetoranUpzController::class, 'show'])->name('show');
            Route::get('/{id}/cetak', [SetoranUpzController::class, 'cetakLampiran'])->name('cetak');
            Route::delete('/{id}', [SetoranUpzController::class, 'destroy'])->name('destroy');
        });
        Route::get('/kas-masuk', [SetoranUpzController::class, 'kasMasuk'])->name('kas.index');
    });

    // Modul Simba Export
    Route::prefix('simba')->name('simba.')->group(function () {
        Route::get('/muzakki', [SimbaExportController::class, 'exportMuzakki'])->name('muzakki');
        Route::get('/kas-masuk', [SimbaExportController::class, 'exportKasMasuk'])->name('kas-masuk');
    });

    // Route alias laporan lampiran setoran
    Route::get('/laporan/lampiran-setoran', function () {
        return redirect()->route('pengumpulan.upz.index');
    })->name('laporan.lampiran-setoran');

    // Feature toggle routes
    Route::get('/fitur-tidak-tersedia', [FeatureToggleController::class, 'featureDisabled'])->name('feature.disabled');
});
