# Brief: P0 — Fase 2: Penutupan Kebocoran Produk Kasir POS & Penegakan 2 Kanal (ADR-001)

- **Status:** closed (selesai 2026-11-23 — laporan: `docs/reports/2026-11-23-backend-p0-fase-2-kanal-dan-pos.md`)
- **Untuk:** role-backend
- **Dari:** role-asisten
- **Tanggal:** 2026-11-23
- **Disetujui user:** ya (Opsi A disetujui; **Izin Delegasi Berkelanjutan** disetujui — boleh dieksekusi sampai seluruh kriteria selesai & test lulus tanpa berhenti di setiap langkah kecil)

## Tujuan
Menutup total kebocoran produk buatan kasir POS (`pos_only` / `is_custom = true`) ke website E-Commerce, meniadakan opsi `'both'` dari seluruh jalur input/bulk/import/export/default DB sesuai ADR-001 sambil memfasilitasi admin memilah produk legacy `'both'` secara bulk (Opsi A), serta memperbaiki bug stok fiktif pada auto-save produk kustom di POS.

## Konteks
- Rujukan laporan audit:
  - [`docs/reports/2026-11-23-inspektur-audit-fase-3-kanal-ecommerce-vs-pos.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-3-kanal-ecommerce-vs-pos.md) (Temuan F3-01, F3-02, F3-03)
- Rujukan keputusan:
  - [`docs/decisions.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/decisions.md) (ADR-001: Peniadaan kanal `'both'`, hanya `online_only` atau `pos_only`)
  - Keputusan User (Opsi A): Sebagai developer kita tidak menebak mana produk online vs POS dari 57 data lama berstatus `'both'`. Maka query baca sementara tetap mentoleransi data legacy `'both'` agar tidak langsung hilang, namun seluruh jalur buat/ubah/bulk/impor/ekspor/default DB dikunci ke 2 kanal (`online_only` / `pos_only`) dan Admin diberi filter + badge *"Legacy (Perlu Dipilah)"* agar klien bisa mencentang bulk dan memindahkannya sendiri.
- Profil risiko: **Standar–Ketat** (Zero Data Loss — jangan mengubah status 57 data legacy `'both'` secara otomatis).

## Yang diminta
1. **Tutup Total Kebocoran Produk Kasir (`pos_only` & `is_custom = true`) di E-Commerce:**
   - Tambahkan helper/scope terpusat di [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php) (misal `scopeVisibleOnline($query)` dan `scopeVisibleInPos($query)`):
     - `scopeVisibleOnline`: `where('is_active', true)->where('is_hidden', false)->where('is_custom', false)->whereIn('channel_visibility', ['online_only', 'both'])` *(secara tegas memblokir `pos_only` DAN `is_custom = true`, sambil tetap mentoleransi legacy `'both'` sesuai Opsi A)*.
     - `scopeVisibleInPos`: `where('is_active', true)->whereIn('channel_visibility', ['pos_only', 'both'])` *(tetap membaca produk kasir `pos_only` & `is_custom`, serta legacy `'both'`)*.
   - Terapkan `scopeVisibleOnline()` (atau filter setara) di:
     - [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php) (`$latestProducts` dan `$featuredProducts` Lookbook).
     - [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) (`mount()` wajib menolak `pos_only` atau `is_custom = true` dengan `404` / `firstOrFail()`, dan query produk terkait juga wajib memblokir `is_custom = true` & `pos_only`).
     - [`app/Livewire/Shop.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Shop.php) (tambahkan `where('is_custom', false)` pada query katalog).
     - [`app/Livewire/Search.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Search.php) (tambahkan `where('is_custom', false)` pada query pencarian).
2. **Hapus Opsi `'both'` dari Seluruh Jalur Input/Bulk/Import/Export + Filter Legacy di Admin (ADR-001):**
   - [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php):
     - Pada Bulk Action `change_channel_visibility`: hanya sediakan 2 opsi (`online_only` => E-Commerce Saja, `pos_only` => POS Kasir Saja). Hapus `'both'`.
     - Pada kolom badge `channel_visibility` dan `SelectFilter::make('channel_visibility')`: tampilkan label/badge peringatan jelas untuk data lama `'both'` (misal `"Legacy (Perlu Dipilah)"` berwarna `warning`) agar admin mudah memfilter 57 produk lama, mencentang serentak, lalu memindahkannya via Bulk Action.
   - [`app/Filament/Imports/ProductImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php) & [`app/Filament/Imports/ProductVariantImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductVariantImporter.php):
     - Hapus `'both'` dari opsi validasi & fallback; defaultkan ke `'online_only'` (dan jika `is_custom` bernilai true, paksa `'pos_only'`).
   - [`app/Jobs/ExportProductsCsvJob.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php), [`app/Filament/Resources/Products/Pages/ListProducts.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Pages/ListProducts.php), [`app/Filament/Pages/ExportData.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Pages/ExportData.php):
     - Hapus fallback/opsi `'both'` dari filter/ekspor (gunakan default `'online_only'`, tetap tampilkan nilai aktual jika data DB masih legacy `'both'` saat diekspor atau petakan dengan aman).
   - **Migrasi Non-Destruktif Default Kolom DB:**
     - Buat migrasi baru untuk mengubah default kolom `channel_visibility` pada tabel `products` dan `product_variants` dari `'both'` menjadi `'online_only'` *(hanya ubah default kolom; DILARANG meng-update 57 baris data eksisting sesuai Opsi A)*. Pastikan migrasi aman di SQLite (untuk test) maupun MySQL (untuk produksi).
3. **Perbaikan Bug Stok Auto-Save Produk Kustom di POS:**
   - [`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L58-L88):
     - Saat kasir membuat produk kustom dengan `save_to_catalog = true` di dalam transaksi penjualan yang langsung laku `$qty`, pastikan produk tersimpan dengan `channel_visibility = 'pos_only'`, `is_custom = true`, dan stok akhirnya akurat `0` (bukan meninggalkan stok fiktif `$qty` karena `locked_model => null`).
   - Verifikasi [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1359-L1370) bahwa pembuatan produk kustom manual juga selalu menetapkan `channel_visibility = 'pos_only'` dan `is_custom = true`.
4. **Automated Feature Tests:**
   - Buat feature test baru (misal `tests/Feature/ChannelIsolationAndPosCustomProductTest.php`) yang membuktikan:
     1. Produk buatan kasir (`pos_only` & `is_custom = true`) **tidak muncul** di Home (`/`), Shop (`/shop`), Search, dan **ditolak `404`** di ProductDetail (`/product/{slug}`).
     2. Produk `online_only` tetap tampil normal di Home, Shop, Search, ProductDetail, dan **tidak muncul** di katalog POS.
     3. Produk legacy `'both'` (non-custom) masih tetap tampil sementara di Web & POS (sesuai Opsi A).
     4. `ProductImporter` tidak lagi memakai fallback `'both'` (default `'online_only'`).
     5. Auto-save produk kustom di `PosTransactionService` menghasilkan `channel_visibility = 'pos_only'`, `is_custom = true`, dan `stock = 0` (tidak menyisakan stok fiktif).

## Di luar cakupan
- **JANGAN** mengubah 57 data legacy `'both'` yang sudah ada di DB secara otomatis (diserahkan ke klien via Bulk Action).
- **JANGAN** menjalankan `composer update` / `npm audit fix` (itu masuk **P0 — Fase 3**).
- **JANGAN** mengubah perilaku Mobile Bottom Sheet, `is_active` varian, atau batas qty `addToCart()` (itu masuk **P1**).
- **JANGAN** melakukan push/deploy ke VPS produksi tanpa persetujuan eksplisit user.

## Kriteria selesai
- [ ] Scope `visibleOnline` & `visibleInPos` tersedia di `Product.php` dan diterapkan di `home.blade.php`, `ProductDetail.php`, `Shop.php`, `Search.php`, dan `PosManager.php`.
- [ ] Produk kasir (`pos_only` / `is_custom = true`) terisolasi 100% dari E-Commerce (termasuk `404` di URL detail produk).
- [ ] Bulk Action `change_channel_visibility` hanya punya 2 opsi (`online_only` & `pos_only`), ditambah filter & badge *"Legacy (Perlu Dipilah)"* untuk data `'both'` di `ProductsTable.php`.
- [ ] Seluruh Importer/Exporter/Default DB bebas dari fallback `'both'` (default `'online_only'`).
- [ ] Bug stok auto-save produk kustom di `PosTransactionService.php` diperbaiki.
- [ ] Feature test baru & seluruh test terkait (`ChannelIsolationAndPosCustomProductTest`, `ProductImageSingleSourceOfTruthTest`, `VariantImageMatchingTest`, `ShopPageTest`, `CartPageTest`, `ProductBuyNowTest`, `SecurityHardeningTest`) lulus 100% hijau.
- [ ] `php -l` bersih, `vendor/bin/pint --test` bersih pada file baru, `CHANGELOG.md` diperbarui di `[Unreleased]`, handoff `006` ditutup, dan laporan tersimpan di `docs/reports/2026-11-23-backend-p0-fase-2-kanal-dan-pos.md`.

## Pertanyaan terbuka
- *(Semua sudah dikonfirmasi user: Opsi A untuk data legacy `'both'`, dan Izin Delegasi Berkelanjutan disetujui).*
