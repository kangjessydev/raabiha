# Brief: P3 Fase 1 — Penyelarasan Widget Stok Habis (`BKL-005`) & Null-Safe Void POS (`BKL-002`)

- **Untuk:** `role-backend`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-backend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-backend-p3-fase-1-widget-stok-dan-void-null-safe.md`)
- **Disetujui user:** ya (rencana P3 Fase 1 → Fase 2 + ADR-003 telah dikonfirmasi user pada 2026-11-23)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menyelesaikan 2 perbaikan integritas logika & stabilitas POS di **P3 Fase 1** (hanya **2 file produksi** + **1 file test**):
1. **`BKL-005`**: Menyelaraskan kriteria *"Stok Habis / Menipis"* di [`ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php) agar sesuai keputusan bisnis user (produk bervarian dihitung perlu restock jika **salah satu varian aktifnya habis**, atau belum memiliki varian aktif).
2. **`BKL-002`**: Memasang null-safe guard pada nama supervisor di [`PosManager::voidOrder()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1189) agar pembatalan nota tanpa PIN supervisor (`pos_require_pin_for_void = false`) tidak crash `Attempt to read property "name" on null`.

## Konteks
- Acuan backlog: [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) (`BKL-005` & `BKL-002`).
- **ADR-003 ([`docs/decisions.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/decisions.md)):** `BKL-003` (rate limiting PIN supervisor) **TIDAK diterapkan** sesuai kebijakan klien.
- Berkas target (2 file produksi + 1 file test):
  1. [`app/Filament/Resources/Products/Widgets/ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php)
  2. [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php) *(method `voidOrder()`, baris ~1189–1340)*
  3. `tests/Feature/P3WidgetStockAndPosVoidNullSafeTest.php` *(test baru)*

## Yang diminta
1. **`BKL-005` — Penyelarasan Query `$outOfStock` di [`ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php#L19-L26):**
   - Periksa juga kriteria yang dipakai di `StockManagementResource` agar selaras.
   - Pastikan query `$outOfStock` menghitung:
     - Produk non-varian (`has_variants = false`) dengan `stock <= 0`, **ATAU**
     - Produk bervarian (`has_variants = true`) yang memiliki **minimal 1 varian aktif (`is_active = true`) dengan `stock <= 0`**, **ATAU** tidak memiliki varian aktif sama sekali (`whereDoesntHave('variants', fn ($q) => $q->where('is_active', true))`).
   - Pastikan varian non-aktif (`is_active = false`) yang stoknya `0` **tidak** memicu produk dianggap perlu restock jika seluruh varian aktifnya masih berstok `> 0`.

2. **`BKL-002` — Null-Safe Supervisor Name di [`PosManager::voidOrder()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1189-L1340):**
   - Periksa seluruh akses `$supervisor->name` di dalam `voidOrder()` (sekitar baris 1257, 1278, 1292, 1331) dan pastikan saat `$supervisor` bernilai `null` (ketika `pos_require_pin_for_void = false`), string catatan/log/pesan memakai label null-safe yang jelas (misal: `$actorLabel = $supervisor ? ('Disetujui Supervisor: ' . $supervisor->name) : ('Oleh Kasir: ' . (Auth::user()?->name ?? 'Kasir'));`).
   - Pastikan tidak ada lagi akses `$supervisor->name` tanpa null-safe guard di `voidOrder()`.

3. **Automated Tests & Dokumentasi:**
   - Buat `tests/Feature/P3WidgetStockAndPosVoidNullSafeTest.php` yang membuktikan:
     - `ProductStatsWidget` menghitung produk bervarian sebagai *"Stok Habis / Menipis"* ketika **salah satu** varian aktifnya memiliki `stock = 0` (meskipun varian aktif lain stoknya `10`), dan **tidak** menghitungnya jika semua varian aktif punya stok `> 0` (meskipun ada varian non-aktif berstok `0`).
     - `PosManager::voidOrder()` berhasil membatalkan nota dan mengembalikan stok + menulis `StockLog` tanpa error ketika `pos_require_pin_for_void = false` dan `$supervisorId = null, $supervisorPin = null`.
     - `PosManager::voidOrder()` tetap mencatat nama supervisor ketika `pos_require_pin_for_void = true` dengan PIN valid.
   - Jalankan test baru beserta `PosVoidOrderTest` & `PosSupervisorPinTest` hingga 100% hijau.
   - Perbarui `CHANGELOG.md`, tandai `BKL-002` & `BKL-005` selesai di `docs/backlog.md`, tutup handoff `016`, dan tulis laporan di `docs/reports/2026-11-23-backend-p3-fase-1-widget-stok-dan-void-null-safe.md`.

## Di luar cakupan
- **DILARANG** menambahkan rate limiter pada PIN supervisor (**ADR-003**).
- **TIDAK mengubah** validasi diskon manual `PosTransactionService.php` (agenda **P3 Fase 2 — `BKL-001`**).
- **TIDAK mengubah** `resources/views/livewire/cart.blade.php` (`S-01` akan dikerjakan oleh `role-frontend` di akhir P3).
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. `ProductStatsWidget` selaras dengan definisi bisnis user (salah satu varian aktif habis = perlu restock).
2. `PosManager::voidOrder()` 100% null-safe saat `pos_require_pin_for_void = false`.
3. Seluruh automated test baru & test regresi POS (`PosVoidOrderTest`, `PosSupervisorPinTest`) lulus 100%.
4. `CHANGELOG.md`, `docs/backlog.md`, handoff `016` (`closed`), dan laporan tertulis dengan bukti konkret.
