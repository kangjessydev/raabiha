# Laporan Pengerjaan: P4 Fase 1 — Composite Performance Indexes & Pembersihan Tabel Hantu (`BKL-010`)

- **Peran:** `role-database`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`019-asisten-to-database-p4-fase-1-composite-indexes-dan-cleanup-tabel-hantu.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/019-asisten-to-database-p4-fase-1-composite-indexes-dan-cleanup-tabel-hantu.md) (`closed`)
- **Handoff Lanjutan:** [`020-database-to-backend-p4-fase-2-optimasi-query-minor.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/020-database-to-backend-p4-fase-2-optimasi-query-minor.md) (`open`, untuk `role-backend`)

---

## Ringkasan Perubahan
1. **Migrasi Idempoten — [`2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_11_23_090000_add_composite_indexes_and_drop_unused_legacy_tables.php):**
   - Menambahkan 6 composite performance indexes (dengan pengecekan `Schema::hasTable`, `Schema::hasColumns`, & `Schema::hasIndex` agar idempoten di MySQL & SQLite):
     - `products`: `idx_products_channel_active` (`is_active, channel_visibility, is_custom`)
     - `product_variants`: `idx_variants_product_active_stock` (`product_id, is_active, stock`)
     - `orders`: `idx_orders_source_payment_created` (`source, payment_status, created_at`) & `idx_orders_status_created` (`status, created_at`)
     - `carts`: `idx_carts_user_buy_now` (`user_id, is_buy_now`) & `idx_carts_session_buy_now` (`session_id, is_buy_now`)
   - Menghapus tabel legacy tak terpakai `coupons` dan `salespages` (`Schema::dropIfExists`) atas izin eksplisit user.
2. **Test Verifikasi — [`DatabasePerformanceIndexesAndCleanupTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/DatabasePerformanceIndexesAndCleanupTest.php):**
   - Memverifikasi ketiadaan tabel `coupons` & `salespages` serta keberadaan seluruh 6 composite indexes.

## Bukti Verifikasi
- `php artisan test --filter="DatabasePerformanceIndexesAndCleanupTest|PosManualDiscountServerGuardTest|P3WidgetStockAndPosVoidNullSafeTest|BuyNowIsolationAndCartStockSyncTest|AdminOrderVariantAndProductStockSortTest|ProductDetailVariantAndCartGuardTest|ChannelIsolationAndPosCustomProductTest|ProductImageSingleSourceOfTruthTest"`:
  - Hasil: **35/35 tests passed (202 assertions, 9.388s)**.

## Checklist SOP
- [x] Izin eksplisit user diperoleh sebelum `DROP TABLE coupons` & `salespages`.
- [x] Migrasi idempoten & kompatibel MySQL + SQLite.
- [x] `CHANGELOG.md` (termasuk sub-bagian `Dihapus & Alasan`), `docs/backlog.md`, handoff `019` (`closed`), dan handoff `020` (`open`) diperbarui.
