# Laporan: Inspektur — Audit Forensik Fase 3 (Pemisahan Kanal E-Commerce vs POS & Kebocoran Produk Kasir)

- **Tanggal:** 2026-11-23
- **Mode:** Audit-Only (100% Read-Only — Tidak mengubah satu baris kode pun)
- **Cakupan Fase 3:** Menjawab keluhan #3 (*opsi tampilkan E-Commerce vs POS nyampur lagi*) dan keluhan #4 (*pembuatan produk di sisi Kasir malah menambahkan/menampilkan produk ke E-Commerce juga*).

---

## 1. Cakupan & Anggaran Fase
Berkas yang diaudit pada Fase 3:
- [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php) (query produk beranda)
- [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) (query detail & produk terkait)
- [`app/Livewire/Shop.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Shop.php) & [`app/Livewire/Search.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Search.php)
- [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php) (pembuatan produk kustom & filter katalog POS)
- [`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php) (auto-save produk kustom saat transaksi POS)
- [`app/Filament/Resources/Products/Schemas/ProductForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php), [`ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php), [`ListProducts.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Pages/ListProducts.php)
- [`database/migrations/2026_07_22_144300_add_pos_columns_to_products_tables.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_07_22_144300_add_pos_columns_to_products_tables.php) & migrasi `migrate_both_channel_products_to_online_only.php`

---

## 2. Daftar Temuan Terklasifikasi

### [KRITIS] Temuan F3-01: Produk Buatan Kasir (`pos_only`) Bocor Langsung ke Beranda & Halaman Detail E-Commerce
- **Lokasi:**
  - [`resources/views/home.blade.php:268`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php#L268) (`$latestProducts`)
  - [`resources/views/home.blade.php:416,418`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php#L416) (`$featuredProducts`)
  - [`app/Livewire/ProductDetail.php:344`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L344) (`mount()`)
- **Bukti Kode:**
  Di `home.blade.php:268`:
  ```php
  $latestProducts = \App\Models\Product::where('is_active', true)->where('is_hidden', false)->latest()->take(4)->get();
  ```
  Di `ProductDetail.php:344`:
  ```php
  $this->product = Product::with(['variants.attributeOptions.attribute', 'variants.media'])->where('slug', $slug)->firstOrFail();
  ```
- **Dampak Nyata:**
  Saat kasir membuat produk baru di POS (`PosManager.php:1359` atau `PosTransactionService.php:60`), produk memang disimpan dengan `channel_visibility = 'pos_only'`, **tetapi `is_active = true` dan `is_hidden = false`**.
  Karena query Beranda (`home.blade.php`) **sama sekali tidak memfilter `channel_visibility`**, produk buatan kasir yang paling baru dibuat langsung tampil di posisi terdepan bagian **"New Arrival / Produk Terbaru"** dan **"Lookbook"** di website E-Commerce (`raabiha.com`). Dan saat diklik pengunjung, `ProductDetail.php` juga tidak memblokir `pos_only` sehingga produk kasir bisa dibuka & dibeli online!

### [KRITIS] Temuan F3-02: Mengapa Opsi E-Commerce vs POS Tercampur Lagi (7 Lubang Sisa `'both'`)
Meskipun pada commit `8c1f22c3` (27 Juli 2026) pernah dilakukan penghapusan `both` di `ProductForm.php` dan migrasi data sekali jalan, nilai `'both'` **kembali mencemari database (terbukti 57 produk di VPS berstatus `both`)** dan membuat katalog E-Commerce & POS bercampur karena 7 celah berikut:
1. **Default Kolom DB Masih `'both'`:** [`2026_07_22_144300_add_pos_columns_to_products_tables.php:14,23`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_07_22_144300_add_pos_columns_to_products_tables.php#L14) menetapkan `->default('both')` untuk `products` dan `product_variants`. Migrasi `migrate_both_channel_products_to_online_only.php` hanya meng-update data lama tanpa mengubah default kolom DB dan tanpa menyentuh `product_variants`.
2. **Bulk Action Admin Masih Punya `'both'`:** [`ProductsTable.php:244`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L244) (`change_channel_visibility`) masih menyediakan opsi `'both' => 'Web & POS Kasir'`.
3. **Export/Import CSV Memaksa Fallback ke `'both'`:** [`ProductImporter.php:189-191, 297-299`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L189), [`ProductVariantImporter.php:92-94`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductVariantImporter.php#L92), [`ExportProductsCsvJob.php:131,177`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php#L131), [`ListProducts.php:48`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Pages/ListProducts.php#L48), dan [`ExportData.php:68`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Pages/ExportData.php#L68).
4. **Query Storefront & POS Masih Membaca `'both'`:**
   - [`Shop.php:70`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Shop.php#L70): `whereIn('channel_visibility', ['online_only', 'both'])`
   - [`Search.php:26`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Search.php#L26): `whereIn('channel_visibility', ['online_only', 'both'])`
   - [`ProductDetail.php:644`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L644): `whereIn('channel_visibility', ['online_only', 'both'])`
   - [`PosManager.php:1423`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1423): `whereIn('channel_visibility', ['pos_only', 'both'])`
   Akibatnya, 57 produk yang terlanjur berstatus `'both'` di VPS tampil sekaligus di Website E-Commerce DAN di Terminal Kasir POS!

### [TINGGI] Temuan F3-03: Produk Buatan Kasir (`is_custom`) Bercampur Tanpa Pemisah di Admin & Bug Stok Saat Auto-Save
- **Lokasi:** [`PosTransactionService.php:58-88`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L58-L88) & [`PosManager.php:1359-1370`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1359-L1370)
- **Masalah:**
  1. Ketika kasir membuat produk baru via `PosTransactionService::createOrder()` dengan `save_to_catalog = true`, produk baru dibuat dengan `'stock' => $qty` (baris 66), namun `'locked_model' => null` (baris 87). Akibatnya, saat transaksi selesai, **stok tidak pernah dikurangi** — barang yang baru saja laku `$qty` tetap tercatat punya stok sisa `$qty` di database!
  2. Produk buatan kasir tidak mengisi `sku`, `category_id`, maupun `images`, dan langsung muncul bercampur di daftar Produk Admin (`ProductsTable.php`) bersama produk E-Commerce utama.

---

## 3. Matriks Sinkronisasi Kanal (FE ↔ BE ↔ DB) — Fase 3

| Halaman / Modul | Filter `channel_visibility` Saat Ini | Status |
| :--- | :--- | :--- |
| Beranda Website (`home.blade.php`) | **TIDAK ADA FILTER SAMA SEKALI** | **BOCOR TOTAL (`pos_only` TAMPIL DI WEB)** |
| Detail Produk (`ProductDetail.php:344`) | **TIDAK ADA FILTER SAMA SEKALI** | **BOCOR TOTAL (`pos_only` BISA DIBELI ONLINE)** |
| Katalog Shop (`Shop.php:70`) & Search (`Search.php:26`) | `['online_only', 'both']` | **TERCAMPUR (`both` MASIH DIIZINKAN)** |
| Terminal Kasir (`PosManager.php:1423`) | `['pos_only', 'both']` | **TERCAMPUR (`both` MASIH DIIZINKAN)** |
| Form Produk Admin (`ProductForm.php:155`) | Hanya `pos_only` & `online_only` | **SINKRON ADR-001** *(tapi bentrok dengan data `both` di DB)* |
| Bulk Action Admin (`ProductsTable.php:244`) | Masih ada `'both'` | **MELANGGAR ADR-001** |
| Database (`products` & `product_variants`) | Default kolom masih `'both'` | **SUMBER KEMUNCULAN ULANG `'both'`** |

---

## 4. File Gemuk (> 300 Baris) yang Terdeteksi di Fase 3
- [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php): **2.163 baris** *(sangat gemuk — melampaui 7x batas ideal 300 baris)*
- [`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php): **866 baris**

---

## 5. Pertanyaan Konfirmasi Bisnis (*Zero-Assumption Gate*)
1. **Nasib 57 Produk Berstatus `both` di Database VPS:** Saat perbaikan dijalankan nanti, apakah semua produk yang saat ini masih berstatus `both` di database ingin dikonversi ke `online_only` (seperti migrasi 27 Juli lalu), atau ada sebagian yang harus dicek manual dulu mana yang `pos_only` vs `online_only`?
2. **Produk Buatan Kasir (`is_custom = true`):** Apakah produk yang dibuat kasir di POS **mutlak 100% hanya boleh ada di POS** (dilarang tampil di Beranda, Shop, Search, maupun Detail Produk E-Commerce)?

---

## Status Fase
- **Fase 3 Selesai** (100% Read-Only).
- **Fase Berikutnya:** **Fase 4 — Audit Skeptis Versi PHP, Laravel, Dependensi & Indikasi CVE/Malware** (`composer.json`, `composer.lock`, `composer audit`, dan pemeriksaan integritas file).
