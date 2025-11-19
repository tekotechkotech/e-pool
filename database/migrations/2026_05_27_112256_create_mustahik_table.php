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
        Schema::create('mustahiks', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_mustahik', ['perorangan', 'lembaga'])->default('perorangan');
            $table->foreignId('person_id')->nullable()->constrained('persons')->cascadeOnDelete();
            $table->foreignId('lembaga_id')->nullable()->constrained('lembagas')->cascadeOnDelete();
            
            $table->string('pekerjaan')->nullable();
            $table->string('nim')->nullable();
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mustahiks');
    }
};
