# Deployment & Build Strategy

## 1. Spesifikasi Server
Sistem di-*hosting* pada lingkungan *production* mandiri dengan detail:
- **Provider:** Domainesia
- **Spesifikasi:** VPS Lite (2 Core CPU, 2 GB RAM, 20 GB SSD)
- **Sistem Operasi:** Ubuntu
- **Control Panel:** CloudPanel

## 2. Alur Pembaruan (Update Process)
*(Tahap ini akan menggunakan standar eksekusi CloudPanel untuk framework Laravel)*
- Pembaruan kode akan dipusatkan melalui sinkronisasi Git (Git Pull).
- **Proses Build & Optimize:** Setiap rilis kode baru wajib menjalankan perintah kompilasi dan optimasi Laravel:
  - `composer install --no-dev --optimize-autoloader`
  - `php artisan migrate --force`
  - `php artisan optimize:clear` (atau `php artisan optimize` di *production*)
  - `npm ci` & `npm run build` (Untuk mem-build aset Tailwind/Vite).

## 3. Strategi Backup (Wajib Diimplementasikan)
*Saat ini sistem backup belum berjalan. Tugas Developer (AI) ke depannya adalah membantu merancang:*
1. **Cron Job Otomatis:** Menyiapkan perintah CLI (misal lewat `wp-cli` atau *bash script*) untuk mengekspor *database* setiap tengah malam.
2. **Backup File Terjadwal:** Membuat arsip `wp-content/uploads` dan tema secara berkala.
3. **Pembersihan Log:** Rotasi log server dan WordPress agar tidak menghabiskan kapasitas disk 20 GB.
