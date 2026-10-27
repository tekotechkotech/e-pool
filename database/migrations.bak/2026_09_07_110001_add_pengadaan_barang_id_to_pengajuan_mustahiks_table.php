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
            $table->foreignId('pengadaan_barang_id')->nullable()->after('satuan_barang')->constrained('pengadaan_barangs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_mustahiks', function (Blueprint $table) {
            $table->dropForeign(['pengadaan_barang_id']);
            $table->dropColumn('pengadaan_barang_id');
        });
    }
};
