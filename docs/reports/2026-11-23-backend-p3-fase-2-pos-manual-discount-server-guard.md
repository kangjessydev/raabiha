# Laporan Pengerjaan: P3 Fase 2 — Hardening Otorisasi Diskon Manual Kasir di Sisi Server (`BKL-001`)

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`017-asisten-to-backend-p3-fase-2-pos-manual-discount-server-guard.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/017-asisten-to-backend-p3-fase-2-pos-manual-discount-server-guard.md) (`closed`)
- **Handoff Lanjutan:** [`018-backend-to-frontend-p3-fase-3-cart-effective-price.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/018-backend-to-frontend-p3-fase-3-cart-effective-price.md) (`open`, untuk `role-frontend`)

---

## Ringkasan Perubahan
Hanya menyentuh **2 file produksi** (`+93/-6` baris kumulatif P3 di kedua file) + **1 file feature test baru**:

1. **[`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L25-L88) (`+68/-1` baris):**
   - Menambahkan `isSupervisorUser()` dan `authorizeManualDiscount()` yang memvalidasi `manual_discount` di sisi server terhadap `pos_require_pin_for_manual_discount`, `pos_manual_discount_max_percent_without_pin` (default `20`), dan `pos_manual_discount_max_rp_without_pin` (default `50000`).
   - Mencegah diskon negatif (`max(0.0, ...)`), mewajibkan otorisasi supervisor jika melewati ambang batas (kasir berhak supervisor, token session terverifikasi `manual_discount_authorized_by`, atau `supervisor_id` + `supervisor_pin` valid), serta menyimpan jejak audit `manual_discount_supervisor_id` pada `payment_details`.

2. **[`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L736-L810) (`+17/-0` baris untuk Fase 2):**
   - Di `verifySupervisorPin()` ([`PosManager.php:1192-1203`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1192-L1203)): menyimpan token otorisasi sekali pakai di server-side session (`pos_supervisor_discount_auth`, TTL 15 menit).
   - Di `processCheckout()` ([`PosManager.php:736-749,806-809`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L736-L749)): membuang segala upaya pemalsuan `manual_discount_authorized_by` dari payload klien (`unset`), menyuntikkan nilai hanya dari session server yang sah, dan menghanguskan token setelah transaksi sukses (`session()->forget('pos_supervisor_discount_auth')`).

3. **Automated Feature Test — [`PosManualDiscountServerGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/PosManualDiscountServerGuardTest.php):**
   - 5 skenario test penuh: diskon dalam batas tanpa PIN, penolakan bypass + pemalsuan payload, alur verifikasi PIN → checkout sekali pakai → token hangus, validasi `supervisor_id` + `supervisor_pin` langsung di service, serta kelonggaran saat `pos_require_pin_for_manual_discount = 0`.

---

## Bukti Verifikasi
- `php artisan test --filter="PosManualDiscountServerGuardTest|PosVoidOrderTest|PosSupervisorPinTest|P3WidgetStockAndPosVoidNullSafeTest"`: **12/12 passed (49 assertions, 3.602s)**.

---

## Checklist SOP
- [x] Membaca handoff `017` dan mematuhi ADR-003 (tidak mengubah/menambah rate limit PIN).
- [x] Phase-Gating terpenuhi (2 file produksi).
- [x] Proteksi anti-forging payload klien & token sekali pakai di server-side session.
- [x] Seluruh 12 automated test lulus 100%.
- [x] `CHANGELOG.md`, `docs/backlog.md` (`BKL-001` → selesai), handoff `017` (`closed`), handoff `018` (`open`), dan laporan tertulis dengan bukti konkret.

---

## Log Penyimpangan
- Tidak ada penyimpangan dari rencana.
