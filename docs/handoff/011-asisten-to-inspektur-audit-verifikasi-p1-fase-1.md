# Brief: Audit Verifikasi P1 Fase 1 (Logika Varian Aktif, Guard Stok Keranjang & Admin Varian)

- **Untuk:** `role-inspektur`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Mode:** **Audit-Only (100% Read-Only — DILARANG mengubah satu baris kode pun)**
- **Status:** `closed` *(Laporan audit: `docs/reports/2026-11-23-inspektur-audit-verifikasi-p1-fase-1.md`)*
- **Disetujui user:** ya (permintaan eksplisit user sebelum melanjutkan ke P1 Fase 2)

## Tujuan
Memverifikasi secara forensik dan skeptis bahwa seluruh implementasi **P1 Fase 1** pada `ProductDetail.php` dan `VariantsRelationManager.php` bebas dari celah logika bisnis, *edge case* stok/varian, N+1 query, maupun regresi terhadap fitur P0 dan fitur eksisting sebelum kita lanjut ke P1 Fase 2 (`role-frontend`).

## Konteks
- Handoff asal: [`docs/handoff/009-asisten-to-backend-p1-fase-1-varian-stok-dan-admin.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/009-asisten-to-backend-p1-fase-1-varian-stok-dan-admin.md)
- Laporan implementasi: [`docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md)
- Berkas yang diubah di P1 Fase 1:
  1. [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php)
  2. [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php)
  3. [`tests/Feature/ProductDetailVariantAndCartGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/ProductDetailVariantAndCartGuardTest.php)
- Handoff P1 Fase 2 yang sedang ditahan menunggu hasil audit ini: [`docs/handoff/010-backend-to-frontend-p1-fase-2-mobile-bottom-sheet-dan-ui-stok.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/010-backend-to-frontend-p1-fase-2-mobile-bottom-sheet-dan-ui-stok.md)

## Yang diminta (Checklist Audit Forensik)
1. **Audit Isolasi Varian Non-Aktif (`is_active = false`):**
   - Verifikasi seluruh jalur di `ProductDetail.php` (`availableAttributes`, `isOptionAvailable`, `selectOption`, `getFirstVariantWithImage`, `getMatchedVariant`, `priceRange`, `addToCart`, `buyNow`, `render` JSON-LD).
   - Cek *edge case*: bagaimana jika produk memiliki `has_variants = true` tetapi **semua variannya** `is_active = false`, atau produk memiliki 2 atribut (Warna + Ukuran) dan hanya sebagian kombinasi yang aktif?
2. **Audit Guard Akumulasi Stok & Batas Kuantitas (`maxOrderableQuantity`, `incrementQuantity`, `updatedQuantity`, `addToCart`, `buyNow`):**
   - Cek *edge case*: stok `0`, input qty negatif/nol/string, perpindahan dari varian stok besar ke stok kecil atau stok `0`, klik `addToCart()` berulang untuk guest session maupun user login, serta perilaku `buyNow()` bila item yang sama sudah penuh di keranjang.
   - Cek apakah ada *race condition* atau celah antara `ProductDetail.php` dan `Cart.php` (catat bila ada temuan di `Cart.php` tanpa mengubah kode).
3. **Audit Reset Galeri saat Deselect & Kesiapan Kontrak untuk P1 Fase 2:**
   - Verifikasi `selectOption()`, `activeGalleryIndex`, `activeGalleryImageUrl`, dan event `change-gallery-image` untuk skenario: pilih 1 atribut, pilih 2 atribut lalu deselect salah satu, deselect semua atribut, dan varian tanpa `media_id`.
   - Verifikasi apakah kontrak di handoff `010` sudah 100% selaras dengan implementasi `ProductDetail.php` dan `product-detail.blade.php` eksisting.
4. **Audit Admin `VariantsRelationManager.php`:**
   - Cek `Toggle::make('is_active')`, `ToggleColumn::make('is_active')`, `TernaryFilter`, dan `ImageColumn::make('thumbnail')` (apakah ada N+1 query saat varian tidak punya `media_id` dan fallback ke `$ownerRecord->images`?).
   - Cek logika anti-duplikasi `generate_variants` (termasuk urutan kombinasi berbeda, atribut kosong, atau kombinasi parsial yang sudah ada di DB).
5. **Verifikasi Independen & Regresi:**
   - Jalankan test suite secara mandiri (`php artisan test --filter=...`) dan pastikan tidak ada file di luar cakupan (`product-detail.blade.php`, migrasi DB) yang ikut berubah.

## Di luar cakupan
- **DILARANG mengubah satu baris kode pun** (100% Read-Only).
- Tidak perlu mengaudit ulang P0 atau modul di luar cakupan P1 Fase 1 (kecuali ada interaksi langsung yang memicu regresi).

## Kriteria selesai
1. Laporan audit tersimpan di `docs/reports/2026-11-23-inspektur-audit-verifikasi-p1-fase-1.md`.
2. Memuat klasifikasi temuan (Kritis / Tinggi / Sedang / Rendah) berbasis bukti konkret `file:baris` dan hasil perintah tes independen.
3. Memberikan verdict jelas: **LAYAK LANJUT KE P1 FASE 2** atau **PERLU PERBAIKAN DULU DI `role-backend`** (dengan daftar perbaikan terukur).

## Pertanyaan terbuka
- Tidak ada.
