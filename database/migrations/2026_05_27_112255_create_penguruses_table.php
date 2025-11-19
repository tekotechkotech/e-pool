<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penguruses', function (Blueprint $table) {
            $table->id();
            
            $table->enum('jenis_pengurus', ['Baznas', 'Lembaga']);
            $table->foreignId('lembaga_id')->nullable()->constrained('lembagas')->cascadeOnDelete();

            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('pengurus_jabatan_id')->nullable()->constrained('pengurus_jabatans')->cascadeOnDelete();

            $table->string('no_sk')->nullable();
            $table->string('file_sk')->nullable();

            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penguruses');
    }
};
