# Laporan Audit Forensik: P2 Fase 1 (`BKL-009` & `BKL-004`)

- **Peran:** `role-inspektur` *(Mode Audit Saja / Read-Only — 0 baris kode diubah)*
- **Tanggal:** 2026-11-23
- **Branch:** `development`
- **Cakupan Audit:**
  - [`app/Filament/Resources/Orders/Schemas/OrderForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php)
  - [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php)
  - [`tests/Feature/AdminOrderVariantAndProductStockSortTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/AdminOrderVariantAndProductStockSortTest.php)

---

## 1. Ringkasan Kesehatan Kode
- **Status:** **Lulus Audit / Siap Lanjut ke P2 Fase 2**
- **Ringkasan Temuan:**
  - Kritis: **0**
  - Tinggi: **0**
  - Sedang: **0**
  - Rendah (Catatan Efisiensi Minor): **1** *(tidak memblokir rilis/fase berikutnya)*
- **Verifikasi Test:** 31/31 test lulus (177 assertions) pada `AdminOrderVariantAndProductStockSortTest` + `OrderStockIntegrityTest` + suite regresi P0/P1.

---

## 2. Matriks Sinkronisasi Alur Data (UI ➔ Backend ➔ DB)

| Elemen UI / Form | Validasi & Logika Backend | Tabel & Kolom DB | Status |
| :--- | :--- | :--- | :--- |
| `OrderForm` — Pilih/Ganti `product_id` ([`OrderForm.php:99-110`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L99-L110)) | `->live()` + `$set('product_variant_id', null)` + `dehydrated(true)` pada field varian | `order_items.product_id`, `order_items.product_variant_id` | **Sinkron Penuh** |
| `OrderForm` — Pilih `product_variant_id` ([`OrderForm.php:112-195`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L112-L195)) | `visible` & `required` berbasis `has_variants`; server-side `rule` cek kepemilikan `product_id` & `is_active = true` (kecuali varian historis eksisting saat Edit); `disableOptionWhen` tanpa N+1 | `order_items.product_variant_id` ↔ `product_variants (id, product_id, is_active)` | **Sinkron Penuh** |
| `ProductsTable` — Sorting Kolom `stock` ([`ProductsTable.php:95-103`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L95-L103)) | Custom SQL `CASE WHEN products.has_variants = 1 THEN COALESCE((SELECT SUM(pv.stock) ... AND pv.is_active = 1), 0) ELSE COALESCE(products.stock, 0) END` + whitelist arah `asc`/`desc` | `products (has_variants, stock)`, `product_variants (product_id, is_active, stock)` | **Sinkron Penuh** |
| `ProductsTable` — Tampilan & Warna `stock` ([`ProductsTable.php:33-39,104-123`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L104-L123)) | `withSum` `active_variants_stock_sum` (`is_active = true`) + fallback koleksi eager-loaded `variants` | `product_variants (is_active, stock)`, `products.stock` | **Sinkron Penuh** |

---

## 3. Temuan Audit

### `R-01` (Rendah — Efisiensi Query Minor, Opsional / Non-Blokir)
- **Lokasi:** [`app/Filament/Resources/Orders/Schemas/OrderForm.php:116,118,872-881`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L872-L881)
- **Temuan:** `self::productHasVariants($get('product_id'))` dieksekusi pada `visible()` dan `required()` untuk setiap item repeater, menghasilkan `2 × N` query `SELECT has_variants FROM products WHERE id = ?` per roundtrip Livewire pada pesanan dengan `N` item.
- **Dampak:** Sangat kecil pada penggunaan normal admin (umumnya 1–5 item per order manual), tidak menimbulkan bug fungsional maupun celah keamanan.
- **Rekomendasi:** Tidak perlu membuka fase perbaikan terpisah; dapat di-memoize ringan saat refactoring `OrderForm.php` (882 baris) pada **P4 (`BKL-011`)**.

---

## 4. Kesimpulan
- Tidak ada celah integritas data, SQL injection, regresi pesanan historis, maupun N+1 pada daftar produk.
- **Tidak perlu tiket handoff perbaikan.** Aman untuk langsung melanjutkan ke **P2 Fase 2 (`BKL-013`)** via `/role-asisten`.
