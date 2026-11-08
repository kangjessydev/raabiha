# Handoff 001: onboard → backend

- **Dari:** role-onboard   **Untuk:** role-backend
- **Tanggal:** 2026-11-08
- **Status:** open
- **Prioritas:** sedang

## Masalah / Kebutuhan
Eksekusi baseline test suite `php artisan test` menghasilkan 17 tes gagal dari total 109 tes (92 passed, 17 failed). Sebagian besar kegagalan (8 tes) disebabkan oleh rute subdomain `admin.` dan `pos.` diuji menggunakan pemanggilan standar `$this->get('/admin/login')` atau `$this->get('/')` tanpa mendefinisikan server header `HTTP_HOST` / URL subdomain yang valid dalam pengujian. Selain itu, terdapat 9 tes logika POS (loyalty, receipt settings, shift schedule) yang gagal karena perubahan batas/threshold bisnis terbaru (misal: jumlah cap 9 vs 12).

## Bukti
- Output `php artisan test`:
  - `AdminLoginTest.php:18`: `$response = $this->get('/admin/login');` menerima status 404 (karena Filament terikat ke `admin.` subdomain di `app/Providers/Filament/AdminPanelProvider.php:53`).
  - `KasirAccessTest.php`: 7 tes gagal 404/redirect ke rute salah karena tidak menyertakan host `pos.raabiha.com`.
  - `PosLoyaltyTest.php:37, 92, 133, 190, 246, 336`: Selisih assertions poin dan stempel.
  - `PosReceiptSettingsTest.php:40`: Format teks struk cap ("Total Cap: 3 dari 12 Cap" vs "Total Cap: 3 dari 9 Cap").

## Tindakan yang Disarankan
1. Tambahkan konfigurasi atau helper host subdomain pada test case (`$this->withServerVariables(['HTTP_HOST' => 'admin.raabiha.com'])` atau konfigurasi `APP_URL`/`APP_DOMAIN` pada `phpunit.xml`).
2. Selaraskan assertions nilai uji `PosLoyaltyTest` dan `PosReceiptSettingsTest` sesuai aturan bisnis terkini.
3. Jalankan ulang test suite hingga seluruh 109 tes berstatus hijau.

## Di Luar Cakupan
- Dilarang mengubah skema tabel database produksi atau logika transaksi core yang sudah stabil.
- Tidak mengubah konfigurasi server production VPS.

## Kriteria Selesai
- `php artisan test` berjalan dengan 109 tes lulus (0 failures).

## Disetujui User
menunggu
