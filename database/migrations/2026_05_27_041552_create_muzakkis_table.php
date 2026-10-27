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
        Schema::create('muzakkis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->unique()->constrained('persons')->cascadeOnDelete();
            
            $table->string('nrm')->nullable();
            $table->string('npwz')->nullable();

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muzakkis');
    }
};
