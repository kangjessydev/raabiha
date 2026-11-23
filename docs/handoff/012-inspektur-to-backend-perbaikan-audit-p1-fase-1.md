# Brief: Perbaikan Temuan Audit Verifikasi P1 Fase 1 (T-01, T-02, S-01, S-02, R-01)

- **Untuk:** `role-backend`   **Dari:** `role-inspektur`   **Tanggal:** 2026-11-23
- **Status:** `closed` *(Laporan: `docs/reports/2026-11-23-backend-perbaikan-audit-p1-fase-1.md`)*
- **Disetujui user:** ya (konfirmasi eksplisit user atas temuan audit P1 Fase 1)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menutup seluruh temuan dari laporan audit verifikasi P1 Fase 1 ([`docs/reports/2026-11-23-inspektur-audit-verifikasi-p1-fase-1.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-verifikasi-p1-fase-1.md)) sebelum kita melanjutkan ke P1 Fase 2 (`role-frontend`).

## Konteks
- Laporan audit acuan: [`docs/reports/2026-11-23-inspektur-audit-verifikasi-p1-fase-1.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-verifikasi-p1-fase-1.md)
- Berkas target perbaikan (ringan & terarah):
  1. [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php) *(T-01)*
  2. [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php) *(T-02)*
  3. [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php) *(S-01 & S-02)*
  4. [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) *(R-01)*
  5. [`tests/Feature/ProductDetailVariantAndCartGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/ProductDetailVariantAndCartGuardTest.php) *(tambah test untuk T-01, T-02, S-01/S-02)*

## Yang diminta
1. **[T-01 — Tinggi] Saring varian aktif pada `Product::getIsOutOfStockAttribute()` & `Product::getTotalStockAttribute()` ([`app/Models/Product.php:194-209`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L194-L209)):**
   - Untuk produk dengan `has_variants = true`, hitung stok hanya dari `$this->variants->where('is_active', true)->sum('stock')`.
   - Pastikan jika semua varian `is_active = false` (meski `stock > 0`), maka `$product->total_stock === 0` dan `$product->is_out_of_stock === true`.
2. **[T-02 — Tinggi] Pasang guard stok riil pada `Cart::incrementQuantity($itemId)` ([`app/Livewire/Cart.php:152-164`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L152-L164)):**
   - Muat relasi `product` dan `variant` pada `$item`.
   - Jika item memakai varian (`product_variant_id !== null`), cek apakah varian masih ada & `is_active === true`, dan ambil `$availableStock = (int) $item->variant->stock`; jika tidak memakai varian, ambil `$availableStock = (int) $item->product->stock`.
   - Jika `$item->quantity >= $availableStock`, JANGAN increment — kirim `session()->flash('error', ...)` yang jelas (misal: `"Stok maksimal ({$availableStock} pcs) sudah tercapai."` atau pesan varian non-aktif jika `is_active = false`).
3. **[S-01 — Sedang] Eliminasi N+1 query fallback foto induk di `VariantsRelationManager` ([`VariantsRelationManager.php:272-276`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L272-L276)):**
   - Preload media milik `$ownerRecord` sekali saja di level `table()` (misal via `ProductImageResolver::preloadMedia([$ownerRecord])`) dan teruskan sebagai argumen ke-3 `ProductImageResolver::resolveVariantUrl($record, $ownerRecord, $preloadedOwnerMedia)`.
4. **[S-02 — Sedang] Cegah nama varian duplikat pada `generate_variants` ([`VariantsRelationManager.php:384-389`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L384-L389)):**
   - Gunakan `->unique()` pada `$combination` sebelum memetakan `$nameParts` agar tidak menghasilkan nama ganda (misal `"Tunik - Merah - Merah"`) bila admin tidak sengaja memilih atribut sama dua kali di Repeater.
5. **[R-01 — Rendah] Clamp `ProductDetail::updatedQuantity()` saat stok `0` ([`ProductDetail.php:434-446`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L434-L446)):**
   - Jika `$maxQty <= 0` atau `$qty < 1`, set `$this->quantity = 1`.
6. **Automated Tests & Dokumentasi:**
   - Tambahkan feature test di [`tests/Feature/ProductDetailVariantAndCartGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/ProductDetailVariantAndCartGuardTest.php) untuk:
     - `Product::total_stock` & `is_out_of_stock` mengabaikan varian `is_active = false` (termasuk kasus semua varian non-aktif).
     - `Cart::incrementQuantity()` menolak penambahan bila sudah mencapai stok riil.
   - Jalankan test suite terkait hingga 100% hijau, perbarui `CHANGELOG.md`, dan tulis laporan singkat di `docs/reports/2026-11-23-backend-perbaikan-audit-p1-fase-1.md`.

## Di luar cakupan
- **TIDAK mengubah** `resources/views/livewire/product-detail.blade.php` (tetap menjadi tugas P1 Fase 2 `role-frontend` di handoff `010`).
- **TIDAK mengubah** `app/Livewire/Checkout.php` — pemisahan total `buyNow()` dengan `Cart(is_buy_now = true)` + notifikasi stok keranjang berkurang telah dicatat ke backlog **`BKL-013`** agar tidak mencampur perubahan `Checkout.php` (1.929 baris) ke perbaikan ini.
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. Temuan **T-01, T-02, S-01, S-02, R-01** tertutup dengan bukti konkret `file:baris`.
2. Seluruh test suite (`ProductDetailVariantAndCartGuardTest`, `VariantImageMatchingTest`, `ProductBuyNowTest`, `CartPageTest`, `ChannelIsolationAndPosCustomProductTest`, `ProductImageSingleSourceOfTruthTest`) lulus 100%.
3. `CHANGELOG.md` diperbarui dan laporan tersimpan di `docs/reports/2026-11-23-backend-perbaikan-audit-p1-fase-1.md`.

## Pertanyaan terbuka
- Tidak ada (seluruh item perbaikan telah disetujui user).
