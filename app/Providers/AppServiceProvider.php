<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        config(['app.locale' => 'id']);
        setlocale(LC_TIME, 'id_ID', 'id_ID.utf8', 'id', 'IND', 'Indonesian');

        // Gate untuk Admin
        Gate::define('is-admin', function ($user) {
            return session('active_jabatan') === 'Admin BAZNAS';
        });

        // Gate untuk Pimpinan
        Gate::define('is-pimpinan', function ($user) {
            return session('active_jabatan') === 'Pimpinan BAZNAS';
        });

        // Gate Multi-jabatan (Contoh: Bisa diakses Admin ATAU Admin System)
        Gate::define('manage-master', function ($user) {
            return in_array(session('active_jabatan'), ['Admin BAZNAS', 'Admin System']);
        });
    }
}
