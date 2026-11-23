# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.1.0] - 2026-11-23

### Ditambahkan
- Automated feature test hardening transaksi & fundamental pra-rilis `v1.1.0`: `tests/Feature/EcommerceCheckoutAndWebhookHardeningTest.php`, `tests/Feature/PosTransactionHardeningTest.php`, `tests/Feature/MiniCartGuestMergeAndVoucherDedupTest.php`.
- Helper `Cart::mergeGuestCartToUser()` di `app/Models/Cart.php` untuk menggabungkan keranjang belanja guest ke akun user saat Login/Register (`K-03`), serta method `Checkout::clearDestination()` (`F-03`) dan `MiniCart::proceedToCheckout()` (`K-02`).
- Migrasi idempoten P4 Fase 1 (`database/migrations/2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables.php`, `BKL-010`) yang menambahkan 6 composite performance indexes (`idx_products_channel_active`, `idx_variants_product_active_stock`, `idx_orders_source_payment_created`, `idx_orders_status_created`, `idx_carts_user_buy_now`, `idx_carts_session_buy_now`) serta test verifikasi `tests/Feature/DatabasePerformanceIndexesAndCleanupTest.php`.
- Automated feature test `tests/Feature/PosManualDiscountServerGuardTest.php` dan laporan pengerjaan P3 Fase 2 (`docs/reports/2026-11-23-backend-p3-fase-2-pos-manual-discount-server-guard.md`).
- Automated feature test `tests/Feature/P3WidgetStockAndPosVoidNullSafeTest.php` dan laporan pengerjaan P3 Fase 1 (`docs/reports/2026-11-23-backend-p3-fase-1-widget-stok-dan-void-null-safe.md`).
- Catatan keputusan arsitektur ADR-003 di `docs/decisions.md` (penangguhan `BKL-003` rate limiting PIN supervisor POS atas permintaan klien).
- Automated feature test `tests/Feature/BuyNowIsolationAndCartStockSyncTest.php` dan laporan pengerjaan P2 Fase 2A (`docs/reports/2026-11-23-backend-p2-fase-2a-buy-now-isolation-dan-cart-stock-sync.md`).
- Kontrak publik `$stockNotices` dan `$itemAvailability` beserta method `syncCartStockState()` pada `app/Livewire/Cart.php` untuk sinkronisasi & auto-clamp stok keranjang (Opsi A — `BKL-013`).
- Automated feature test `tests/Feature/AdminOrderVariantAndProductStockSortTest.php` dan laporan pengerjaan P2 Fase 1 (`docs/reports/2026-11-23-backend-p2-fase-1-order-variant-dan-stok-admin.md`).
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
- Hardening Transaksi & Fundamental Pra-Rilis `v1.1.0` (Handoff `023`, `024`, `025`):
  - `app/Livewire/Checkout.php` & `resources/views/livewire/checkout.blade.php`: mengunci `#[Locked]` pada `$shippingRates` & `$totalWeight` (`T-01`), menyelaraskan total `order_items` Tripay saat ada diskon/voucher agar sama dengan `amount` (`T-04`), validasi `is_active`/kanal online di dalam `lockForUpdate()` & validasi `payment_method` online aktif (`S-02`), serta mengganti `$wire.set('shipping_cost', 0)` pada tombol Ganti/Hapus Lokasi dengan `$wire.clearDestination()` agar tidak memicu HTTP 500 Livewire 4 (`F-03`).
  - `app/Http/Controllers/Webhook/TripayWebhookController.php` & `XenditWebhookController.php`: mencegah webhook `EXPIRED`/`FAILED` menimpa pesanan yang sudah `paid`, serta menandai pembayaran telat (`PAID` pada order `cancelled`) untuk verifikasi manual admin (`S-01`).
  - `app/Livewire/PosManager.php`: memperbaiki *double-checked locking* idempotency di `processCheckout()` (`T-02`), menambahkan `lockForUpdate()` + flag `is_stock_restored` di `voidOrder()` untuk mencegah double-void & double-restock saat order dihapus (`T-03`), serta memperbaiki kalkulasi Z-Report tutup shift (`closeSession()`) agar tidak mengurangkan void tunai shift yang sama dua kali dan menyertakan porsi tunai split payment (`K-01`).
  - `app/Services/PosTransactionService.php`: memaksa harga item katalog POS & barang tukar 100% dari server (`resolveServerCatalogPrice()` — anti manipulasi Console Rp 100rb → Rp 0, `F-01`), memvalidasi kecukupan bayar tunai & split di server (`F-02`), serta menolak kuantitas `<= 0` (`S-03`).
  - `app/Livewire/MiniCart.php` & `resources/views/livewire/mini-cart.blade.php`: menutup IDOR via scoping `$this->cart->items()`, menambah guard stok riil & `is_active`, menyelaraskan harga promo `effective_price` + gambar Curator `ProductImageResolver`, serta mereset `checkout_is_buy_now` saat klik Lanjut Pembayaran (`K-02`).
  - `app/Livewire/Auth/Login.php` & `Register.php`: menggabungkan keranjang guest ke akun user saat Login/Register (`K-03`).
  - `app/Livewire/Account.php`: mengunci baris order dengan `lockForUpdate()` & menolak pembatalan jika `payment_status === 'paid'` (`S-04`).
  - `app/Services/VoucherService.php`: menambahkan deduplikasi kode voucher pada `verifyAndCalculate()` (`F-04`).
- Perbaikan aset publik & UX Detail Produk:
  - Mem-publish ulang aset publik `public/vendor/livewire/` (`manifest.json: 102cd394`) agar selaras dengan Livewire `4.4.9` & Filament `5.12.0` (menutup error JS `TypeError: h is not a function` pada `ToggleColumn` di `VariantsRelationManager`).
  - `app/Livewire/ProductDetail.php` & `resources/views/livewire/product-detail.blade.php`: menambahkan *Optimistic Instant Slide* (`0ms`) berbasis `getPredictedGalleryIndex()`, state Alpine lokal `optimisticSelected` + `wire:key` unik pada tombol opsi varian (menghilangkan border ganda saat klik cepat), filter `priceRange()` berdasarkan pilihan parsial (langsung menyempit ke harga varian terpilih saat warna diklik), sinkronisasi preview `#bs-product-image` `0ms`, serta menghapus `translate-y-0` permanen pada drawer Bottom Sheet.
  - `app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`: menyelaraskan helper text `Harga Promo (Diskon)` agar sesuai perilaku model `ProductVariant::getDiscountPriceAttribute()` (mewarisi harga promo produk induk bila dikosongkan).
  - `resources/views/livewire/account.blade.php`: memperbaiki atribut `x-transition` pada toast notifikasi alamat.
- Optimasi P4 Fase 2 (temuan minor `R-01` hasil audit P2 & P3):
  - `app/Services/PosTransactionService.php`: menggabungkan 3 query `SiteSetting` di `authorizeManualDiscount()` menjadi 1 query tunggal `whereIn(...)->pluck('value', 'key')`.
  - `app/Filament/Resources/Orders/Schemas/OrderForm.php`: menambahkan memoize ringan per request (`spl_object_id(request())`) pada `productHasVariants()` agar `visible()` & `required()` pada item repeater tidak mengulang query identik.
- Perbaikan P3 Fase 3 (`S-01` — `resources/views/livewire/cart.blade.php:73`): menyelaraskan tampilan harga per baris item keranjang agar menggunakan `effective_price` seperti kalkulasi subtotal di `app/Livewire/Cart.php`.
- Perbaikan P3 Fase 2 (`BKL-001` — Hardening Otorisasi Diskon Manual Kasir di Sisi Server):
  - `app/Services/PosTransactionService.php`: menambahkan `authorizeManualDiscount()` & `isSupervisorUser()` pada `completePosTransaction()` untuk memvalidasi ambang batas `pos_manual_discount_max_percent_without_pin` (default 20%) & `pos_manual_discount_max_rp_without_pin` (default Rp 50.000) di sisi server, mencegah diskon negatif, mewajibkan otorisasi supervisor bila melewati batas, dan mencatat `manual_discount_supervisor_id` di `payment_details`.
  - `app/Livewire/PosManager.php`: menyimpan token otorisasi sekali pakai di server-side session (`pos_supervisor_discount_auth`, TTL 15 menit) saat `verifySupervisorPin()` berhasil, membuang potensi pemalsuan `manual_discount_authorized_by` dari payload klien di `processCheckout()`, dan menghanguskan token pasca-transaksi sukses.
- Perbaikan P3 Fase 1:
  - `app/Filament/Resources/Products/Widgets/ProductStatsWidget.php` (`BKL-005`): menyelaraskan query `Stok Habis / Menipis` agar produk bervarian dihitung perlu restock bila memiliki minimal 1 varian aktif (`is_active = true`) dengan `stock <= 0` atau belum memiliki varian aktif sama sekali (varian non-aktif stok `0` tidak memicu false positive).
  - `app/Livewire/PosManager.php` (`BKL-002`): memasang null-safe `$voidActorLabel` pada `voidOrder()` (`Disetujui Supervisor: ...` vs `Oleh Kasir: ...`) agar pembatalan nota tanpa PIN supervisor (`pos_require_pin_for_void = false`) tidak crash `Attempt to read property "name" on null`.
- Perbaikan P2 Fase 2B (`BKL-013` Frontend) pada `resources/views/livewire/cart.blade.php`: menampilkan banner notifikasi `$stockNotices` (`role="alert"`, `aria-live="polite"`) untuk peringatan auto-clamp & item tidak tersedia, badge alasan (`Stok habis.` / `Varian sudah tidak aktif.`) & indikator `Sisa X pcs`, menonaktifkan checkbox & tombol `- / +` sesuai ketersediaan dan batas stok riil (`$itemAvailability`), serta kepatuhan anti-AI-slop (dimensi eksplisit `width="240" height="280"` + `loading="lazy"`, `tabular-nums`, `aria-label`, dan `focus-visible:ring-2`).
- Perbaikan P2 Fase 2A (`BKL-013` Backend):
  - `app/Livewire/ProductDetail.php`: memisahkan `buyNow()` dari `addToCart()` dengan `Cart` terpisah (`is_buy_now = true`) dan pengecekan stok riil khusus qty Beli Sekarang (mengabaikan isi/qty keranjang reguler) serta mengekstrak helper bersama `resolveSelectionForCart()`.
  - `app/Livewire/Checkout.php`: `getCartProperty()` kini membaca sumber cart sesuai flag `session('checkout_is_buy_now', false)` (dan `whereNull('user_id')` untuk guest), serta menggabungkan pembersihan cart & session (`checkout_item_ids`, `checkout_is_buy_now`) pasca-order ke helper `cleanupCheckedOutCart()`.
  - `app/Livewire/Cart.php`: menerapkan sinkronisasi stok & auto-clamp otomatis pada `mount()` dan `proceedToCheckout()` (menyesuaikan qty ke stok riil, otomatis uncheck & memblokir checkout item habis/non-aktif, serta mereset `checkout_is_buy_now = false` saat checkout reguler).
- Perbaikan P2 Fase 1 pada `app/Filament/Resources/Orders/Schemas/OrderForm.php` (`BKL-009`): `Select::make('product_id')` kini bersifat `->live()` dan otomatis mereset `product_variant_id = null` saat produk diganti; `Select::make('product_variant_id')` kini tersembunyi untuk produk non-varian (`->visible(...)` + `->dehydrated(true)`), wajib diisi untuk produk bervarian (`->required(...)`), memvalidasi kepemilikan varian terhadap `product_id` terpilih di sisi server, menandai varian non-aktif dengan suffix `(Non-Aktif)` serta mencegah pemilihan varian non-aktif pada pesanan baru sambil tetap mempertahankan keterbacaan/penyimpanan pesanan historis saat Edit.
- Perbaikan P2 Fase 1 pada `app/Filament/Resources/Products/Tables/ProductsTable.php` (`BKL-004`): mengganti `->sortable()` mentah pada kolom `stock` dengan custom SQL sorting berbasis `CASE WHEN products.has_variants = 1 THEN COALESCE((SELECT SUM(pv.stock) ... AND pv.is_active = 1), 0) ELSE COALESCE(products.stock, 0) END`, menambahkan `withSum` untuk `active_variants_stock_sum` di query tabel (anti-N+1), dan menyelaraskan tampilan `formatStateUsing` & `color` agar hanya menghitung stok varian aktif (`is_active = true`).
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
- Tabel database legacy `coupons` dan `salespages` (`Schema::dropIfExists` pada migrasi `2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables.php`, dengan izin eksplisit user 2026-11-23): **Alasan:** Kedua tabel sudah 0 referensi aktif di `app/`, `routes/`, dan `resources/views/` (digantikan oleh modul `vouchers` & halaman modern), sehingga dibersihkan untuk efisiensi skema database (`BKL-010`).

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
