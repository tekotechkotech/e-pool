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
        Schema::create('setoran_upz_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setoran_upz_id')->constrained('setoran_upzs')->cascadeOnDelete();
            $table->foreignId('muzakki_id')->nullable()->constrained('muzakkis')->nullOnDelete();
            $table->string('npwz')->nullable();
            $table->string('nama');
            $table->unsignedBigInteger('nominal_zakat')->default(0);
            $table->unsignedBigInteger('nominal_infak')->default(0);
            $table->unsignedBigInteger('nominal_fitrah')->default(0);
            $table->unsignedBigInteger('nominal_titipan')->default(0);
            $table->unsignedBigInteger('jumlah')->default(0);
            $table->string('keterangan')->nullable();
            $table->string('periode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_upz_items');
    }
};
