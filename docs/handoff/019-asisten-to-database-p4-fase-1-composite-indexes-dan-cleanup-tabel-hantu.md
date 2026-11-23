# Brief: P4 Fase 1 — Composite Performance Indexes & Pembersihan Tabel Hantu (`BKL-010`)

- **Untuk:** `role-database`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-database` pada 2026-11-23; laporan di `docs/reports/2026-11-23-database-p4-fase-1-composite-indexes-dan-cleanup-tabel-hantu.md`)
- **Disetujui user:** ya (user memberikan **izin eksplisit** pada 2026-11-23 untuk `dropIfExists('coupons')`, `dropIfExists('salespages')`, dan penambahan composite indexes)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Meningkatkan performa query katalog, varian, keranjang, dan laporan transaksi sekaligus membersihkan tabel legacy tak terpakai (**`BKL-010`**) melalui migrasi Laravel yang idempoten dan kompatibel dengan MySQL (production) maupun SQLite (test suite).

## Konteks
- Acuan backlog: [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) (`BKL-010`).
- Migrasi index eksisting: [`database/migrations/2026_06_17_135249_add_performance_indexes_to_tables.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_06_17_135249_add_performance_indexes_to_tables.php) (sudah punya single index `products.is_active`, `products.price`, `orders.status`, `orders.payment_status`, `orders.created_at`).
- Verifikasi 0 referensi aktif untuk tabel `coupons` dan `salespages` di `app/`, `routes/`, dan `resources/views/`.
- Berkas target (hanya 1 file migrasi baru + 1 file test verifikasi):
  1. `database/migrations/2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables.php`
  2. `tests/Feature/DatabasePerformanceIndexesAndCleanupTest.php`

## Yang diminta
1. **Buat migrasi idempoten `database/migrations/2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables.php`:**
   - Tambahkan **composite indexes** berikut dengan nama index eksplisit yang jelas (cek eksistensi kolom/index dengan aman bila perlu agar idempoten):
     - `products`: `['is_active', 'channel_visibility', 'is_custom']` (`idx_products_channel_active`)
     - `product_variants`: `['product_id', 'is_active', 'stock']` (`idx_variants_product_active_stock`)
     - `orders`: `['source', 'payment_status', 'created_at']` (`idx_orders_source_payment_created`) dan `['status', 'created_at']` (`idx_orders_status_created`)
     - `carts`: `['user_id', 'is_buy_now']` (`idx_carts_user_buy_now`) dan `['session_id', 'is_buy_now']` (`idx_carts_session_buy_now`)
   - Hapus tabel legacy tak terpakai (dengan izin eksplisit user):
     - `Schema::dropIfExists('coupons');`
     - `Schema::dropIfExists('salespages');`
   - Tulis method `down()` yang membalik seluruh index dengan aman (drop index berdasarkan nama eksplisit; tidak perlu merekonstruksi tabel legacy hantu).
2. **Automated Test & Dokumentasi:**
   - Buat `tests/Feature/DatabasePerformanceIndexesAndCleanupTest.php` yang membuktikan:
     - Migrasi berjalan bersih di SQLite (`RefreshDatabase`) tanpa error.
     - Tabel `coupons` dan `salespages` sudah tidak ada (`Schema::hasTable(...) === false`).
     - Seluruh 6 composite indexes terpasang di tabel `products`, `product_variants`, `orders`, dan `carts`.
   - Jalankan test baru + suite regresi penuh P0–P3 untuk memastikan tidak ada migrasi atau query yang rusak.
   - Perbarui `CHANGELOG.md` (termasuk sub-bagian **`Dihapus & Alasan`** untuk penghapusan tabel `coupons` & `salespages` sesuai disiplin CHANGELOG!), tandai `BKL-010` selesai di `docs/backlog.md`, tutup handoff `019`, dan tulis laporan di `docs/reports/2026-11-23-database-p4-fase-1-composite-indexes-dan-cleanup-tabel-hantu.md`.

## Di luar cakupan
- **TIDAK mengubah** kode aplikasi di `app/` maupun `resources/` (optimasi query `R-01` dikerjakan `role-backend` di P4 Fase 2).
- **TIDAK menjalankan** migrasi ke database production VPS.
- **TIDAK menyentuh** branch `main`.

## Kriteria selesai
1. Migrasi komposit index & drop tabel `coupons`/`salespages` bersih, idempoten, dan kompatibel MySQL + SQLite.
2. Test verifikasi & regresi lulus 100%.
3. `CHANGELOG.md` (termasuk `Dihapus & Alasan`), `docs/backlog.md`, handoff `019` (`closed`), dan laporan tertulis dengan bukti konkret.
