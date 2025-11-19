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
        if (!Schema::hasTable('pengajuan_surveys')) {
            Schema::create('pengajuan_surveys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengajuan_mustahik_id')->nullable()->constrained('pengajuan_mustahiks')->cascadeOnDelete();
                $table->string('token')->nullable()->unique();

                $table->date('tanggal_survey');
                $table->enum('hasil_akhir', ['layak', 'tidak layak'])->nullable();

                $table->text('catatan')->nullable();

                $table->string('surveyor')->nullable();
                $table->string('nohp_surveyor')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surveys');
    }
};
