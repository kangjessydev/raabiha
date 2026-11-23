# Brief: P3 Fase 2 — Hardening Otorisasi Diskon Manual Kasir di Sisi Server (`BKL-001`)

- **Untuk:** `role-backend`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-backend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-backend-p3-fase-2-pos-manual-discount-server-guard.md`)
- **Disetujui user:** ya (bagian dari rencana P3 Fase 1 → Fase 2 yang telah dikonfirmasi user pada 2026-11-23)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menutup celah otorisasi **`BKL-001`** agar batasan diskon manual kasir (`pos_manual_discount_max_percent_without_pin` & `pos_manual_discount_max_rp_without_pin`) divalidasi secara ketat di sisi server (`PosTransactionService::createOrder()`), sambil tetap mulus dengan alur modal PIN Supervisor yang sudah ada di POS (`PosManager::verifySupervisorPin()`).

## Konteks
- Acuan backlog: [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) (`BKL-001`).
- **ADR-003 ([`docs/decisions.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/decisions.md)):** Jangan mengubah/menambah aturan rate limit PIN supervisor.
- Temuan teknis alur eksisting:
  - Di `resources/views/livewire/pos-manager.blade.php:5870`, saat kasir menginput diskon manual melebihi batas, UI memanggil `PosManager::verifySupervisorPin($supervisorId, $pin, 'general')` terlebih dahulu.
  - Kemudian saat klik bayar (`pos-manager.blade.php:8007-8042`), UI memanggil `PosManager::processCheckout($payload)` yang membawa `manual_discount` tanpa mengirim ulang PIN mentah.
- Berkas target (2 file produksi + 1 file test):
  1. [`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php) *(baris ~177–225)*
  2. [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php) *(method `verifySupervisorPin()` baris ~1181 dan `processCheckout()` baris ~729)*
  3. `tests/Feature/PosManualDiscountServerGuardTest.php` *(test baru)*

## Yang diminta
1. **Token Otorisasi Sisi Server di [`PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php):**
   - Di `verifySupervisorPin($supervisorId, $pin, $actionType = '')`: ketika `$supervisor` terverifikasi valid, simpan otorisasi singkat di Laravel server-side session:
     `session(['pos_supervisor_discount_auth' => ['supervisor_id' => $supervisor->id, 'expires_at' => now()->addMinutes(15)->timestamp]]);`
   - Di `processCheckout($payload)`:
     - **Wajib bersihkan** (`unset($payload['manual_discount_authorized_by'])`) agar klien tidak bisa memalsukan field ini melalui DevTools/Livewire payload.
     - Periksa `session('pos_supervisor_discount_auth')`; jika ada dan belum kedaluwarsa (`expires_at >= now()->timestamp`), injeksikan `$payload['manual_discount_authorized_by'] = (int) $auth['supervisor_id']` sebelum memanggil `PosTransactionService::createOrder()`.
     - Setelah `createOrder()` sukses, hapus token sekali pakai tersebut (`session()->forget('pos_supervisor_discount_auth')`).

2. **Server-Side Guard Diskon Manual di [`PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L177-L225):**
   - Pastikan `$manualDiscount = max(0.0, (float) ($data['manual_discount'] ?? $data['discount'] ?? 0));` (cegah diskon negatif).
   - Baca pengaturan toko dari `SiteSetting`:
     - `pos_require_pin_for_manual_discount` (default `true` jika belum diset)
     - `pos_manual_discount_max_percent_without_pin` (default `20`)
     - `pos_manual_discount_max_rp_without_pin` (default `50000`)
   - Jika `$manualDiscount > 0` dan `$requirePinForManualDiscount`:
     - Cek apakah `$manualDiscount > $maxRpWithoutPin` **ATAU** (`$subtotal > 0 && (($manualDiscount / $subtotal) * 100) > $maxPercentWithoutPin`).
     - Jika melewati ambang batas, verifikasi bahwa salah satu syarat berikut terpenuhi:
       1. Kasir yang sedang login (`User::find($cashierId)`) sendiri memiliki role/hak supervisor (`is_pos_supervisor` atau role `super_admin`/`owner`/`admin`/`manager`/`supervisor`), **ATAU**
       2. `$data['manual_discount_authorized_by']` (dari session server `PosManager`) merujuk ke `User` valid yang memiliki hak supervisor, **ATAU**
       3. `$data['supervisor_id']` + `$data['supervisor_pin']` cocok dengan `pos_pin` milik `User` berhak supervisor.
     - Jika tidak ada yang terpenuhi, lempar `new Exception('Diskon manual melebihi batas tanpa PIN. Diperlukan otorisasi PIN Supervisor.');`.
     - Catat `manual_discount_supervisor_id` pada `$paymentDetails` jika disetujui oleh supervisor agar jejak audit tersimpan rapi.

3. **Automated Tests & Dokumentasi:**
   - Buat `tests/Feature/PosManualDiscountServerGuardTest.php` yang membuktikan:
     - Kasir memberi diskon manual dalam batas (≤ 20% dan ≤ Rp 50.000) → transaksi sukses tanpa PIN supervisor.
     - Kasir mencoba bypass diskon manual melebihi batas (mis. Rp 100.000 atau > 20%) tanpa PIN supervisor (termasuk percobaan memalsukan `manual_discount_authorized_by` di payload) → **ditolak** di server.
     - Kasir memverifikasi PIN supervisor via `verifySupervisorPin()` lalu `processCheckout()` dengan diskon besar → **sukses**, dan token session otomatis hangus setelah transaksi selesai (transaksi berikutnya dengan diskon besar tetap ditolak tanpa verifikasi ulang).
     - Pemanggilan langsung `PosTransactionService::createOrder()` dengan `supervisor_id` + `supervisor_pin` valid untuk diskon besar → **sukses**.
     - Jika `pos_require_pin_for_manual_discount = 0`, diskon besar tanpa PIN diizinkan sesuai pengaturan toko.
   - Jalankan test baru beserta `PosTransactionTest`, `PosVoidOrderTest`, `PosSupervisorPinTest`, dan `P3WidgetStockAndPosVoidNullSafeTest` hingga 100% hijau.
   - Perbarui `CHANGELOG.md`, tandai `BKL-001` selesai di `docs/backlog.md`, tutup handoff `017`, dan tulis laporan di `docs/reports/2026-11-23-backend-p3-fase-2-pos-manual-discount-server-guard.md`.

## Di luar cakupan
- **TIDAK mengubah** `resources/views/livewire/pos-manager.blade.php` maupun `resources/views/livewire/cart.blade.php` (`S-01` akan dikerjakan oleh `role-frontend` di P3 Fase 3).
- **DILARANG** menambah/mengubah rate limit PIN supervisor (**ADR-003**).
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. Diskon manual melebihi ambang batas tidak bisa di-bypass dari payload klien tanpa otorisasi supervisor yang terverifikasi di server.
2. Alur UI POS eksisting (`verifySupervisorPin` → `processCheckout`) tetap berjalan mulus tanpa friksi tambahan.
3. Seluruh automated test baru & test regresi POS lulus 100%.
4. `CHANGELOG.md`, `docs/backlog.md` (`BKL-001` → selesai), handoff `017` (`closed`), dan laporan tertulis dengan bukti konkret.
