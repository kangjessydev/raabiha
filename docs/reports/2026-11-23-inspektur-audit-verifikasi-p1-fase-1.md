# Laporan Audit Verifikasi Forensik: P1 Fase 1 (Varian Aktif, Guard Stok Keranjang & Admin Varian)

- **Peran:** `role-inspektur` *(Mode Audit-Only — 100% Read-Only pada kode produksi)*
- **Tanggal:** 2026-11-23
- **Handoff Acuan:** [`docs/handoff/011-asisten-to-inspektur-audit-verifikasi-p1-fase-1.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/011-asisten-to-inspektur-audit-verifikasi-p1-fase-1.md)
- **Branch:** `development` *(belum di-commit, tidak menyentuh `main` maupun VPS)*
- **Verdict:** **PERLU PERBAIKAN DULU DI `role-backend`** *(sebelum lanjut ke P1 Fase 2 `role-frontend`)*

---

## 1. Cakupan, Batas Ruang Lingkup & Verifikasi Independen

### 1.1 Berkas yang Diaudit
| Berkas | Jumlah Baris | Status Perubahan P1 Fase 1 | Status Ukuran (Ideal ≤ 300) |
| :--- | :---: | :--- | :--- |
| [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) | 700 | Diubah (`+131 / -27`) | Gemuk (> 300 baris) |
| [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php) | 437 | Diubah (`+78 / -7`) | Gemuk (> 300 baris) |
| [`tests/Feature/ProductDetailVariantAndCartGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/ProductDetailVariantAndCartGuardTest.php) | 357 | Baru (`+357`) | File test |
| [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php) | — | Tidak diubah *(cek interaksi)* | — |
| [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php) | 341 | Tidak diubah *(cek interaksi)* | Gemuk (> 300 baris) |
| [`app/Services/ProductImageResolver.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/ProductImageResolver.php) | 380 | Tidak diubah *(cek N+1)* | Gemuk (> 300 baris) |
| [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php) | 1.043 | Tidak diubah *(cek kontrak 010)* | Gemuk (> 300 baris, agenda `BKL-011`) |

### 1.2 Bukti Batas Ruang Lingkup (`git status --short`)
- Hanya `app/Livewire/ProductDetail.php`, `app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`, `tests/Feature/ProductDetailVariantAndCartGuardTest.php`, `CHANGELOG.md`, laporan, dan handoff yang berubah.
- **Terkonfirmasi bersih:** `resources/views/livewire/product-detail.blade.php` dan `database/migrations/` tidak tersentuh.
- **Terkonfirmasi API Filament 5.12.0:** Method `ImageColumn::imageSize(int|string|Closure $size)` valid (`vendor/filament/tables/src/Columns/ImageColumn.php:112`).

### 1.3 Hasil Test Suite Independen
Perintah:
```bash
php artisan test --filter="ProductDetailVariantAndCartGuardTest|VariantImageMatchingTest|ProductBuyNowTest|CartPageTest|ChannelIsolationAndPosCustomProductTest|ProductImageSingleSourceOfTruthTest"
```
Hasil: **18/18 tests passed (114 assertions, 4.391 ms)** — tidak ada regresi terhadap fitur P0 maupun eksisting.

---

## 2. Peta Cara Kerja Fitur P1 Fase 1 Saat Ini

1. **Isolasi Varian Non-Aktif di `ProductDetail.php`:**
   - Helper `activeVariants()` (`ProductDetail.php:47-54`) menyaring `$this->product->variants->where('is_active', true)->values()`.
   - Sudah dipakai konsisten pada `maxOrderableQuantity()`, `availableAttributes()`, `isOptionAvailable()`, `getFirstVariantWithImage()`, `getMatchedVariant()`, `priceRange()`, `addToCart()`, dan JSON-LD `inStock` di `render()`.
2. **Guard Stok & Batas Kuantitas di `ProductDetail.php`:**
   - `incrementQuantity()` (`ProductDetail.php:419-425`) dan `updatedQuantity()` (`ProductDetail.php:434-446`) memakai `$this->maxOrderableQuantity` (tidak lagi hardcode `10`).
   - `selectOption()` (`ProductDetail.php:160-164`) otomatis meng-clamp `$this->quantity` jika pindah ke varian dengan stok lebih kecil.
   - `addToCart()` (`ProductDetail.php:508-523`) menjumlahkan `$existingQty + $this->quantity` pada `CartItem` eksisting (user login maupun guest session) dan menolak jika melebihi `$availableStock`.
3. **Reset Galeri saat Deselect & Kontrak Fase 2:**
   - `selectOption()` (`ProductDetail.php:150-192`) mengizinkan *deselect* sebelum cek `isOptionAvailable()`, lalu menghitung `$targetIndex` (default `0`), memperbarui `$this->activeGalleryIndex`, dan men-dispatch `change-gallery-image` dengan payload `index: $targetIndex`.
   - Listener Alpine di `product-detail.blade.php:62` (`@change-gallery-image.window="activeIndex = $event.detail.index"`) sudah cocok dengan kontrak payload.
4. **Admin `VariantsRelationManager.php`:**
   - Kontrol `is_active` sudah tersedia lengkap di form (`Toggle`), tabel (`ToggleColumn`), dan filter (`TernaryFilter`).
   - Aksi `generate_variants` (`VariantsRelationManager.php:356-423`) sudah membuat *canonical signature* terurut dari ID opsi atribut untuk melewati kombinasi duplikat dan menampilkan notifikasi ringkasan.

---

## 3. Daftar Temuan Forensik Terklasifikasi

### [T-01] TINGGI — `Product::total_stock` & `Product::is_out_of_stock` Masih Menghitung Varian Non-Aktif (`is_active = false`)
- **Lokasi:** [`app/Models/Product.php:194-209`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L194-L209) dan penggunaannya di [`resources/views/livewire/product-detail.blade.php:420-455`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php#L420-L455) & [`resources/views/livewire/product-detail.blade.php:832-954`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php#L832-L954).
- **Bukti Kode (`Product.php:194-209`):**
  ```php
  public function getIsOutOfStockAttribute(): bool
  {
      if ($this->has_variants) {
          return $this->variants->sum('stock') <= 0; // <-- tidak memfilter is_active = true!
      }
      return $this->stock <= 0;
  }

  public function getTotalStockAttribute(): int
  {
      if ($this->has_variants) {
          return $this->variants->sum('stock'); // <-- tidak memfilter is_active = true!
      }
      return $this->stock;
  }
  ```
- **Dampak Riil:**
  1. Jika produk punya Varian A (`is_active = true`, stok `5`) dan Varian B (`is_active = false`, stok `10`), tampilan UI Detail Produk menampilkan **"15 PCS TERSEDIA (TOTAL SEMUA VARIAN)"** padahal stok aktif yang bisa dibeli hanya `5`.
  2. Jika **semua varian** dinonaktifkan (`is_active = false`) tetapi kolom `stock > 0`, `$product->is_out_of_stock` tetap bernilai `false` -> tombol `+ KERANJANG` & `BELI SEKARANG` tetap tampil hijau aktif, padahal tidak ada opsi varian yang muncul dan klik tombol selalu berakhir error *"Varian produk tidak tersedia atau sedang tidak aktif."*
  3. Kontradiktif dengan JSON-LD di `ProductDetail.php:630` yang sudah memakai `$this->activeVariants()->sum('stock') > 0`.
- **Rekomendasi Perbaikan (`role-backend`):** Saring `->where('is_active', true)` pada `getIsOutOfStockAttribute()` dan `getTotalStockAttribute()` di `app/Models/Product.php` (sebagai *Single Source of Truth* untuk seluruh storefront).

---

### [T-02] TINGGI — Celah Bypass Guard Stok pada Tombol `+` di Halaman Keranjang (`Cart::incrementQuantity`)
- **Lokasi:** [`app/Livewire/Cart.php:152-164`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L152-L164)
- **Bukti Kode:**
  ```php
  public function incrementQuantity($itemId)
  {
      if (! $this->cart) {
          $this->loadCart();
      }

      $item = $this->cart ? $this->cart->items()->find($itemId) : null;
      if ($item) {
          $item->increment('quantity'); // <-- TIDAK ADA PENGECEKAN STOK SAMA SEKALI!
          $this->loadCart();
          $this->dispatch('cart-updated');
      }
  }
  ```
- **Dampak Riil:**
  - Guard akumulasi stok di `ProductDetail::addToCart()` sudah ketat, tetapi begitu user membuka halaman `/cart` dan menekan tombol `+`, kuantitas item di keranjang bisa dinaikkan tanpa batas (misal stok riil `3` pcs, user bisa klik `+` hingga `50` pcs di keranjang).
  - Merusak konsistensi *end-to-end* guard stok antara Detail Produk dan Keranjang.
- **Rekomendasi Perbaikan (`role-backend`):** Muat relasi `product` dan `variant` pada `$item` di `Cart::incrementQuantity()`, cek `$availableStock` (dari `$item->variant->stock` jika ada varian dan `is_active`, atau `$item->product->stock`), dan tolak dengan flash message error jika `$item->quantity >= $availableStock`.

---

### [S-01] SEDANG — N+1 Query Tersembunyi pada Kolom Thumbnail `VariantsRelationManager` saat Fallback ke Foto Induk
- **Lokasi:** [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php:268,272-276`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L268-L276) dan [`app/Services/ProductImageResolver.php:78,151-154`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/ProductImageResolver.php#L78).
- **Bukti Kode:**
  - `VariantsRelationManager.php:274`: `ProductImageResolver::resolveVariantUrl($record, $this->getOwnerRecord())` — argumen `$preloadedMedia` tidak dikirim (`null`).
  - Saat `$record->media_id === null`, `resolveVariantUrl()` jatuh ke `resolvePrimaryUrl($ownerRecord, null)` -> `resolveSingle($images[0], null)` -> `Media::find($id)`.
- **Dampak Riil:**
  - Setiap baris varian yang belum memiliki foto khusus (`media_id = null`, misal 20 varian baru hasil *Generate Varian Otomatis*) akan mengeksekusi query `SELECT * FROM media WHERE id = ?` identik sebanyak N kali per render.
  - Karena tabel memakai `->poll('15s')` (`VariantsRelationManager.php:269`), N query identik ini berjalan berulang setiap 15 detik selama tab admin terbuka.
- **Rekomendasi Perbaikan (`role-backend`):** Preload media milik `$ownerRecord` sekali saja menggunakan `ProductImageResolver::preloadMedia([$this->getOwnerRecord()])` (di-cache di luar closure baris) dan kirimkan sebagai argumen ke-3 `resolveVariantUrl($record, $ownerRecord, $preloadedOwnerMedia)`.

---

### [S-02] SEDANG — Edge Case Nama Varian Duplikat di `generate_variants` Jika Admin Memilih Atribut Sama 2x
- **Lokasi:** [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php:375,384-389`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L375-L389)
- **Bukti Kode:**
  - Baris 375 sudah men-deduplikasi ID: `$sortedIds = collect($combination)->map(...)->unique()->sort()->values()->all();`
  - Namun baris 384 masih memakai `$combination` mentah tanpa `->unique()` untuk membentuk `$nameParts`.
- **Dampak Riil:** Jika admin tidak sengaja menambahkan 2 baris Repeater dengan `attribute_id` yang sama (misal Warna & Warna), varian yang terbuat memiliki 1 opsi di pivot (`[Merah]`) tetapi namanya menjadi `"Tunik - Merah - Merah"`.
- **Rekomendasi Perbaikan (`role-backend`):** Tambahkan `->unique()` pada `$combination` sebelum memetakan `$nameParts` (atau gabungkan `option_ids` per `attribute_id` sebelum membuat Cartesian product).

---

### [R-01] RENDAH — `ProductDetail::updatedQuantity()` Tidak Meng-clamp Saat Stok `0`
- **Lokasi:** [`app/Livewire/ProductDetail.php:434-446`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L434-L446)
- **Bukti Kode:**
  ```php
  if ($qty < 1) {
      $this->quantity = 1;
  } elseif ($maxQty > 0 && $qty > $maxQty) {
      $this->quantity = $maxQty;
  } else {
      $this->quantity = $qty;
  }
  ```
- **Dampak Riil:** Jika `$maxQty === 0` dan client mengirim angka `50` via `wire:model.live="quantity"`, state `$this->quantity` tersimpan sebagai `50` (aman saat submit karena `addToCart()` tetap memblokir `$availableStock < $this->quantity`, namun state angka di UI menjadi tidak rapi). Jika `$maxQty === 0`, sebaiknya di-clamp ke `1`.

---

### [R-02] RENDAH — Catatan Ketepatan Kontrak untuk Handoff `010` (`role-frontend` P1 Fase 2)
- **Lokasi:** [`docs/handoff/010-backend-to-frontend-p1-fase-2-mobile-bottom-sheet-dan-ui-stok.md:9,22`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/010-backend-to-frontend-p1-fase-2-mobile-bottom-sheet-dan-ui-stok.md#L9) vs [`resources/views/livewire/product-detail.blade.php:801,873,898`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php#L801).
- **Detail:**
  1. Elemen `<img>` Mobile Bottom Sheet yang perlu disinkronkan berada di **baris 801** (bukan baris 814 sebagaimana tertulis di handoff `010`).
  2. Pada tombol pilihan varian Mobile Bottom Sheet (`baris 873` & `898`), kondisi `@if($isAvailable)` sebaiknya diubah menjadi `@if($isAvailable || $isSelected)` di Fase 2 agar opsi yang sedang terpilih tetap bisa di-*deselect* meskipun stok kombinasi berubah ke `0` di latar belakang.

---

## 4. Matriks Sinkronisasi End-to-End (FE ↔ BE ↔ DB)

| Fitur / Elemen | Frontend (`product-detail.blade.php` / Admin) | Backend (`ProductDetail.php` / `Product.php` / `Cart.php`) | Database (`product_variants` / `cart_items`) | Status Sinkronisasi |
| :--- | :--- | :--- | :--- | :--- |
| Filter Varian Non-Aktif (Opsi & Harga) | `@foreach($this->availableAttributes)` & `$this->priceRange` | `ProductDetail::activeVariants()` (`is_active = true`) | `product_variants.is_active` | **Sinkron** |
| Status Stok Total & `STOK HABIS` | `$product->total_stock` & `$product->is_out_of_stock` | `Product::getTotalStockAttribute()` & `getIsOutOfStockAttribute()` | `product_variants.stock` *(tanpa filter `is_active`)* | **Mismatch (T-01)** |
| Guard Akumulasi Stok (Detail Produk) | `addToCart()` | `ProductDetail::addToCart()` (`$existingQty + $qty <= $stock`) | `cart_items.quantity` | **Sinkron** |
| Guard Stok Tombol `+` (Halaman Keranjang) | `Cart::incrementQuantity($itemId)` | `Cart.php:152-164` *(tanpa cek stok)* | `cart_items.quantity` | **Putus / Bolong (T-02)** |
| Reset Galeri saat Deselect | `@change-gallery-image.window="activeIndex = $event.detail.index"` | `ProductDetail::selectOption()` (`$activeGalleryIndex`, dispatch `index: 0`) | `product_variants.media_id` | **Sinkron** *(Mobile BS menunggu Fase 2)* |
| Admin Varian (`is_active` & Generate) | `VariantsRelationManager` (`Toggle`, `ToggleColumn`, `TernaryFilter`, `ImageColumn`) | `VariantsRelationManager::table()` & `generate_variants` | `product_variants` & `attribute_option_product_variant` | **Sinkron** *(dengan catatan N+1 S-01 & edge case S-02)* |

---

## 5. Pertanyaan Konfirmasi Bisnis (*Zero-Assumption Gate*)

1. **Perilaku `buyNow()` Saat Keranjang Sudah Penuh (`ProductDetail.php:549-565`):**
   Saat ini `buyNow()` memanggil `addToCart()`. Jika stok produk `3` pcs dan user sudah memiliki `3` pcs produk tersebut di keranjang reguler, lalu user kembali ke halaman produk dan klik **"Beli Sekarang"**, aksi ditolak dengan pesan *"Stok maksimal (3 pcs) sudah ada di keranjang Anda."*
   - Apakah perilaku ini sudah sesuai (user diminta checkout melalui halaman keranjang), atau Kang ingin `buyNow()` langsung meneruskan item yang sudah ada di keranjang ke `/checkout`?
2. **Persetujuan Perbaikan Sebelum P1 Fase 2:**
   Apakah Kang setuju kita perbaiki dulu temuan **T-01** (`Product.php`), **T-02** (`Cart.php`), dan **S-01 / S-02 / R-01** (`VariantsRelationManager.php` & `ProductDetail.php`) melalui `role-backend` sebelum lanjut ke P1 Fase 2 (`role-frontend`)?
