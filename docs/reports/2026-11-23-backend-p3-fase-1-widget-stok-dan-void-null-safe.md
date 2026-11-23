# Laporan Pengerjaan: P3 Fase 1 — Penyelarasan Widget Stok Habis (`BKL-005`) & Null-Safe Void POS (`BKL-002`)

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`016-asisten-to-backend-p3-fase-1-widget-stok-dan-void-null-safe.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/016-asisten-to-backend-p3-fase-1-widget-stok-dan-void-null-safe.md) (`closed`)

---

## Ringkasan Perubahan
Hanya menyentuh **2 file produksi** (`+16/-8` baris) + **1 file feature test baru**:

1. **`BKL-005` — [`ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php#L19-L30) (`+7/-3` baris):**
   - Menyelaraskan query `$outOfStock` agar produk bervarian (`has_variants = true`) dihitung *"Stok Habis / Menipis"* bila memiliki minimal 1 varian aktif (`is_active = true`) dengan `stock <= 0` atau belum memiliki varian aktif sama sekali (`whereDoesntHave('variants', fn ($q) => $q->where('is_active', true))`).
   - Varian non-aktif (`is_active = false`) yang stoknya `0` tidak memicu *false positive*.

2. **`BKL-002` — [`PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1214-L1336) (`+10/-6` baris):**
   - Menambahkan `$voidActorLabel = $supervisor ? 'Disetujui Supervisor: ' . $supervisor->name : 'Oleh Kasir: ' . (Auth::user()?->name ?? 'Kasir');` dan menggunakannya pada seluruh 4 titik log/notifikasi di `voidOrder()` (`StockLog`, 2× `Cashflow`, dan notifikasi `notify`).
   - Memastikan pembatalan nota saat `pos_require_pin_for_void = false` sukses 100% tanpa crash `Attempt to read property "name" on null`.

3. **Automated Feature Test — [`P3WidgetStockAndPosVoidNullSafeTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/P3WidgetStockAndPosVoidNullSafeTest.php):**
   - Menguji 4 kombinasi produk pada `ProductStatsWidget` dan menguji `PosManager::voidOrder()` tanpa PIN supervisor (`pos_require_pin_for_void = 0`).

---

## Bukti Verifikasi
- `php artisan test --filter="P3WidgetStockAndPosVoidNullSafeTest|PosVoidOrderTest|PosSupervisorPinTest"`: **7/7 passed (30 assertions, 2.542s)**.
- `git diff --stat app/`:
  - `app/Filament/Resources/Products/Widgets/ProductStatsWidget.php | 10 +++++++---`
  - `app/Livewire/PosManager.php | 14 +++++++++-----`
  - `2 files changed, 16 insertions(+), 8 deletions(-)`

---

## Checklist SOP
- [x] Membaca handoff `016` dan mematuhi ADR-003 (tidak menambahkan rate limiter PIN).
- [x] Phase-Gating terpenuhi (2 file produksi, `+16/-8` baris).
- [x] Seluruh automated test baru & regresi POS lulus 100%.
- [x] `CHANGELOG.md`, `docs/backlog.md`, handoff `016` (`closed`), dan laporan tertulis dengan bukti konkret.

---

## Log Penyimpangan
- Tidak ada penyimpangan dari rencana.
