# Brief: Audit Verifikasi End-to-End Hasil Pengerjaan P0 (Fase 1, Fase 2, Fase 3)

- **Status:** closed
- **Ditutup pada:** 2026-11-23 (Laporan: `docs/reports/2026-11-23-inspektur-audit-verifikasi-p0.md`, Verdict: LAYAK DENGAN CATATAN)
- **Untuk:** role-inspektur
- **Dari:** role-asisten
- **Tanggal:** 2026-11-23
- **Mode:** **Audit-Only (100% Read-Only — Dilarang mengubah satu baris kode pun)**
- **Disetujui user:** ya

## Tujuan
Melakukan audit forensik & skeptis atas seluruh perubahan **P0 (Fase 1–3)** sebelum dilakukan commit/rilis ke VPS produksi, memastikan tidak ada celah logika, regresi, pelanggaran ADR-001, data loss, N+1 query baru, atau sisa CVE kritis.

## Konteks
- Rujukan laporan pengerjaan yang harus diverifikasi ulang secara independen (jangan percaya klaim laporan tanpa cek `file:baris` & jalankan perintah verifikasi sendiri):
  1. **P0 Fase 1 (Gambar & Curator):** [`docs/reports/2026-11-23-backend-p0-fase-1-gambar-curator.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-backend-p0-fase-1-gambar-curator.md)
  2. **P0 Fase 2 (Kanal POS vs E-Commerce & Stok Custom):** [`docs/reports/2026-11-23-backend-p0-fase-2-kanal-dan-pos.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-backend-p0-fase-2-kanal-dan-pos.md)
  3. **P0 Fase 3 (Patch CVE Composer & NPM):** [`docs/reports/2026-11-23-backend-p0-fase-3-patch-cve-dependensi.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-backend-p0-fase-3-patch-cve-dependensi.md)
- Rujukan keputusan bisnis:
  - [`docs/decisions.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/decisions.md) (ADR-001 & ADR-002).
  - Opsi A (P0 Fase 2): 57 data legacy `'both'` di DB produksi sengaja tidak diubah otomatis agar klien dapat memilahnya sendiri via filter & Bulk Action Admin.

## Yang diminta (Fokus Audit Skeptis P0)
1. **Verifikasi P0 Fase 1 (Gambar Produk & Media Curator):**
   - Periksa [`ProductImageResolver.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/ProductImageResolver.php) & [`ProductForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php): apakah masih ada skenario di mana klik **Simpan** pada produk legacy URL menghapus `products.images` menjadi `NULL`?
   - Periksa kompatibilitas `CuratorPicker` setelah upgrade `awcodes/filament-curator` ke `5.6.7` (cek apakah `CuratorPicker` di versi baru mengubah mekanisme `afterStateHydrated`/`dehydrateStateUsing`!).
   - Periksa [`NormalizeProductImages.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Console/Commands/NormalizeProductImages.php) & [`ProductImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php): pastikan idempoten dan *zero data loss*.
   - Periksa konsolidasi 6 lokasi pembacaan gambar (`ProductsTable`, `shop.blade`, `home.blade`, `ProductDetail`, `cart.blade`, `checkout.blade`): cek apakah ada sisa `placeholder.png` atau N+1 `Media::find()` yang tertinggal.
2. **Verifikasi P0 Fase 2 (Isolasi Kanal POS vs E-Commerce):**
   - Periksa apakah masih ada jalur lain di E-Commerce (misal API/route/Livewire/Sitemap/Wishlist/Cart/Checkout) yang bisa membocorkan produk `pos_only` atau `is_custom = true`.
   - Periksa migrasi [`2026_11_23_070000_set_channel_visibility_default_to_online_only.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_11_23_070000_set_channel_visibility_default_to_online_only.php): apakah aman untuk MySQL produksi (tidak merusak 57 baris legacy `'both'`)?
   - Periksa [`PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php): apakah pengikatan `$lockedCustomProduct` pada auto-save produk kustom menimbulkan efek samping (misal pada void/return/stock log)?
3. **Verifikasi P0 Fase 3 (Keamanan Dependensi & Regresi):**
   - Verifikasi ulang secara langsung output `composer audit`, `npm audit --omit=dev`, `git diff --stat`, dan test suite.
   - Cek apakah upgrade `guzzlehttp/guzzle` (`7.10.6 -> 8.6.0`), `filament/filament` (`5.6.6 -> 5.12.0`), atau `awcodes/filament-curator` (`5.0.7 -> 5.6.7`) menimbulkan *breaking change* tersembunyi di fitur yang tidak tercakup test.

## Di luar cakupan
- **DILARANG** mengubah kode, migrasi, atau dependensi (Audit-Only).
- **JANGAN** mengaudit ulang fitur P1/P2 yang memang belum dikerjakan (Mobile Bottom Sheet, `variants.is_active`, batas qty `addToCart`, refactoring file gemuk).
- **JANGAN** melakukan push/deploy ke VPS.

## Kriteria selesai
- [ ] Seluruh 3 area P0 diaudit secara forensik berbasis bukti konkret (`file:baris` dan output perintah langsung).
- [ ] Keputusan status kesiapan rilis P0 dinyatakan tegas: **LAYAK RILIS / LAYAK DENGAN CATATAN / BLOKIR (WAJIB PERBAIKI DULU)**.
- [ ] Jika ditemukan celah/bug baru, klasifikasikan tingkat keparahannya dan siapkan rekomendasi handoff perbaikan ke `role-backend`.
- [ ] Laporan audit tersimpan di `docs/reports/2026-11-23-inspektur-audit-verifikasi-p0.md` dan handoff `008` ditutup.
