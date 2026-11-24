# Laporan Deploy Rilis v1.1.0 ke VPS CloudPanel

- **Peran:** `role-devops`
- **Tanggal:** 2026-11-23
- **Handoff Ditutup:** [022-asisten-to-devops-deploy-vps-cloudpanel.md](../handoff/022-asisten-to-devops-deploy-vps-cloudpanel.md)
- **Target Server:** `<VPS_CLOUDPANEL>` (`/home/raabiha/htdocs/www.raabiha.com`, owner `raabiha:raabiha`)
- **Tag Rilis:** **`v1.1.0`** (`f6933674`) — naik dari **`v1.0.7`** (`8b14a959`)

---

## 1. Ringkasan Eksekusi (Semua Langkah Disetujui Eksplisit)

1. **Fase 1 — Audit Lingkungan VPS (Read-Only):**
   - PHP-FPM `8.5` (port `20001`), PHP CLI `8.4.18` (`/usr/bin/php8.5` tersedia), Composer `2.9.5`, Node `v20.20.2`, NPM `10.8.2`, MariaDB `11.4.10`.
   - Disk aman (18 GB kosong dari 30 GB), RAM 1.9 GiB + 2.0 GiB swap.
   - Verifikasi zero data loss sebelum migrasi: `coupons` = `0 rows`, `salespages` = `0 rows`, `sales_pages` = `1 row` (aman tidak disentuh), produk `both` = `14 rows` (aman ditoleransi Opsi A).
2. **Fase 2 — Release Tagging:**
   - `CHANGELOG.md` dirilis ke `[1.1.0] - 2026-11-23` (`0d0a2460`), merge `development` → `main` (`f6933674`), annotated tag `v1.1.0` dibuat & di-push ke `origin`.
3. **Fase 3 — Backup & Deploy Terkendali:**
   - **Langkah 1 (Backup DB):** `mariadb-dump` terkompresi ke `/home/raabiha/backups/db_pre_v1.1.0_20261123.sql.gz` (`-rw------- raabiha:raabiha`, **1.7 MB**, lolos uji `gzip -t`).
   - **Langkah 2 (Maintenance, Checkout, Dependensi & Aset):**
     - `php8.5 artisan down --retry=60`
     - `git fetch --tags origin` + normalisasi ownership ke `raabiha:raabiha` + `git checkout --force tags/v1.1.0` (`f6933674`)
     - `composer install --no-dev --optimize-autoloader` (Laravel `13.43.0`, Filament `5.12.0`, Livewire `4.4.9`, Curator `5.6.7`, `filament:upgrade` sukses)
     - `php8.5 artisan vendor:publish --force --tag=livewire:assets` (`{"/livewire.js":"102cd394"}`)
     - `npm install --no-audit --no-fund --no-save && npm run build` (`vite v8.3.8`, `✓ built in 8.44s`)
   - **Langkah 3 (Migrasi, Cache, Live & Smoke Check):**
     - `php8.5 artisan migrate --force`:
       - `2026_11_23_070000_set_channel_visibility_default_to_online_only` — `272.19ms DONE` (`[9] Ran`)
       - `2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables` — `927.81ms DONE` (`[9] Ran`)
     - `optimize:clear` → `optimize` → `filament:optimize` — semua `DONE`.
     - `php8.5 artisan up` — **Application is now live.**

---

## 2. Bukti Smoke Check Pasca-Deploy
- `https://www.raabiha.com` → **HTTP 200**
- `https://www.raabiha.com/shop` → **HTTP 200**
- `https://admin.raabiha.com` → **HTTP 302** (redirect login Filament normal)
- `https://pos.raabiha.com/login` → **HTTP 200**
- `storage/logs/laravel.log` → **0 error baru** pasca-deploy.

---

## 3. Panduan Rollback Cepat (Jika Diperlukan)
Jika ditemukan kendala tak terduga di produksi, jalankan sebagai user `raabiha` di `/home/raabiha/htdocs/www.raabiha.com`:
1. `/usr/bin/php8.5 artisan down`
2. `/usr/bin/php8.5 artisan migrate:rollback --step=2 --force` *(atau restore dari `/home/raabiha/backups/db_pre_v1.1.0_20261123.sql.gz`)*
3. `git checkout tags/v1.0.7`
4. `COMPOSER_MEMORY_LIMIT=-1 /usr/bin/php8.5 $(command -v composer) install --no-dev --optimize-autoloader`
5. `npm run build && /usr/bin/php8.5 artisan optimize:clear && /usr/bin/php8.5 artisan optimize`
6. `/usr/bin/php8.5 artisan up`

---

## 4. Tindakan Keamanan Wajib User
Segera aktifkan kembali password / pengamanan SSH `root` pada `<VPS_CLOUDPANEL>` sekarang setelah sesi deploy selesai.
