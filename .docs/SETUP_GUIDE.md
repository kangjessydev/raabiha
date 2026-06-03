# Panduan Instalasi & Persiapan Proyek (Setup Guide)

Dokumen ini menjelaskan langkah-langkah untuk menyiapkan proyek Raabiha E-Commerce dari awal, baik di lokal maupun di server.

## 1. Persyaratan Lingkungan (Environment)
- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL / MariaDB

## 2. Langkah-Langkah Instalasi
1. Kloning repositori proyek.
2. Salin file environment:
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan konfigurasi `.env` dengan database lokal atau servermu.
4. Instal dependensi PHP:
   ```bash
   composer install
   ```
5. Buat key aplikasi:
   ```bash
   php artisan key:generate
   ```
6. Jalankan migrasi dan seeder awal:
   ```bash
   php artisan migrate --seed
   ```
7. Buat link storage (wajib agar gambar muncul):
   ```bash
   php artisan storage:link
   ```
8. Instal dan build aset frontend:
   ```bash
   npm install
   npm run build
   ```

## 3. Kredensial Pihak Ketiga (Third-Party APIs)
*Harap isi data berikut di file `.env` ketika API sudah didaftarkan.*

### Xendit (Payment Gateway)
- `XENDIT_SECRET_KEY=` *(Diperoleh dari dashboard Xendit)*
- `XENDIT_PUBLIC_KEY=`

### RajaOngkir (Pengiriman)
- `RAJAONGKIR_API_KEY=` *(Harus mendaftar akun Basic/Pro)*
- `RAJAONGKIR_ACCOUNT_TYPE=basic`

*(Kunci-kunci di atas harap tidak di-commit ke Git. Simpan dengan aman!)*
