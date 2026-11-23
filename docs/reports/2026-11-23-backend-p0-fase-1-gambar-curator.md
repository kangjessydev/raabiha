# Laporan Sesi: P0 — Fase 1 Pemulihan & Pengamanan Gambar Produk + Media Curator

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Handoff Acuan:** [`docs/handoff/005-asisten-to-backend-p0-fase-1-gambar-curator.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/005-asisten-to-backend-p0-fase-1-gambar-curator.md)
- **Status:** Selesai & Terverifikasi (Lokal)

---

## 1. Ringkasan Perubahan

1. **Single Source of Truth Resolusi Gambar:**
   - Membuat [`app/Services/ProductImageResolver.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/ProductImageResolver.php) yang menstandarkan resolusi gambar pada `\App\Models\Media` (sesuai `config/curator.php:21`), fallback `asset('assets/images/placeholder.webp')` (16 KB), batch preloading (`preloadMedia()`) untuk mengeliminasi N+1 query, resolusi galeri paralel 1-ke-1 (`resolveGallery()`), serta normalisasi URL/path ke ID Curator (`normalizeImageArray()`).
   - Menambahkan helper pada [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L183-L202) (`primary_image_url`, `resolvePrimaryImageUrl()`, `resolveGallery()`) dan [`app/Models/ProductVariant.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/ProductVariant.php#L59-L61) (menyeragamkan relasi `media()` ke `App\Models\Media`, plus `image_url` dan `resolveImageUrl()`).
2. **Pengamanan `CuratorPicker` (Anti Data Loss):**
   - Memperbarui [`app/Filament/Resources/Products/Schemas/ProductForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php#L104-L162):
     - `afterStateHydrated`: menerjemahkan URL/path legacy di `products.images` menjadi ID Media Curator dalam urutan galeri asli sehingga gambar tetap tampil di form Edit Admin.
     - `dehydrateStateUsing`: jika state kosong namun record memiliki URL legacy yang tidak ditemukan di tabel `curator`, sistem mempertahankan `$record->images` agar tombol **Simpan** tidak pernah menghapus gambar menjadi `NULL`.
3. **Artisan Command Pemulihan Idempoten:**
   - Membuat [`app/Console/Commands/NormalizeProductImages.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Console/Commands/NormalizeProductImages.php) (`php artisan products:normalize-images [--dry-run]`).
   - Mengonversi URL/path di `products.images` kembali ke array integer `[curator.id, ...]` secara aman (*chunked*), mempertahankan string yang tidak cocok di tabel `curator` (*zero data loss*), dan idempoten jika dijalankan berulang.
4. **Perbaikan `ProductImporter`:**
   - Memperbarui [`app/Filament/Imports/ProductImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L107-L127) dengan `ignoreBlankState()`, normalisasi URL CSV ke ID Curator saat `castStateUsing`, serta proteksi `fillRecordUsing` agar sel kosong tidak menimpa gambar eksisting.
5. **Konsolidasi 6 Lokasi Pembacaan Gambar + Eliminasi N+1:**
   - [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L48-L60)
   - [`resources/views/livewire/shop.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php#L260-L270)
   - [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php#L40-L56) (termasuk blok produk terbaru baris 253-267 dan lookbook baris 396-403)
   - [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L354-L358)
   - [`resources/views/livewire/cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L45-L55) + eager loading `items.variant.media` di [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L136-L149)
   - [`resources/views/livewire/checkout.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php#L522-L535) + eager loading `items.variant.media` & Tripay `image_url` di [`app/Livewire/Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L944-L955)

---

## 2. Bukti Verifikasi

| Pemeriksaan | Perintah / Target | Hasil |
| :--- | :--- | :--- |
| PHP Syntax Lint (`php -l`) | 11 file PHP baru/diubah | **0 syntax errors** |
| Code Style (`vendor/bin/pint --test`) | `ProductImageResolver.php`, `NormalizeProductImages.php`, `ProductImageSingleSourceOfTruthTest.php` | **PASS** (`{"tool":"pint","result":"passed"}`) |
| Feature Test Suite | `php artisan test --filter="ProductImageSingleSourceOfTruthTest\|VariantImageMatchingTest\|ShopPageTest\|CartPageTest\|ProductBuyNowTest\|SecurityHardeningTest"` | **15/15 passed, 64 assertions** (3.11s) |

---

## 3. Catatan Operasional Produksi (Saat Nanti Deploy)
- Setelah perubahan ini suatu saat disetujui untuk di-deploy ke VPS, jalankan simulasi terlebih dahulu:
  ```bash
  php artisan products:normalize-images --dry-run
  ```
- Jika hasil simulasi sesuai ekspektasi, jalankan normalisasi live:
  ```bash
  php artisan products:normalize-images
  ```
- *(Belum ada push/deploy ke VPS pada sesi ini sesuai batasan protokol).*

---

## 4. Log Penyimpangan
- Tidak ada penyimpangan dari brief handoff `005`.
