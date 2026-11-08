# Laporan: Onboard — Initial Codebase & Architecture Baseline

- **Tanggal:** 2026-11-08
- **Brief/Handoff Terkait:** Pemanggilan langsung via `/role-onboard`
- **Mode:** Normal

## Ringkasan
Telah dilakukan audit dan pemetaan menyeluruh terhadap repositori **Raabiha E-Commerce & POS**. Sistem berjalan pada stack Laravel 13, Filament v5, Livewire 3, dan Tailwind CSS v4. Seluruh arsitektur, dependensi runtime (dipakai vs live registry), titik masuk routing multi-domain (`admin.`, `pos.`, dan root), serta kondisi baseline test suite telah dipetakan secara akurat tanpa mengubah satu baris pun kode produksi. Dokumen `docs/project-brief.md`, `docs/project-map.md`, dan `docs/glossary.md` telah diterbitkan.

## Yang Dikerjakan
1. **Deteksi Runtime & Dependensi (Live Registry Ping per 2026-11-08):**
   - PHP: `8.4.1` (CLI)
   - Laravel: `v13.12.0` (Packagist latest: `v13.40.0`)
   - Filament: `v5.6.6` (Packagist latest: `v5.11.2`)
   - Tailwind CSS: `^4.3.0` (NPM latest: `4.3.5`)
   - Vite: `^8.0.0` (NPM latest: `8.3.6`)
   - Puppeteer: `^25.1.0` (NPM latest: `25.16.0`)
2. **Pemeriksaan Baseline Pengujian:**
   - Menjalankan `php artisan test` dengan hasil: `tests: 109, passed: 92, failed: 17, assertions: 394`.
   - 17 kegagalan terkonsentrasi pada:
     - `AdminLoginTest.php` & `KasirAccessTest.php` (8 tes gagal 404 karena route subdomain `admin.` dan `pos.` dipanggil tanpa simulasi domain host di test case).
     - `PosLoyaltyTest.php` (6 tes gagal karena perbedaan batas/threshold perhitungan stempel dan poin).
     - `PosReceiptSettingsTest.php` (1 tes gagal karena format jumlah cap: "Total Cap: 3 dari 12 Cap" vs ekspektasi "9 Cap").
     - `PosShiftScheduleTest.php` (2 tes gagal 404).
3. **Penyusunan Peta Dokumen:**
   - Diterbitkan `docs/project-brief.md` (memuat profil risiko 'Ketat' dan baseline).
   - Diterbitkan `docs/project-map.md` (memetakan modul UI, Service, DB, dan isolasi stok).
   - Diterbitkan `docs/glossary.md` (menyelaraskan terminologi resmi POS, e-commerce, dan peran).

## Checklist SOP (Peran Read-Only)
- [x] Hanya membaca kode dan tidak mengubah file di luar `docs/`.
- [x] Menyajikan bukti konkret berbasis output CLI dan `file:baris`.
- [x] Tidak menebak versi; menggunakan live ping registry resmi.
- [x] Menjaga integritas git status dan arsitektur yang sudah ada.

## Belum Terverifikasi
- Verifikasi perbaikan 17 unit test yang gagal (diserahkan ke handoff tim pembangun/backend).
- Lingkungan produksi aktual CloudPanel / VPS (hanya diverifikasi riwayatnya via `LAPORAN_TEKNIS_CLAUDE.md`).

## Log Penyimpangan
| Langkah/Pertanyaan Tambahan | Alasan | Disetujui User? |
|---|---|---|
| Eksekusi `php artisan test` & ping registry | Memverifikasi kondisi baseline nyata proyek lanjutan | Disetujui user |

## Handoff yang Dibuat
- `docs/handoff/001-onboard-to-backend.md`: Rekomendasi perbaikan konfigurasi routing subdomain test suite dan penyesuaian assertions tes loyalty POS.

## Rangkuman Sesi
Sesi onboarding awal tuntas 100%. Peta dan fondasi arsitektur proyek Raabiha E-Commerce & POS telah tercatat rapi di `docs/`. Tim siap melanjutkan ke fase pemeliharaan atau pengembangan fitur berikutnya.
