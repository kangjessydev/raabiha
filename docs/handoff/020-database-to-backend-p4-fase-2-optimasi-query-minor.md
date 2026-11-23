# Brief: P4 Fase 2 (Ringan) — Optimasi Query Minor Hasil Audit P2 & P3 (`R-01`)

- **Untuk:** `role-backend`   **Dari:** `role-database` / `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-backend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-backend-p4-fase-2-optimasi-query-minor.md`)
- **Disetujui user:** ya (bagian dari **P4 Opsi A** yang dikonfirmasi user pada 2026-11-23)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menyelesaikan 2 optimasi query ringan (`R-01` dari Audit P2 Fase 1 & Audit P3) tanpa mengubah perilaku fungsional, serta memperbarui status `BKL-011` di `docs/backlog.md` sesuai keputusan user (Opsi A — refactoring besar `Checkout.php` ditunda pasca-rilis).

## Yang diminta
1. **[`app/Services/PosTransactionService.php:50-56`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L50-L56):**
   - Gabungkan 3 query `SiteSetting::where('key', ...)->value('value')` (`pos_require_pin_for_manual_discount`, `pos_manual_discount_max_percent_without_pin`, `pos_manual_discount_max_rp_without_pin`) menjadi **1 query tunggal** `SiteSetting::whereIn('key', [...])->pluck('value', 'key')`.
2. **[`app/Filament/Resources/Orders/Schemas/OrderForm.php:872-881`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L872-L881):**
   - Tambahkan memoize ringan per request pada `productHasVariants(mixed $productId)` (misal menggunakan `static array $cache = []` berbasis `spl_object_id(request())` atau key per request agar tidak pernah bocor antar test/request) sehingga `visible()` & `required()` pada item repeater tidak mengulang query identik untuk `product_id` yang sama.
3. **Verifikasi & Dokumentasi:**
   - Jalankan `php artisan test --filter="PosManualDiscountServerGuardTest|AdminOrderVariantAndProductStockSortTest|DatabasePerformanceIndexesAndCleanupTest"`.
   - Perbarui `CHANGELOG.md`, perbarui catatan `BKL-011` di `docs/backlog.md` (status ditunda pasca-rilis sesuai Opsi A), tutup handoff `020`, dan tulis laporan ringkas di `docs/reports/2026-11-23-backend-p4-fase-2-optimasi-query-minor.md`.

## Di luar cakupan
- **TIDAK memecah/mengubah** arsitektur `Checkout.php` maupun `ProductDetail.php` (Opsi A — ditunda pasca-rilis).
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. 3 query `SiteSetting` di `PosTransactionService::authorizeManualDiscount()` tergabung menjadi 1 query.
2. `OrderForm::productHasVariants()` ter-memoize aman per request tanpa kebocoran state antar test.
3. Seluruh test lulus 100%, `CHANGELOG.md`, `docs/backlog.md`, handoff `020` (`closed`), dan laporan tertulis.
