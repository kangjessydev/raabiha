# Brief: Inspeksi VPS CloudPanel & Deploy Rilis v1.1.0

- **Untuk:** role-devops   **Dari:** role-asisten   **Tanggal:** 2026-11-23
- **Status:** `open`
- **Disetujui user:** ya

## Tujuan
Melakukan audit read-only pada lingkungan VPS CloudPanel (`<VPS_CLOUDPANEL>`), lalu—setelah audit transaksi (`021`) dinyatakan aman dan setiap langkah disetujui user—merilis branch `development` ke `main` dengan tag **`v1.1.0`** dan melakukan deploy terkendali tanpa kehilangan data (*zero data loss*).

## Konteks
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18)).
- **Target Rilis:** Merge `development` (`08977841` atau commit final pasca-audit `021`) → `main` → buat tag rilis **`v1.1.0`** (melanjutkan tag terakhir `v1.0.7`).
- **Akses Sementara:** User telah mengizinkan akses SSH sementara ke `<VPS_CLOUDPANEL>` sebagai `root` (tanpa password sementara; user akan mengembalikan password setelah sesi selesai). **Dilarang keras menulis alamat IP mentah atau isi `.env` ke dalam file repo/laporan.**
- **Ketergantungan Fase:**
  - **Fase 1 (Read-Only Audit VPS)** boleh dijalankan segera (karena 100% read-only dan tidak mengubah apa pun di server/Git).
  - **Fase 2 (Merge `development` → `main` & Tag `v1.1.0`)** dan **Fase 3 (Backup & Deploy VPS)** **WAJIB menunggu** hasil audit transaksi ([021-asisten-to-inspektur-audit-transaksi-ecommerce-dan-pos.md](./021-asisten-to-inspektur-audit-transaksi-ecommerce-dan-pos.md)) dinyatakan aman/Go oleh user.

## Yang diminta
1. **Fase 1 — Audit VPS CloudPanel (100% Read-Only):**
   - Temukan path root situs CloudPanel, `<site-user>`, dan cek apakah `admin.`/`pos.` berbagi codebase yang sama.
   - Cek versi PHP CLI, PHP-FPM, Composer, Node, NPM, MySQL/MariaDB, serta kapasitas disk & RAM.
   - Cek status Git saat ini di server (tag/branch aktif & apakah ada file termodifikasi).
   - Cek `php artisan migrate:status` dan verifikasi apakah tabel/kolom yang sempat bentrok di lokal (`imports`, `exports`, `failed_import_rows`, `product_variants.media_id`) atau tabel legacy (`coupons`, `salespages`) ada di DB server.
   - Cek versi `public/vendor/livewire/manifest.json` dan `public/build/`.
   - Cek keberadaan layanan pendukung (Supervisor/Queue worker, Redis, Cronjob scheduler) dan error terbaru di `storage/logs/laravel.log`.
2. **Fase 2 — Release Gate & Tagging `v1.1.0` (Wajib Izin Eksplisit):**
   - Setelah audit `021` selesai & disetujui user, rapikan `CHANGELOG.md` ke versi `[1.1.0]`, merge `development` → `main`, buat annotated tag `v1.1.0`, dan push ke `origin`.
3. **Fase 3 — Backup & Deploy Terkendali (Wajib Izin Per Langkah):**
   - Backup database (`mysqldump`) ke lokasi aman di luar webroot + catat tag aktif sebelumnya untuk rollback.
   - Aktifkan maintenance mode (`php artisan down`) sebagai `<site-user>`.
   - Jalankan `git fetch --tags` & `git checkout tags/v1.1.0` sebagai `<site-user>` (**CloudPanel Ownership Guard** — dilarang mengubah kepemilikan file jadi `root:root`).
   - Install dependensi (`composer install --no-dev --optimize-autoloader`), publish `livewire:assets`, build aset (`npm ci && npm run build` jika perlu), jalankan migrasi aman (`migrate --force`), dan `optimize:clear` + `optimize`.
   - Nonaktifkan maintenance mode (`php artisan up`), lakukan smoke-test HTTP & cek `storage/logs/laravel.log`.
4. **Fase 4 — Laporan & Pengamanan Kembali:**
   - Simpan laporan di `docs/reports/2026-11-23-devops-deploy-v1-1-0.md` beserta panduan rollback cepat.
   - Ingatkan user untuk mengaktifkan kembali password/keamanan SSH `root`.

## Di luar cakupan
- Mengubah kode aplikasi (`app/`, `resources/`, `routes/`, `database/migrations/`).
- Menjalankan perubahan di GUI CloudPanel (jika diperlukan, laporkan dan pandu user melakukannya sendiri).
- Menjalankan perintah mutasi di VPS atau branch `main` tanpa konfirmasi eksplisit per langkah dari user.

## Kriteria selesai
- Hasil Fase 1 terdokumentasi dengan bukti output perintah yang jelas.
- Merge ke `main` & tag `v1.1.0` terverifikasi (setelah Go dari audit `021`).
- Deploy ke VPS CloudPanel sukses tanpa data loss, ownership file tetap milik `<site-user>`, migrasi sinkron, aset Livewire `4.4.9` aktif, dan smoke-check normal.
- Laporan tersimpan di `docs/reports/` tanpa membocorkan IP mentah maupun isi `.env`.

## Pertanyaan terbuka
- Tidak ada (seluruh parameter & kebijakan privasi sudah disetujui user).
