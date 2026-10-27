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
        // 1. Remap FK references in pengajuan_mustahiks from duplicate IDs to original IDs
        if (Schema::hasTable('pengajuan_mustahiks')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement("\n                    UPDATE pengajuan_mustahiks
                    SET subprogram_id = (
                        SELECT orig.id
                        FROM subprograms dup
                        JOIN subprograms orig ON orig.program_id = dup.program_id AND orig.nama = dup.nama AND orig.id <= 37
                        WHERE dup.id = pengajuan_mustahiks.subprogram_id
                    )
                    WHERE subprogram_id >= 38
                ");
            } else {
                DB::statement("\n                    UPDATE pengajuan_mustahiks pm
                    JOIN subprograms dup ON pm.subprogram_id = dup.id
                    JOIN subprograms orig ON orig.program_id = dup.program_id AND orig.nama = dup.nama AND orig.id <= 37
                    SET pm.subprogram_id = orig.id
                    WHERE dup.id >= 38
                ");
            }
        }

        // 2. Delete duplicate rows from subprograms
        DB::statement("DELETE FROM subprograms WHERE id >= 38");

        // 3. Add unique constraint on (program_id, nama)
        Schema::table('subprograms', function (Blueprint $table) {
            $table->unique(['program_id', 'nama'], 'subprograms_program_id_nama_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subprograms', function (Blueprint $table) {
            $table->dropUnique('subprograms_program_id_nama_unique');
        });
    }
};
