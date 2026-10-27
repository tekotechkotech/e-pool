<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan_mustahiks', function (Blueprint $table) {
            $table->string('bentuk_bantuan', 20)->default('Uang')->after('sumber_dana_id');
            $table->string('nama_barang')->nullable()->after('bentuk_bantuan');
            $table->unsignedInteger('jumlah_barang')->nullable()->after('nama_barang');
            $table->string('satuan_barang', 50)->nullable()->after('jumlah_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_mustahiks', function (Blueprint $table) {
            $table->dropColumn(['bentuk_bantuan', 'nama_barang', 'jumlah_barang', 'satuan_barang']);
        });
    }
};
