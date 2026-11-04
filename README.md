# e-Pool: Sistem Manajemen ZIS Terpadu BAZNAS & UPZ

Sistem **e-Pool** digunakan untuk manajemen pencatatan, penyetoran, dan rekonsiliasi dana Zakat, Infaq, dan Shadaqah (ZIS) dari Unit Pengumpul Zakat (UPZ) ke BAZNAS, dilengkapi skema verifikasi dua arah (*maker-checker workflow*), deteksi otomatis muzakki, dan isolasi data multi-tenant.

---

## 🔐 Kredensial Akun Demo (Seeder)

Seluruh akun demo telah dikonfigurasi melalui seeder database (`database/seeders/UserPengurusSeeder.php` dan `database/seeders/LembagaSeeder.php`).

> **Password Default untuk Seluruh Akun Demo:**  
> `password`

### Tabel Matriks Akun Demo & Hak Akses

| Email | Nama Pengguna | Jabatan / Role | Tingkat / Lembaga | Fokus Skenario Demo |
| :--- | :--- | :--- | :--- | :--- |
| **`admin@baznas.id`** | Ahmad Fauzi | **Admin BAZNAS** | BAZNAS Kabupaten | **Verifikasi Setoran, Input Multi-UPZ & Koreksi**<br>• Input setoran atas nama UPZ manapun.<br>• Opsi centang langsung terverifikasi vs tunda verifikasi.<br>• Koreksi nominal setoran (in-place edit).<br>• Verifikasi/Tolak setoran UPZ.<br>• Ekspor SIMBA & kelola profil BAZNAS/UPZ. |
| **`superuser@baznas.id`** | Super Administrator | **Superuser** | BAZNAS Kabupaten | **Akses Penuh Seluruh Sistem**<br>• Manajemen pengguna, master program/asnaf, master dana & barang, dan override seluruh aksi. |
| **`pimpinan@baznas.id`** | Dr. H. Ridwan Hakim | **Pimpinan BAZNAS** | BAZNAS Kabupaten | **Monitoring & Persetujuan**<br>• Review laporan penerimaan ZIS, realisasi dana, dan buku kas. |
| **`staff@baznas.id`** | Siti Rahmawati | **Staff Penyaluran** | BAZNAS Kabupaten | **Penyaluran & Mustahik**<br>• Manajemen mustahik dan pencatatan distribusi bantuan. |
| **`upz@baznas.id`** | H. Mahfudz | **Ketua UPZ** | UPZ Masjid Al-Ikhlas | **Input Mandiri UPZ (Locked Scope)**<br>• Form setoran terkunci otomatis ke *UPZ Masjid Al-Ikhlas*.<br>• Data muzakki binaan otomatis terdeteksi & termuat.<br>• Setoran berstatus *Menunggu Verifikasi* (kas ditahan).<br>• Isolasi tenant: hanya melihat data UPZ sendiri. |
| **`sekretaris.upz@baznas.id`** | Deden Suryadi | **Sekretaris UPZ** | UPZ Masjid Al-Ikhlas | **Administrasi & Muzakki UPZ**<br>• Input setoran mandiri dan pendataan muzakki UPZ Masjid Al-Ikhlas. |
| **`bendahara.upz@baznas.id`** | Yanti Nurfitri | **Bendahara UPZ** | UPZ Masjid Al-Ikhlas | **Keuangan UPZ**<br>• Input setoran dan upload bukti transfer bank UPZ Masjid Al-Ikhlas. |
| **`upz.rsud@baznas.id`** | dr. Wahyu Santoso | **Ketua UPZ** | UPZ RSUD Kota | **Uji Isolasi Multi-Tenant Antar-UPZ**<br>• Membuktikan bahwa UPZ RSUD Kota tidak dapat melihat/mengakses setoran milik UPZ Masjid Al-Ikhlas. |

---

## 🎬 Panduan Langkah Demo Alur Kerja Sistem

### Skenario A: Input Mandiri oleh Pengurus UPZ
1. Buka halaman login di `/login`.
2. Masuk dengan akun **`upz@baznas.id`** (Password: `password`).
3. Masuk ke menu **Pengumpulan $\rightarrow$ Setoran ZIS UPZ $\rightarrow$ Input Setoran UPZ**.
4. **Amati Keistimewaan**:
   - Pilihan UPZ langsung terkunci ke kartu *"Input Mandiri UPZ: UPZ Masjid Al-Ikhlas"*.
   - Seluruh daftar muzakki binaan UPZ Masjid Al-Ikhlas otomatis terdeteksi dan termuat ke dalam tabel lembar kerja.
   - Tidak ada tombol centang verifikasi (UPZ tidak dapat memverifikasi setorannya sendiri).
5. Isi nominal ZIS atau unggah spreadsheet / bukti transfer, lalu klik **Simpan Setoran UPZ**.
6. **Hasil**: Status setoran tercatat sebagai `Menunggu Verifikasi` (lencana jam pasir oranye ⏳). Rekor kas masuk (`DanaTransaksi`) **belum dicatat** ke Buku Kas.

---

### Skenario B: Verifikasi & Koreksi Data oleh Admin BAZNAS
1. Logout, lalu login sebagai Admin BAZNAS: **`admin@baznas.id`** (Password: `password`).
2. Masuk ke menu **Pengumpulan $\rightarrow$ Setoran ZIS UPZ**.
3. Filter status berdasarkan `Menunggu Verifikasi`.
4. Klik tombol detail pada setoran yang baru dikirim oleh UPZ Masjid Al-Ikhlas.
5. **Amati Panel Verifikasi BAZNAS**:
   - Admin dapat melihat bukti transfer yang diunggah UPZ.
   - **Koreksi Nominal (Jika berbeda dengan mutasi bank)**: Klik tombol **Edit / Sesuaikan Data** untuk mengubah nominal per item muzakki atau total setoran jika terdapat selisih.
   - **Sahkan Setoran**: Klik tombol **Verifikasi Setoran** pada banner aksi verifikasi.
6. **Hasil**:
   - Status setoran berubah menjadi `Terverifikasi` (lencana centang hijau ✅).
   - Kolom audit mencatat nama verifikator (*Ahmad Fauzi*) dan waktu verifikasi.
   - Sistem seketika membukukan transaksi penerimaan ke Buku Kas BAZNAS (`DanaTransaksi`) untuk pos Zakat (2.2), Infak (2.7), Fitrah (2.5), atau Titipan (2.18).

---

### Skenario C: Input Langsung oleh Admin BAZNAS (Kasus Penyerahan Tunai/Langsung)
1. Login sebagai **`admin@baznas.id`**.
2. Masuk ke menu **Input Setoran UPZ**.
3. **Amati Fitur Admin BAZNAS**:
   - Dapat mencari dan memilih UPZ manapun menggunakan pencarian *asynchronous* (min. 4 karakter).
   - Terdapat kartu verifikasi: **"Tandai Data Sesuai & Langsung Terverifikasi"** (default tercentang ☑️).
4. Jika centang dibiarkan aktif: Setoran langsung tersimpan dengan status `Terverifikasi` dan langsung masuk ke Buku Kas.
5. Jika centang dimatikan: Setoran disimpan sebagai draf `Menunggu Verifikasi` (berguna jika staf hanya menginput draf sementara bendahara yang memegang mutasi bank).

---

### Skenario D: Uji Keamanan & Isolasi Multi-Tenant
1. Login sebagai Ketua UPZ RSUD Kota: **`upz.rsud@baznas.id`**.
2. Buka daftar setoran UPZ: Hanya setoran milik RSUD Kota yang tampil.
3. Mencoba membuka URL setoran milik UPZ Masjid Al-Ikhlas secara langsung (`/pengumpulan/upz/{id_al_ikhlas}`): Sistem secara otomatis menolak dengan error `403 Forbidden` (*Akses Ditolak*).

---

### Skenario E: Manajemen Data Muzakki pada UPZ
1. Login sebagai **`admin@baznas.id`** atau **`superuser@baznas.id`**.
2. Buka menu **Manajemen $\rightarrow$ Unit Pengumpul Zakat (UPZ)**.
3. Klik detail salah satu UPZ (misal: *UPZ Masjid Al-Ikhlas*).
4. Pilih tab **Muzakki Terdaftar**:
   - Menampilkan seluruh muzakki binaan UPZ tersebut.
   - Dilengkapi filter pencarian real-time berdasarkan Nama / NIK / NPWZ.

---

## 🛠️ Perintah Reset Database & Seeding

Untuk menyetel ulang database dan mengaktifkan kembali seluruh data demo di atas:

```bash
# Jalankan migrasi ulang dan seluruh seeder demo
php artisan migrate:fresh --seed

# Atau jalankan seeder spesifik
php artisan db:seed
```

Untuk menjalankan rangkaian pengujian otomatis (86 skenario test):

```bash
php artisan test
```
