<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan_mustahiks', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_jiwa')->default(1)->notNull()->after('keterangan');
            $table->unsignedInteger('jumlah_kk')->default(1)->notNull()->after('jumlah_jiwa');
        });

        // Backfill existing rows with default value of 1
        DB::table('pengajuan_mustahiks')
            ->whereNull('jumlah_jiwa')
            ->orWhereNull('jumlah_kk')
            ->update([
                'jumlah_jiwa' => 1,
                'jumlah_kk' => 1,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_mustahiks', function (Blueprint $table) {
            $table->dropColumn(['jumlah_jiwa', 'jumlah_kk']);
        });
    }
};