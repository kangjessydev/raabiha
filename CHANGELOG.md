# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Ditambahkan
- Automated feature test `tests/Feature/ProductDetailVariantAndCartGuardTest.php` dan laporan pengerjaan P1 Fase 1 (`docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md`).
- Properti `activeGalleryIndex` beserta computed `activeGalleryImageUrl` & `maxOrderableQuantity` pada `app/Livewire/ProductDetail.php` sebagai kontrak sinkronisasi UI Mobile Bottom Sheet & kontrol kuantitas stok riil (P1 Fase 2).
- Laporan pembaruan keamanan dependensi P0 Fase 3 (`docs/reports/2026-11-23-backend-p0-fase-3-patch-cve-dependensi.md`).
- Scope kanal terpusat `scopeVisibleOnline()` & `scopeVisibleInPos()` pada `app/Models/Product.php` untuk mengisolasi produk kasir POS (`pos_only` & `is_custom = true`) dari website E-Commerce sambil tetap mentoleransi data legacy `both` sementara (Opsi A).
- Migrasi non-destruktif `database/migrations/2026_11_23_070000_set_channel_visibility_default_to_online_only.php` untuk mengubah default kolom `channel_visibility` pada `products` & `product_variants` dari `both` menjadi `online_only` tanpa mengubah data lama.
- Automated feature test `tests/Feature/ChannelIsolationAndPosCustomProductTest.php` dan laporan pengerjaan P0 Fase 2 (`docs/reports/2026-11-23-backend-p0-fase-2-kanal-dan-pos.md`).
- Layanan resolusi gambar produk & varian terpusat (*Single Source of Truth*) `app/Services/ProductImageResolver.php` beserta accessor `primary_image_url` / `resolveGallery()` pada `Product` dan `image_url` / `resolveImageUrl()` pada `ProductVariant` (seragam memakai `\App\Models\Media` dan fallback `placeholder.webp` 16 KB).
- Perintah Artisan idempoten `php artisan products:normalize-images` (`app/Console/Commands/NormalizeProductImages.php`) dengan opsi `--dry-run` untuk memulihkan `products.images` dari string URL/path kembali ke ID Curator tanpa kehilangan data (*zero data loss*).
- Automated feature test komprehensif `tests/Feature/ProductImageSingleSourceOfTruthTest.php` untuk verifikasi resolusi gambar produk berdiri sendiri & varian di Shop, Detail Produk, Cart, Checkout, normalisasi gambar, serta proteksi impor CSV.
- Laporan pengerjaan P0 Fase 1 (`docs/reports/2026-11-23-backend-p0-fase-1-gambar-curator.md`).
- Laporan audit logika bisnis & inkonsistensi penamaan Admin Dashboard (`docs/reports/2026-11-09-admin-logic-audit.md`).
- Catatan keputusan arsitektur ADR-001 (peniadaan kanal `both`) & ADR-002 (reseller sebagai fitur lanjutan) di `docs/decisions.md`.
- Tiket backlog perbaikan lanjutan BKL-004 s/d BKL-012 di `docs/backlog.md`.

### Diubah
- Perbaikan P1 Fase 2 pada `resources/views/livewire/product-detail.blade.php`: sinkronisasi foto preview Mobile Bottom Sheet (`#bs-product-image`) secara reaktif menggunakan `$this->activeGalleryImageUrl` (mengikuti varian aktif & kembali ke foto pertama saat *deselect*) dengan dimensi eksplisit `width="48" height="64"` (Zero CLS), penyelarasan status disabled & `max` tombol kuantitas (`- / +`) di Desktop & Mobile mengikuti `$this->maxOrderableQuantity` (stok riil), penambahan `tabular-nums`, `aria-label`, `focus-visible:ring-2`, penghapusan `transition-all` pada tombol opsi varian, serta perbaikan `@if($isAvailable || $isSelected)` agar opsi terpilih tetap bisa di-*deselect*.
- Perbaikan temuan audit P1 Fase 1 (`T-01`, `T-02`, `S-01`, `S-02`, `R-01`): `Product::getIsOutOfStockAttribute()` & `getTotalStockAttribute()` kini hanya menghitung varian aktif (`is_active = true`), `Cart::incrementQuantity()` kini mengecek stok riil & status aktif varian sebelum menaikkan qty, `VariantsRelationManager` melakukan batch preload media produk induk untuk menghilangkan N+1 fallback thumbnail dan men-deduplikasi nama opsi pada `generate_variants`, serta `ProductDetail::updatedQuantity()` meng-clamp qty ke `1` saat stok `0`.
- Perbaikan P1 Fase 1 pada `app/Livewire/ProductDetail.php`: filter `is_active = true` secara konsisten di seluruh method pemilihan varian (`availableAttributes`, `isOptionAvailable`, `getFirstVariantWithImage`, `getMatchedVariant`, `priceRange`, JSON-LD `inStock`), guard akumulasi stok keranjang di `addToCart()`, batas `incrementQuantity()`/`updatedQuantity()` mengikuti stok riil + auto-clamp saat ganti varian, serta reset galeri ke `index 0` saat *deselect* varian.
- Perbaikan P1 Fase 1 pada `app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`: penambahan kontrol `is_active` (Toggle form, ToggleColumn tabel, & TernaryFilter), kolom thumbnail gambar varian berbasis `ProductImageResolver` dengan eager loading `media`, serta pencegahan duplikasi kombinasi atribut pada aksi `generate_variants` (`BKL-007` & `BKL-008`).
- Perbaikan temuan audit P0 (`F-01` & `F-02`): `app/Http/Controllers/SitemapController.php` kini memakai `Product::visibleOnline()` agar produk `pos_only` & `is_custom` tidak bocor ke `/sitemap.xml`, dan bagian *Related Products* di `resources/views/livewire/product-detail.blade.php` kini memakai `ProductImageResolver::preloadMedia()` + `resolvePrimaryImageUrl()` (eliminasi N+1 `Media::find()`).
- Patch keamanan dependensi P0 Fase 3 (`composer.lock`, `package-lock.json`, & aset publik Filament/Curator): menambal 49 Composer security advisories menjadi **0** (`laravel/framework 13.12.0 -> 13.43.0`, `filament/filament 5.6.6 -> 5.12.0`, `livewire/livewire 4.3.1 -> 4.4.9`, `awcodes/filament-curator 5.0.7 -> 5.6.7`, `symfony/* 8.1.0 -> 8.1.11`, `league/commonmark 2.8.2 -> 2.10.7`, `guzzlehttp/guzzle 7.10.6 -> 8.6.0`, `ueberdosis/tiptap-php 2.1.0 -> 2.2.1`) serta 4 NPM High advisories menjadi **0** (`npm audit --omit=dev`).
- Penutupan kebocoran produk kasir POS ke E-Commerce: `home.blade.php` (produk terbaru & lookbook), `ProductDetail.php` (`mount()` kini mengembalikan 404 untuk `pos_only` / `is_custom = true`, serta `relatedProducts()`), `Shop.php`, `Search.php`, dan `PosManager.php` kini memakai `Product::visibleOnline()` / `Product::visibleInPos()`.
- Penegakan 2 kanal (ADR-001): menghapus opsi/fallback `'both'` pada Bulk Action `ProductsTable.php`, `ProductImporter.php`, `ProductVariantImporter.php`, `ExportProductsCsvJob.php`, `ListProducts.php`, dan `ExportData.php`, serta menambahkan badge & filter *"Legacy (Perlu Dipilah)"* di Admin Table untuk memudahkan pemilahan bulk data lama.
- Perbaikan bug stok fiktif auto-save produk kustom di `app/Services/PosTransactionService.php`: `locked_model` kini terhubung ke produk kustom yang baru disimpan sehingga stok langsung dikurangi ke `0` dan tercatat di `StockLog`.
- Pengamanan `CuratorPicker::make('images')` pada `app/Filament/Resources/Products/Schemas/ProductForm.php`: menerjemahkan URL/path legacy ke ID Curator saat hidrasi dan mencegah penghapusan `products.images` menjadi `NULL` saat tombol Simpan diklik.
- Perbaikan `app/Filament/Imports/ProductImporter.php`: kolom `Gambar` kini mengonversi URL/path CSV ke ID Curator saat impor dan memakai `ignoreBlankState()` agar sel kosong tidak menimpa gambar eksisting.
- Konsolidasi resolusi gambar di 6 lokasi (`ProductsTable.php`, `shop.blade.php`, `home.blade.php`, `ProductDetail.php`, `cart.blade.php`, `checkout.blade.php`) memakai `ProductImageResolver` dengan batch preloading (eliminasi N+1 query), prioritas foto varian di Cart & Checkout, serta eager loading `items.variant.media` di `Cart.php` dan `Checkout.php`.
- Pembaruan spesifikasi peran `/role-inspektur` (`.agents/skills/role-inspektur/SKILL.md` & `.agents/roster.md`) menjadi auditor forensik & skeptis dengan 5 pilar pemeriksaan (logika bisnis, semantik/penamaan/duplikasi, sinkronisasi FE-BE-DB, struktur/ukuran file ≤300 baris & efisiensi sintaks/DB, anti-AI-slop), *Zero-Assumption Gate*, serta *Context-Budget Guard* (pembagian fase ≤50% konteks input).

### Dihapus & Alasan
- *(Belum ada)*

## [1.0.7] - 2026-11-09

### Ditambahkan
- Automated feature test untuk sinkronisasi gambar varian dan slider frontend (`tests/Feature/VariantImageMatchingTest.php`).
- Berkas rencana backlog perbaikan fitur POS (`docs/backlog.md`) mencakup otorisasi diskon manual, null-safe supervisor void, dan rate limiting PIN.
- Laporan audit menyeluruh keamanan & integritas data (`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`).
- Tiket handoff perbaikan keamanan & hardening integritas data (`docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`).
- Laporan pengerjaan perbaikan backend (`docs/reports/2026-11-08-backend-security-and-data-hardening.md`).
- Automated feature test untuk verifikasi keamanan (`tests/Feature/SecurityHardeningTest.php`).

### Diubah
- Resolusi gambar varian pada `VariantsRelationManager.php` kini mendukung gambar produk utama dalam format ID Media numerik maupun URL/path string, mencocokkan media dari tabel `curator` berdasarkan path atau nama file.
- Pemetaan galeri pada `ProductDetail.php` (`mount()`) kini menjaga keselarasan urutan 1-ke-1 antara `$galleryUrls` dan `$galleryMediaIds`, serta menambahkan penanganan fallback pencocokan via path/URL media pada `selectOption()`, memastikan slider gambar otomatis meluncur ke foto varian yang dipilih user.
- Proteksi otorisasi rute invoice (`/invoice/{orderNumber}`) dan konfirmasi pesanan (`/order-success`) agar data pribadi tidak bocor ke publik tanpa autentikasi/sesi pesanan yang sah.
- Pengetatan akses data pengajuan refund pada `Account::openRefundStatus` dengan filter kepemilikan pengguna (`user_id`).
- Pembatasan mutasi item keranjang belanja pada `Cart::incrementQuantity`, `decrementQuantity`, dan `removeItem` agar terikat ke cart aktif pengguna/sesi.
- Validasi status aktif, saluran penggunaan (`usable_channel`), kuota, dan rekalkulasi nilai diskon voucher transaksi POS di sisi server melalui `PosTransactionService` dan `VoucherService`.

### Dihapus & Alasan
- *(Belum ada penghapusan berkas/fitur)*

## [1.0.6] - 2026-11-08

### Ditambahkan
- Dokumentasi pemetaan arsitektur proyek awal (`docs/project-brief.md`, `docs/project-map.md`, `docs/glossary.md`).
- Laporan audit awal baseline pengujian (`docs/reports/2026-11-08-onboard-initial.md`).
- Tiket handoff stabilisasi test suite (`docs/handoff/001-onboard-to-backend.md`).
- Tiket brief handoff perbaikan resolusi gambar & kalkulasi stok varian (`docs/handoff/002-asisten-to-backend.md`).
- Tiket handoff audit mutu dan keamanan (`docs/handoff/003-backend-to-inspektur.md`).
- Laporan pengerjaan backend (`docs/reports/2026-11-08-backend-image-and-variant-stock-fix.md`).
- Laporan audit inspektur mutu & keamanan alur data (`docs/reports/2026-11-08-inspektur-audit-image-and-stock.md`).

### Diubah
- Perbaikan resolusi URL string gambar pada `ProductsTable.php`, `ProductDetail.php`, `shop.blade.php`, dan `home.blade.php`.
- Penambahan eager loading `variants` dan format total stok varian pada `ProductsTable.php`.
- Penyesuaian kalkulasi kartu produk habis pada `ProductStatsWidget.php`.

### Dihapus & Alasan
- *(Belum ada penghapusan berkas/fitur)*
