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
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'kode_pos')) {
                $table->string('kode_pos', 10)->nullable()->after('id_kelurahan');
            }
        });

        Schema::table('lembagas', function (Blueprint $table) {
            if (!Schema::hasColumn('lembagas', 'kode_pos')) {
                $table->string('kode_pos', 10)->nullable()->after('id_kelurahan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            if (Schema::hasColumn('persons', 'kode_pos')) {
                $table->dropColumn('kode_pos');
            }
        });

        Schema::table('lembagas', function (Blueprint $table) {
            if (Schema::hasColumn('lembagas', 'kode_pos')) {
                $table->dropColumn('kode_pos');
            }
        });
    }
};
