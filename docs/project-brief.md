# Project Brief

_Terakhir diverifikasi: 2026-11-08 (tag `v1.0.6`)_

## Tujuan & Pengguna
- **Tujuan Sistem:** Platform E-Commerce dan Point of Sale (POS) terpadu untuk Butik Raabiha (busana muslim/gamis/fashion). Mendukung penjualan omnichannel (Online Web Storefront & Toko Fisik POS), manajemen inventori terisolasi/sinkron (`pos_only`, `online_only`, `both`), pencetakan struk kasir thermal via Web Bluetooth/Web Serial ESC/POS, program loyalitas (stempel/cap digital & poin), serta sistem reseller.
- **Pengguna:**
  - Pelanggan Umum & Reseller (Web Storefront).
  - Kasir Toko Fisik (Aplikasi Terminal POS `pos.raabiha.com`).
  - Supervisor & Tim Manajemen Toko (Otorisasi POS, Tutup Shift).
  - Super Admin & Pengelola Bisnis (Filament Admin Panel `admin.raabiha.com`).

## Profil Risiko (Diusulkan - Menunggu Konfirmasi User)
- **Jenis Proyek:** Web publik (E-Commerce Storefront) + Subdomain Kasir POS + Panel Admin Backoffice.
- **Data yang Disentuh:** Data pribadi pelanggan (alamat, telepon, email), transaksi pembayaran online (Tripay/Xendit Webhook), penerimaan kasir tunai/non-tunai, pembukuan kas (*cashflows*), dan kredensial/PIN kasir.
- **Pengguna:** Publik (pelanggan), staf internal (kasir, supervisor), dan pengelola/admin.
- **Dampak jika Gagal:** Besar (transaksi penjualan terhenti, selisih kas/stok, kebocoran data pelanggan/reseller).
- **Tingkat Keamanan:** **Ketat** (Strik/High-trust: perlindungan race condition stok, validasi webhook signature, otorisasi PIN supervisor, mitigasi manipulasi voucher).

## Stack
| Bagian | Teknologi | Versi (Dipakai / Terbaru) | Alasan Pemilihan | Sumber Alasan |
|---|---|---|---|---|
| Runtime | PHP | 8.4.1 / 8.4.x | Performa modern, fitur tipe ketat, kompatibilitas Laravel 13 | Server runtime |
| Framework Backend | Laravel Framework | v13.12.0 / v13.40.0 (per 2026-11-08) | Ekosistem stabil, ORM Eloquent, routing subdomain modular | `composer.json` |
| Admin Panel | Filament PHP | v5.6.6 / v5.11.2 (per 2026-11-08) | TDD cepat admin backoffice berbasis TALL stack | `composer.json` |
| Frontend Storefront & POS | Livewire + Blade | Livewire 3 / v3.x | Reaktivitas dinamis tanpa overhead SPA, integrasi native Laravel | `composer.json` |
| CSS & Styling | Tailwind CSS | ^4.3.0 / 4.3.5 (per 2026-11-08) | Utility-first CSS modern via `@tailwindcss/vite` | `package.json` |
| Bundler & Tooling | Vite | ^8.0.0 / 8.3.6 (per 2026-11-08) | Hot Module Replacement kilat | `package.json` |
| Database | SQLite (Dev/Test), MySQL/PostgreSQL (Prod) | - | Migrasi modular Laravel | `phpunit.xml`, `config/database.php` |
| Layanan Cetak Thermal | ESC/POS Service | Custom Service (`app/Services/EscPosService.php`) | Cetak langsung struk Bluetooth/Serial tanpa dialog driver | `perancangan_sistem_pos.md` |
| Payment Gateway & Webhook | Tripay & Xendit | Custom Controller | Pembayaran otomatis e-commerce | `routes/web.php` |
| Ekspedisi & Ongkir | BinderByte & Emsifa | Custom Service | Pelacakan resi dan tarif pengiriman | `app/Services/` |

## Perintah Penting
- **Jalankan Aplikasi:** `composer dev` (menjalankan server, queue worker, pail, dan vite concurrently) atau `php artisan serve`
- **Tes:** `composer test` atau `php artisan test`
- **Build Aset:** `npm run build`
- **Lint & Format:** `vendor/bin/pint`

## Doktrin Desain & Visual Tokens (Baseline Proyek)
- **Gaya Desain (Style):** Clean Minimalist E-Commerce dengan nuansa Muslimah/Boutique.
- **Tipografi:** Sans-serif modern (Inter pada Filament, font sistem modern pada Storefront).
- **Karakter Motion:** Snappy & responsif untuk POS Terminal kasir; Smooth untuk pengalaman belanja online.

## Rencana Pembagian Fase (Phase-Gating Plan Rekomendasi)
- **Prerequisites (Audit & Baseline):**
  - Penyelesaian onboarding dan dokumentasi arsitektur di `docs/`.
  - Handoff perbaikan konfigurasi testing subdomain (`phpunit.xml` & test suite).
- **Fase 1 (Stabilitas Baseline & Test Suite):**
  - Resolusi 17 tes gagal pada Feature tests (routing subdomain `admin.` dan `pos.`, perhitungan poin loyalty, format struk cap).
- **Fase 2 (Pengembangan / Pemeliharaan Fitur Sesuai Kebutuhan Bisnis):**
  - Ditentukan berdasarkan prioritas user melalui brief `/role-asisten`.

## Batasan & Hal yang Tidak Boleh Berubah
- **Aturan Isolasi Stok Toko & Web:** Channel `pos_only`, `online_only`, dan `both` tidak boleh dilebur sembarangan tanpa sinkronisasi `StockLog`.
- **Integrasi Hardware POS:** Percetakan struk kasir harus tetap mengutamakan Web Bluetooth / Web Serial ESC/POS standar browser tanpa mewajibkan aplikasi tambahan pihak ketiga.
- **Zero Data Loss:** Perubahan skema migrasi database dilarang merusak data transaksi (`orders`, `pos_sessions`, `cashflows`).

## Kondisi Dasar (Baseline, Proyek Lanjutan)
- **Tes/Linter:** Ada (PHPUnit 12.5.12, Laravel Pint 1.27). Total 109 tes: **92 lulus, 17 gagal**.
- **Temuan Awal:** Kegagalan tes disebabkan routing subdomain pada testing environment dan penyesuaian logika reward stempel. Detail tercatat pada Laporan Onboarding.
