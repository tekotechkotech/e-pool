<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Schema::hasTable('wilayah') && DB::table('wilayah')->count() > 0) {
            $this->command->info('Data wilayah sudah ada; impor dilewati.');
            return;
        }

        // 1. Ambil jalur/path file SQL
        $path = database_path('wilayah.sql');

        // 2. Cek apakah filenya benar-benar ada
        if (!File::exists($path)) {
            $this->command->error("File wilayah.sql tidak ditemukan di folder database/");
            return;
        }

        $this->command->info("Sedang mengimport file wilayah.sql... (Mohon tunggu)");

        // 3. Baca isi file SQL
        $sql = File::get($path);

        // 4. Jika tabel sudah ada, hapus DDL create & primary key alter agar tidak bentrok
        if (Schema::hasTable('wilayah')) {
            $sql = preg_replace('/CREATE TABLE\s+(`?wilayah`?|["\']?wilayah["\']?)\s*\(.*?\)\s*ENGINE=[^;]+;/is', '', $sql, 1);
            $sql = preg_replace('/ALTER TABLE\s+(`?wilayah`?|["\']?wilayah["\']?)\s+ADD\s+PRIMARY\s+KEY\s*\([^;]+\);/is', '', $sql, 1);
        }

        // 5. Eksekusi query SQL ke database
        DB::unprepared($sql);

        $this->command->info("Data wilayah berhasil dimasukkan!");
    }
}
