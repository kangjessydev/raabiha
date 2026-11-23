# Handoff: Hardening Payload POS (Anti Console-Tampering), Checkout Locked & Voucher Dedup

- **Dari:** role-inspektur   **Untuk:** role-backend   **Tanggal:** 2026-11-23
- **Status:** `closed` (Laporan: [2026-11-23-backend-hardening-transaksi-ecommerce-pos-pra-rilis.md](../reports/2026-11-23-backend-hardening-transaksi-ecommerce-pos-pra-rilis.md))
- **Rujukan Laporan:** [2026-11-23-inspektur-audit-skeptis-tahap-3-frontend-payload-dan-db.md](../reports/2026-11-23-inspektur-audit-skeptis-tahap-3-frontend-payload-dan-db.md)
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))

## Ringkasan Tugas
Menutup **3 temuan Kritis** dan **1 temuan Sedang** hasil Audit Skeptis Tahap 3 (mencegah manipulasi harga Rp 100.000 → Rp 0 lewat Browser Console di POS, validasi kecukupan bayar tunai/split, perbaikan bug HTTP 500 `#[Locked]` di tombol Ganti Lokasi Checkout, dan deduplikasi kode voucher).

---

## Rincian Perbaikan Wajib

### 1. `F-01` [Kritis] Harga Item Katalog POS 100% Server-Side SSoT (`app/Services/PosTransactionService.php:119`, `:176-182`, `:211-217`, `:719-743`)
- Di `completePosTransaction()` dan `processPosReturn()` (barang tukar):
  - Untuk produk/varian katalog (`!is_custom`), **abaikan `$item['price']` dan `$item['original_price']` mentah dari klien**; hitung harga final murni dari database (termasuk promo/grosir yang sah di server).
  - Untuk produk kustom (`is_custom`), wajib validasi `$customPrice >= 0` dan `$customPurchasePrice >= 0`.

### 2. `F-02` [Kritis] Validasi Kecukupan Bayar Tunai & Split di Server (`app/Services/PosTransactionService.php:283-323`, `:374-404`)
- Untuk metode `cash`/`tunai`: wajib `$cashPaid >= $grandTotal` (tolak dengan Exception jika kurang bayar).
- Untuk metode `split`: wajib `split_payments` tidak kosong, setiap `amount > 0`, dan total `sum(amount) >= $grandTotal`.

### 3. `F-03` [Kritis] Perbaiki Error 500 `#[Locked]` pada Tombol Ganti/Hapus Lokasi Checkout (`app/Livewire/Checkout.php` & `resources/views/livewire/checkout.blade.php:189`, `:205`)
- Tambahkan method `clearDestination()` di `Checkout.php` untuk mereset `selectedDestinationId`, `selectedDestinationLabel`, `shippingRates`, `shipping_method`, dan `shipping_cost = 0` di sisi server.
- Ganti panggilan `$wire.set('shipping_cost', 0)` di `checkout.blade.php:189` & `:205` menjadi `$wire.clearDestination()`.

### 4. `F-04` [Sedang] Deduplikasi Kode Voucher di `app/Services/VoucherService.php:153-194`
- Pastikan kode voucher yang sama tidak dapat diproses dua kali dalam satu array `$appliedVouchersData`.

---

## Kriteria Selesai
- Semua 4 item (`F-01` s/d `F-04`) diperbaiki, dicatat di `CHANGELOG.md`, dan diverifikasi dengan automated test.
