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
        Schema::table('penguruses', function (Blueprint $table) {
            $table->unsignedBigInteger('active_person_id')->nullable()
                ->storedAs("CASE WHEN is_active = 1 THEN person_id ELSE NULL END")
                ->after('is_active');
        });
        Schema::table('penguruses', function (Blueprint $table) {
            $table->unique('active_person_id', 'penguruses_active_person_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penguruses', function (Blueprint $table) {
            $table->dropUnique('penguruses_active_person_id_unique');
            $table->dropColumn('active_person_id');
        });
    }
};
