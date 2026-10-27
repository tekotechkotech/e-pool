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
        if (Schema::hasTable('pengajuan_surveys') && Schema::hasColumn('pengajuan_surveys', 'status_pengisian')) {
            Schema::table('pengajuan_surveys', function (Blueprint $table) {
                $table->dropColumn('status_pengisian');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pengajuan_surveys') && !Schema::hasColumn('pengajuan_surveys', 'status_pengisian')) {
            Schema::table('pengajuan_surveys', function (Blueprint $table) {
                $table->enum('status_pengisian', ['draft', 'selesai'])->default('draft')->nullable();
            });
        }
    }
};
