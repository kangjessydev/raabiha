# Laporan Audit Verifikasi End-to-End P0 (Fase 1, Fase 2, Fase 3)

- **Tanggal:** 2026-11-23
- **Peran:** `role-inspektur` (Mode Audit-Only / 100% Read-Only)
- **Handoff Rujukan:** [`docs/handoff/008-asisten-to-inspektur-audit-verifikasi-p0.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/008-asisten-to-inspektur-audit-verifikasi-p0.md)
- **Verdict Kesiapan Rilis:** **LAYAK DENGAN CATATAN** *(0 Kritis, 0 Tinggi, 2 Sedang, 2 Rendah)*

---

## 1. Bukti Verifikasi Independen (Perintah Langsung)

| Pemeriksaan | Perintah | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- |
| Audit CVE PHP/Composer | `composer audit` | `No security vulnerability advisories found.` | **LULUS** |
| Audit CVE NPM Produksi | `npm audit --omit=dev` | `found 0 vulnerabilities` | **LULUS** |
| Test Suite P0 & Security | `php artisan test --filter="ChannelIsolationAndPosCustomProductTest\|ProductImageSingleSourceOfTruthTest\|VariantImageMatchingTest\|ShopPageTest\|CartPageTest\|ProductBuyNowTest\|SecurityHardeningTest"` | `18/18 passed (98 assertions, 3.21s)` | **LULUS** |
| Sisa `placeholder.png` | `grep -rn "placeholder\.png" app/ resources/views/` | `0 kemunculan` | **LULUS** |
| Versi Paket Terpasang | `composer show` | `laravel/framework 13.43.0`, `filament/filament 5.12.0`, `livewire/livewire 4.4.9`, `awcodes/filament-curator 5.6.7`, `guzzlehttp/guzzle 8.6.0`, `symfony/http-foundation 8.1.11`, `league/commonmark 2.10.7` | **TERVERIFIKASI** |
| Pemakaian Direct Guzzle | `grep -rn "GuzzleHttp" app/` | `0 kemunculan` (100% memakai Laravel `Http::` facade) | **AMAN** |

---

## 2. Verifikasi Per Area P0

### A. P0 Fase 1 — Gambar Produk & Media Curator Single Source of Truth
- **Single Source of Truth (`ProductImageResolver.php`):** Terverifikasi lengkap (`preloadMedia`, `resolveSingle`, `resolvePrimaryUrl`, `resolveVariantUrl`, `resolveGallery`, `normalizeImageArray`) menggunakan `App\Models\Media` dan fallback `assets/images/placeholder.webp`.
- **Kompatibilitas `awcodes/filament-curator 5.6.7`:**
  - Diperiksa langsung pada [`vendor/awcodes/filament-curator/src/Components/Forms/CuratorPicker.php:143-147`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/vendor/awcodes/filament-curator/src/Components/Forms/CuratorPicker.php#L143-L147) dan [`vendor/awcodes/filament-curator/src/Support/MediaScope.php:198-203`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/vendor/awcodes/filament-curator/src/Support/MediaScope.php#L198-L203).
  - Curator 5.6.7 menambahkan validation rule `MediaScope::contains($value, $persistedIds)`.
  - Terverifikasi **KOMPATIBEL**: saat `afterStateHydrated` di [`ProductForm.php:111-144`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php#L111-L144) menghasilkan `[]` (unresolvable legacy URL) atau `['uuid' => $media->toArray()]` pada disk `public`, `MediaScope::contains()` mengembalikan `true`.
- **Command `products:normalize-images` & `ProductImporter`:**
  - [`NormalizeProductImages.php:16-75`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Console/Commands/NormalizeProductImages.php#L16-L75): idempoten (`chunkById(100)`, `--dry-run`, hanya menyimpan jika `$result['changed'] === true`, URL tak cocok dipertahankan / zero data loss).
  - [`ProductImporter.php:107-127`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L107-L127): memakai `->ignoreBlankState()`, menormalisasi URL ke ID Curator via `ProductImageResolver::normalizeImageArray()`, dan tidak menimpa gambar lama saat sel CSV kosong.

### B. P0 Fase 2 — Isolasi Kanal POS vs E-Commerce (ADR-001 + Opsi A) & Stok Produk Kustom
- **Scope Model & Konsistensi Kanal:**
  - [`Product.php:17-19,74-90`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L74-L90): `$attributes['channel_visibility'] = 'online_only'`, `scopeVisibleOnline()` (mengecualikan `pos_only` dan `is_custom = true`, sementara mengizinkan legacy non-custom `'both'` sesuai Opsi A), dan `scopeVisibleInPos()`.
  - Terpasang konsisten di `Shop.php`, `Search.php`, `ProductDetail.php` (termasuk 404 pada `mount()` dan `relatedProducts()`), `home.blade.php` (`$latestProducts` & `$featuredProducts`), dan `PosManager.php`.
  - Admin [`ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php): badge merah `"Legacy (Perlu Dipilah)"` + filter khusus + Bulk Action hanya 2 opsi (`online_only` / `pos_only`).
  - Importer/Exporter (`ProductImporter`, `ProductVariantImporter`, `ExportProductsCsvJob`, `ListProducts`, `ExportData`, `ClientProductImporter`): tidak lagi memunculkan/menulis `'both'`.
- **Keamanan Migrasi MySQL Produksi:**
  - [`2026_11_23_070000_set_channel_visibility_default_to_online_only.php:19-24`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_11_23_070000_set_channel_visibility_default_to_online_only.php#L19-L24) menggunakan `ENUM('online_only', 'pos_only', 'both') NOT NULL DEFAULT 'online_only'`, identik dengan definisi migrasi awal [`2026_07_22_144300_add_pos_columns_to_products_tables.php:15,23`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_07_22_144300_add_pos_columns_to_products_tables.php#L15-L23) tanpa `UPDATE` data. **100% aman untuk 57 baris legacy `'both'` di VPS.**
- **Stok Produk Kustom (`PosTransactionService.php`):**
  - [`PosTransactionService.php:59-90`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L59-L90) & [`264-299`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L264-L299): `$lockedCustomProduct = $newProduct` menurunkan stok dari `$qty` menjadi `0`, mencatat `StockLog`, dan konsisten bila terjadi void/return (stok kembali `+qty`).

### C. P0 Fase 3 — Patch CVE Dependensi
- Seluruh 49 advisory Composer dan 4 High advisory NPM produksi telah bersih (`0 vulnerabilities`).
- Kenaikan mayor `guzzlehttp/guzzle` (`7.10.6 -> 8.6.0`) terverifikasi aman karena seluruh HTTP call di `app/` melewati Laravel `Http::` facade.
- Aset publik Filament & Curator telah di-republish (`filament:upgrade`).

---

## 3. Daftar Temuan Terklasifikasi

| ID | Keparahan | Lokasi (`file:baris`) | Temuan & Dampak Riil | Rekomendasi |
| :--- | :--- | :--- | :--- | :--- |
| **F-01** | **Sedang** | [`SitemapController.php:38-41`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Http/Controllers/SitemapController.php#L38-L41) | Query sitemap masih menulis manual `where('is_active', true)->where('is_hidden', false)->whereIn('channel_visibility', ['online_only', 'both'])` tanpa `Product::visibleOnline()` (belum mengecualikan `is_custom = true`). Jika ada produk kustom legacy berkanal `'both'`/`'online_only'`, URL-nya masuk ke `/sitemap.xml` padahal `ProductDetail` mengembalikan 404 (*Soft 404* di SEO). | Ganti dengan `Product::visibleOnline()->get()`. |
| **F-02** | **Sedang** | [`product-detail.blade.php:677-696`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php#L677-L696) | Loop `@foreach($this->relatedProducts as $related)` masih memakai `\Awcodes\Curator\Models\Media::find($related->images[0])` secara langsung (N+1 hingga 4 query) dan belum memakai `ProductImageResolver`. | Preload media untuk `$this->relatedProducts` via `ProductImageResolver::preloadMedia()` dan gunakan `$related->resolvePrimaryImageUrl($relatedMediaMap)`. |
| **F-03** | **Rendah** | [`ProductForm.php:145-166`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php#L145-L166) | *Edge case mixed array*: jika 1 produk memiliki campuran Curator ID valid **dan** URL eksternal yang tidak ada di tabel `curator` sekaligus (mis. `[12, "https://ext.com/a.jpg"]`), `dehydrateStateUsing` akan mengembalikan `[12]` saja karena `filled($state)` bernilai `true`. | Catatan saja; jalankan `php artisan products:normalize-images --dry-run` di VPS untuk memastikan tidak ada produk campuran semacam ini. |
| **F-04** | **Rendah** | [`ProductsTable.php:51`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L51) | `ProductImageResolver::resolveSingle($firstMediaId, null, null)` tidak menyertakan `$preloadedMedia`, sehingga masih menjalankan 1 query `Media::find()` per baris pada tabel produk Admin (10–25 baris per halaman). Bukan regresi (sama seperti sebelum P0). | Bisa dioptimasi di P1/P2 (atau dibiarkan karena impact minimal di halaman admin terpaginasi). |

---

## 4. Kesimpulan & Rekomendasi Langkah Berikutnya

- **Status Rilis P0:** **LAYAK DENGAN CATATAN** (tidak ada celah Kritis/Tinggi).
- **Opsi 1 (Disarankan):** Perbaiki cepat 2 temuan Sedang (**F-01** `SitemapController` & **F-02** `product-detail.blade.php` — estimasi <15 baris kode) lewat `role-backend`, lalu commit & deploy P0 ke VPS.
- **Opsi 2:** Langsung commit & deploy P0 sekarang, dan masukkan **F-01** & **F-02** ke tiket P1.
- **Checklist Wajib Saat Deploy P0 ke VPS (jika disetujui User):**
  1. `composer install --no-dev --optimize-autoloader`
  2. `php artisan migrate --force`
  3. `php artisan products:normalize-images --dry-run` (cek output dulu sebelum live run)
  4. `php artisan optimize:clear && php artisan optimize`
