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
        Schema::table('setoran_upzs', function (Blueprint $table) {
            $table->string('sumber_input', 20)->default('UPZ')->after('status');
            $table->string('tipe_input', 30)->default('detail_muzakki')->after('sumber_input');
            $table->foreignId('pasangan_setoran_id')->nullable()->after('tipe_input')->constrained('setoran_upzs')->nullOnDelete();
            $table->string('status_rekonsiliasi', 40)->default('menunggu_pasangan')->after('pasangan_setoran_id');
            $table->bigInteger('selisih_nominal')->default(0)->after('status_rekonsiliasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setoran_upzs', function (Blueprint $table) {
            $table->dropForeign(['pasangan_setoran_id']);
            $table->dropColumn([
                'sumber_input',
                'tipe_input',
                'pasangan_setoran_id',
                'status_rekonsiliasi',
                'selisih_nominal',
            ]);
        });
    }
};
