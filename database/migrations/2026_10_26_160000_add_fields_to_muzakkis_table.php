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
        Schema::table('muzakkis', function (Blueprint $table) {
            $table->foreignId('lembaga_id')->nullable()->after('person_id')->constrained('lembagas')->nullOnDelete();
            $table->string('npwp')->nullable()->after('npwz');
            $table->string('nip')->nullable()->after('npwp');
            $table->string('pekerjaan')->nullable()->after('nip');
            $table->text('alamat_kantor')->nullable()->after('pekerjaan');
            $table->text('alamat_korespondensi')->nullable()->after('alamat_kantor');
            $table->string('jenis_zakat')->nullable()->after('alamat_korespondensi');
            $table->date('tanggal_registrasi')->nullable()->after('jenis_zakat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('muzakkis', function (Blueprint $table) {
            $table->dropForeign(['lembaga_id']);
            $table->dropColumn([
                'lembaga_id',
                'npwp',
                'nip',
                'pekerjaan',
                'alamat_kantor',
                'alamat_korespondensi',
                'jenis_zakat',
                'tanggal_registrasi',
            ]);
        });
    }
};
