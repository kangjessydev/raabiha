# Brief: P2 Fase 1 — Validasi Varian Pesanan Manual Admin (`BKL-009`) & Sorting Stok Varian (`BKL-004`)

- **Untuk:** `role-backend`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-backend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-backend-p2-fase-1-order-variant-dan-stok-admin.md`)
- **Disetujui user:** ya (bagian dari rencana P2 Fase 1 → Fase 2 yang telah dikonfirmasi user)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menutup 2 celah integritas data prioritas **Tinggi** di Admin Panel (**`BKL-009`** & **`BKL-004`**) sebelum kita masuk ke **P2 Fase 2** (`BKL-013` — isolasi penuh *Buy Now* & notifikasi auto-clamp stok di `/cart`).

## Konteks
- Acuan backlog: [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) (`BKL-004` & `BKL-009`).
- Berkas target (hanya 2 file produksi + 1 file test baru, memenuhi batas *Phase-Gating* ≤ 3 file):
  1. [`app/Filament/Resources/Orders/Schemas/OrderForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php) *(baris 99–137)*
  2. [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php) *(baris 90–110 + eager loading query tabel)*
  3. `tests/Feature/AdminOrderVariantAndProductStockSortTest.php` *(test baru)*

## Yang diminta
1. **`BKL-009` — Validasi Wajib Pilih Varian pada Pesanan Manual Admin ([`OrderForm.php:99-137`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L99-L137)):**
   - Pada `Select::make('product_id')->afterStateUpdated(...)`: reset `$set('product_variant_id', null)` saat produk diganti agar tidak meninggalkan ID varian dari produk sebelumnya.
   - Pada `Select::make('product_variant_id')`:
     - Wajib diisi (`->required(...)`) jika produk terpilih memiliki `has_variants === true` (validasi tetap berlaku di sisi server saat submit, bukan hanya UI).
     - Tambahkan rule validasi kepemilikan varian (`exists:product_variants,id` dengan constraint `product_id = $get('product_id')`) agar varian dari produk lain tidak bisa disisipkan.
     - Tampilkan label `(Non-Aktif)` pada opsi varian yang `is_active = false` (jangan sembunyikan total agar pesanan historis tetap terbaca saat Edit), dan cegah pemilihan varian non-aktif untuk pesanan baru jika memungkinkan atau beri tanda jelas.
     - Sembunyikan/disable field varian secara rapi (`->visible(...)`) saat produk tidak memiliki varian (`has_variants = false`).
2. **`BKL-004` — Sorting & Sinkronisasi Stok Produk Bervarian di Tabel Admin ([`ProductsTable.php:90-110`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L90-L110)):**
   - Ganti `->sortable()` mentah pada kolom `stock` dengan custom SQL sorting (`->sortable(query: fn (Builder $query, string $direction) => ...)`) berbasis `ORDER BY CASE WHEN products.has_variants = 1 THEN COALESCE((SELECT SUM(pv.stock) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), 0) ELSE COALESCE(products.stock, 0) END {$direction}` (pastikan kompatibel dengan MySQL & SQLite test).
   - Selaraskan tampilan `formatStateUsing` & `color` agar memakai stok varian aktif (`$record->total_stock` atau `$record->variants->where('is_active', true)->sum('stock')`), bukan menjumlahkan varian non-aktif.
   - Pastikan query tabel melakukan eager loading / `withSum` agar tidak terjadi N+1 query pada daftar produk.
3. **Automated Tests & Dokumentasi:**
   - Buat feature test `tests/Feature/AdminOrderVariantAndProductStockSortTest.php` yang membuktikan:
     - Form/validasi `OrderForm` menolak penyimpanan item produk bervarian jika `product_variant_id = null`, dan menerima jika varian valid dari produk yang sama dipilih.
     - Sorting kolom `stock` DESC/ASC di `ProductsTable` mengurutkan produk bervarian berdasarkan total stok varian aktifnya (bukan kolom mentah `products.stock = 0`).
   - Jalankan test suite terkait hingga 100% hijau, perbarui `CHANGELOG.md` (`[Unreleased]`), perbarui status `BKL-004` & `BKL-009` di `docs/backlog.md`, dan tulis laporan di `docs/reports/2026-11-23-backend-p2-fase-1-order-variant-dan-stok-admin.md`.

## Di luar cakupan
- **TIDAK mengubah** `Checkout.php`, `Cart.php`, maupun `ProductDetail.php` (agenda **P2 Fase 2 — `BKL-013`**).
- **TIDAK mengubah** modul POS (`BKL-001/002/003`) maupun Widget Dashboard (`BKL-005`) (agenda **P3**).
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. `OrderForm.php` mewajibkan `product_variant_id` valid untuk produk bervarian dan mereset `product_variant_id` saat `product_id` berubah.
2. `ProductsTable.php` mengurutkan kolom `stock` secara akurat untuk produk berdiri sendiri maupun bervarian (memakai stok varian aktif) tanpa N+1 query.
3. Seluruh automated test baru & test regresi P0/P1 lulus 100%.
4. `CHANGELOG.md`, `docs/backlog.md`, dan laporan `docs/reports/2026-11-23-backend-p2-fase-1-order-variant-dan-stok-admin.md` tertulis dengan bukti konkret.

## Pertanyaan terbuka
- Tidak ada (seluruh keputusan ruang lingkup P2 telah dikonfirmasi user).
