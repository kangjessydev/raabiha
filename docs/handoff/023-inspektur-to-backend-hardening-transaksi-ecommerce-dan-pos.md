# Handoff: Hardening Transaksi E-Commerce & POS (Blocker Pra-Rilis v1.1.0)

- **Dari:** role-inspektur   **Untuk:** role-backend   **Tanggal:** 2026-11-23
- **Status:** `closed` (Laporan: [2026-11-23-backend-hardening-transaksi-ecommerce-pos-pra-rilis.md](../reports/2026-11-23-backend-hardening-transaksi-ecommerce-pos-pra-rilis.md))
- **Rujukan Laporan:** [2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md](../reports/2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md)
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))

## Ringkasan Tugas
Menutup **4 temuan Kritis (Blocker Rilis `v1.1.0`)** dan **3 temuan Sedang** pada alur transaksi E-Commerce dan POS dengan perubahan bedah mikro (*surgical fix*) tanpa merombak arsitektur.

> **Catatan Phase-Gating (`team-protocol.md`):** Total 5 file disentuh. Kerjakan dalam **2 sub-fase** (≤3 file per sub-fase) agar konteks tetap tajam:
> - **Fase A (E-Commerce & Webhook — 3 file):** `Checkout.php`, `TripayWebhookController.php`, `XenditWebhookController.php`
> - **Fase B (POS Checkout, Void & Qty Guard — 2 file):** `PosManager.php`, `PosTransactionService.php`

---

## Rincian Perbaikan Wajib

### Fase A — E-Commerce Checkout & Payment Webhooks (3 File)
1. **`T-01` [Blocker] Kunci `$shippingRates` di `app/Livewire/Checkout.php:422`**
   - Tambahkan atribut `#[Locked]` pada `public $shippingRates = [];` (dan `public $totalWeight = 0;`) agar ongkir tidak dapat dimanipulasi dari payload klien.
2. **`T-04` [Blocker] Sinkronisasi `order_items` Tripay Saat Ada Diskon/Voucher (`app/Livewire/Checkout.php:1601-1650`)**
   - Pastikan total `sum(price * quantity)` pada `$orderItems` yang dikirim ke API Tripay **selalu tepat sama** dengan `(int) $order->grand_total` (termasuk ketika `$order->discount_total > 0` akibat voucher/diskon reseller, sesuai spesifikasi Tripay).
3. **`S-02` [Sedang] Validasi `is_active` Produk/Varian & Validasi `payment_method` Online (`app/Livewire/Checkout.php:1199-1228`, `:1328-1348`, `:1571`)**
   - Di dalam blok `lockForUpdate()` stok (baris 1328–1348), tolak checkout jika `!$variant->is_active`, `!$product->is_active`, atau `$product->channel_visibility === 'pos_only'` / `$product->is_custom`.
   - Validasi `$this->payment_method` terhadap `PaymentMethod` dengan `is_active = true` dan ketersediaan online (`null`, `'both'`, `'online'`) sebelum memproses order agar payload `'tunai'` tidak bisa mem-bypass payment gateway bila tidak diaktifkan.
4. **`S-01` [Sedang] Guard Webhook `EXPIRED`/`FAILED` Terhadap Order `paid` (`TripayWebhookController.php:94-102` & `XenditWebhookController.php:93-101`)**
   - Pada cabang `EXPIRED`/`FAILED`, tambahkan guard: jika `$order->payment_status === 'paid'`, kembalikan `200` tanpa mengubah status dan tanpa memulihkan stok.
   - Pada cabang `PAID`/`SETTLED`, jika `$order->status === 'cancelled'` (pembayaran telat masuk setelah stok dikembalikan), catat log peringatan/notifikasi khusus admin dan hindari langsung mengubah ke `packed` tanpa penanganan stok (atau sesuai kebijakan aman yang dikonfirmasi user).

### Fase B — POS Checkout, Void & Qty Guard (2 File)
5. **`T-02` [Blocker] Perbaiki *Double-Checked Locking* di `app/Livewire/PosManager.php:754-815`**
   - Tepat **setelah** `$lock->block(5)` berhasil memperoleh lock (sebelum baris 806), cek ulang `if (Cache::has($lockKey))` dan kembalikan transaksi yang sudah terbuat (hindari eksekusi `completePosTransaction()` kedua kalinya).
6. **`T-03` [Blocker] Perbaiki Race Condition `voidOrder()` & Set `is_stock_restored` (`app/Livewire/PosManager.php:1208-1318`)**
   - Di dalam `DB::transaction()`, kunci ulang order dengan `Order::where('id', $orderId)->lockForUpdate()->first()`.
   - Verifikasi ulang `$lockedOrder->source === 'pos'` dan `$lockedOrder->status !== 'cancelled'` serta `!$lockedOrder->is_stock_restored`.
   - Set `'is_stock_restored' => true` dan `'stock_restored_at' => now()` saat void berhasil (dan pakai `lockForUpdate()` saat restock produk/varian) agar tidak terjadi double-void maupun double-restock saat order dihapus di Admin.
7. **`S-03` [Sedang] Validasi `quantity <= 0` di `app/Services/PosTransactionService.php:114`**
   - Tambahkan guard `if ($qty <= 0) { throw new Exception('Jumlah item harus lebih dari 0.'); }` agar payload kuantitas nol/negatif tidak menambah stok.

---

## Kriteria Selesai
- Semua 7 item (`T-01` s/d `T-04`, `S-01` s/d `S-03`) diperbaiki dan dicatat di `CHANGELOG.md`.
- Tersedia automated feature test baru yang memverifikasi seluruh skenario di atas (termasuk manipulasi `shippingRates`, Tripay items sum dengan diskon, webhook `EXPIRED` setelah `PAID`, double-checked locking POS, double-void POS + delete order tidak double-restock, dan qty negatif POS).
- Seluruh test baru & test P0–P4 terkait lulus 100%.

## Di Luar Cakupan
- Refactor besar `Checkout.php` (`BKL-011`), perbaikan temuan minor opsional yang sudah di-freeze (`S-01`/`R-01`/`R-02` dari audit sebelumnya), serta deploy ke VPS.
