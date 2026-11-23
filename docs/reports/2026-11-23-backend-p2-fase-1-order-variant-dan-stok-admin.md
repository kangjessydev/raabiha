# Laporan Pengerjaan: P2 Fase 1 — Validasi Varian Pesanan Manual Admin (`BKL-009`) & Sorting Stok Varian (`BKL-004`)

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`013-asisten-to-backend-p2-fase-1-order-variant-dan-stok-admin.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/013-asisten-to-backend-p2-fase-1-order-variant-dan-stok-admin.md) (`closed`)

---

## Ringkasan Perubahan
Hanya menyentuh **2 file produksi** (`+96/-12` baris) dan **1 file feature test baru** (memenuhi batas *Phase-Gating* ≤ 3 file & ≤ 200 baris):

1. **`BKL-009` — [`OrderForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L99-L195) (`+80/-12` baris):**
   - Menambahkan `->live()` dan `$set('product_variant_id', null)` pada `Select::make('product_id')->afterStateUpdated(...)` ([`OrderForm.php:99-110`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L99-L110)) agar pergantian produk otomatis membersihkan ID varian lama dan me-refresh visibilitas/opsi varian.
   - Menambahkan helper `OrderForm::productHasVariants()` ([`OrderForm.php:871-880`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L871-L880)).
   - Pada `Select::make('product_variant_id')` ([`OrderForm.php:112-195`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L112-L195)):
     - `->visible(fn (Get $get): bool => self::productHasVariants($get('product_id')))` + `->dehydrated(true)` agar field disembunyikan rapi untuk produk non-varian sekaligus membersihkan kolom `product_variant_id` menjadi `null` saat produk diganti dari bervarian ke non-varian.
     - `->required(fn (Get $get): bool => self::productHasVariants($get('product_id')))` untuk mewajibkan pemilihan varian pada produk `has_variants = true`.
     - `->options(...)` mengurutkan varian aktif di atas dan menandai varian non-aktif dengan suffix `(Non-Aktif)` agar pesanan historis tetap terbaca.
     - `->disableOptionWhen(...)` (tanpa query N+1) menonaktifkan pilihan varian `(Non-Aktif)` pada pesanan baru atau pergantian varian, sambil tetap mengizinkan varian historis eksisting pada mode Edit.
     - `->rule(...)` memvalidasi di sisi server bahwa `product_variant_id` benar-benar milik `product_id` terpilih dan berstatus `is_active = true` (kecuali record historis eksisting saat Edit).
     - `->afterStateUpdated(...)` juga memfilter query varian berdasarkan `product_id` terpilih.

2. **`BKL-004` — [`ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L32-L120) (`+28/-8` baris):**
   - Menambahkan `withSum(['variants as active_variants_stock_sum' => fn (Builder $variantQuery) => $variantQuery->where('is_active', true)], 'stock')` pada `modifyQueryUsing` ([`ProductsTable.php:33-39`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L33-L39)) untuk mencegah N+1 query.
   - Mengganti `->sortable()` mentah pada `TextColumn::make('stock')` dengan custom SQL sorting (`CASE WHEN products.has_variants = 1 THEN COALESCE((SELECT SUM(pv.stock) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), 0) ELSE COALESCE(products.stock, 0) END {$safeDirection}`) dengan whitelist arah `asc`/`desc` ([`ProductsTable.php:97-103`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L97-L103)).
   - Menyelaraskan `formatStateUsing` dan `color` agar menggunakan `active_variants_stock_sum` (hanya stok varian aktif `is_active = true`) ([`ProductsTable.php:104-123`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L104-L123)).

3. **Automated Feature Test — [`AdminOrderVariantAndProductStockSortTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/AdminOrderVariantAndProductStockSortTest.php):**
   - 6 skenario test penuh: wajib varian pada produk bervarian, penolakan varian lintas produk & varian non-aktif pada CreateOrder, penerimaan varian aktif & produk non-varian, preservasi varian non-aktif historis pada EditOrder, reset `product_variant_id` saat `product_id` berubah, serta sorting DESC/ASC & tampilan stok varian aktif di `ListProducts`.

---

## Bukti Verifikasi
- `php artisan test --filter="AdminOrderVariantAndProductStockSortTest|OrderStockIntegrityTest|ProductDetailVariantAndCartGuardTest|VariantImageMatchingTest|ProductBuyNowTest|CartPageTest|ChannelIsolationAndPosCustomProductTest|ProductImageSingleSourceOfTruthTest"`
  - Hasil: **31/31 tests passed (177 assertions, 7.983s)**.
- `git diff --stat app/`:
  - `app/Filament/Resources/Orders/Schemas/OrderForm.php | 80 ++++++++++++++++++++--`
  - `app/Filament/Resources/Products/Tables/ProductsTable.php | 28 ++++++--`
  - `2 files changed, 96 insertions(+), 12 deletions(-)`

---

## Checklist SOP
- [x] Membaca handoff open `013` dan file target sebelum menulis kode.
- [x] Phase-Gating terpenuhi (2 file produksi, `+96/-12` baris).
- [x] Anti-N+1 query diterapkan (`withSum` di `ProductsTable`, zero-query `disableOptionWhen` di `OrderForm`).
- [x] Validasi sisi server ketat (kepemilikan `product_id`, status `is_active`, whitelist arah sort SQL).
- [x] Seluruh automated test baru & regresi P0/P1 lulus 100%.
- [x] `CHANGELOG.md`, `docs/backlog.md`, dan status handoff `013` diperbarui.

---

## Log Penyimpangan
- Tidak ada penyimpangan dari rencana yang disetujui user.
