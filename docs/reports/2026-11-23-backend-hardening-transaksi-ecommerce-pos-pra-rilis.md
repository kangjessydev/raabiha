# Laporan Perbaikan Backend: Hardening Transaksi & Fundamental E-Commerce + POS (Pra-Rilis v1.1.0)

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Handoff Ditutup:** `023`, `024`, `025` (semua status `closed`)
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))

---

## 1. Ringkasan Eksekusi (3 Fase Terkendali)

### Fase 1 — E-Commerce Checkout & Payment Webhooks (`023` Fase A + `F-03` server method)
- [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php):
  - `T-01`: Menambahkan `#[Locked]` pada `$shippingRates` & `$totalWeight`.
  - `F-03`: Menambahkan `clearDestination()` untuk reset alamat & `$shipping_cost = 0` di server.
  - `S-02`: Validasi `$this->payment_method` terhadap `PaymentMethod` aktif kanal online & pengecekan `is_active` + kanal online di dalam blok `lockForUpdate()`.
  - `T-04`: Menyelaraskan total `sum(price * quantity)` pada `order_items` Tripay agar selalu sama dengan `(int) $order->grand_total` ketika ada diskon/voucher.
- [TripayWebhookController.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Http/Controllers/Webhook/TripayWebhookController.php) & [XenditWebhookController.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Http/Controllers/Webhook/XenditWebhookController.php):
  - `S-01`: Menolak perubahan status pada callback `EXPIRED`/`FAILED` jika `$order->payment_status === 'paid'`; menandai pembayaran telat (`PAID` pada order `cancelled`) untuk verifikasi manual admin tanpa mengubah ke `packed`.

### Fase 2 — POS Hardening (`023` Fase B + `024` `K-01` + `025` `F-01`/`F-02`)
- [PosTransactionService.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php):
  - `F-01`: Menambahkan `resolveServerCatalogPrice()` sehingga harga barang katalog POS & barang tukar (`processPosReturn`) 100% dihitung di server dari DB + `PosEventPromotion::active()` (mengabaikan manipulasi `price: 0` dari Console); produk kustom divalidasi `>= 0`.
  - `F-02`: Menambahkan validasi kecukupan nominal bayar tunai (`$cashPaid >= $grandTotal`) dan split payment (`amount > 0` & `sum >= $grandTotal`) di server.
  - `S-03`: Menolak kuantitas item `<= 0`.
- [PosManager.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php):
  - `T-02`: Menambahkan *double-checked locking* (`if (Cache::has($lockKey))`) tepat setelah `$lock->block(5)` berhasil memperoleh lock di `processCheckout()`.
  - `T-03`: Mengunci baris order dengan `lockForUpdate()` di dalam `DB::transaction()` pada `voidOrder()`, memverifikasi `source === 'pos'` & `!is_stock_restored`, serta menyetel `is_stock_restored = true` & `stock_restored_at = now()`.
  - `K-01`: Memperbaiki `closeSession()` Z-Report agar menyertakan porsi tunai dari transaksi `split` aktif dan tidak mengurangkan void tunai dari shift yang sama dua kali.

### Fase 3 — MiniCart, Checkout Blade, Guest Cart Merge, Account Cancel & Voucher Dedup (`024` + `025`)
- [MiniCart.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/MiniCart.php) & [mini-cart.blade.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/mini-cart.blade.php):
  - `K-02`: Scoping kepemilikan `$this->cart->items()` (tutup IDOR), guard stok & `is_active`, harga promo `effective_price` + gambar Curator `ProductImageResolver`, serta `proceedToCheckout()` yang mereset `checkout_is_buy_now = false`.
- [checkout.blade.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php):
  - `F-03`: Mengganti `$wire.set('shipping_cost', 0)` pada tombol Hapus/Ganti Lokasi menjadi `$wire.clearDestination()`.
- [Cart.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Cart.php), [Login.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Auth/Login.php), & [Register.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Auth/Register.php):
  - `K-03`: Menambahkan `Cart::mergeGuestCartToUser()` agar keranjang guest tidak hilang saat Login/Register.
- [Account.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Account.php):
  - `S-04`: `cancelOrder()` kini memakai `lockForUpdate()` di dalam transaksi dan menolak order yang sudah `paid`.
- [VoucherService.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/VoucherService.php):
  - `F-04`: Menambahkan deduplikasi kode voucher pada `verifyAndCalculate()`.

---

## 2. Bukti Verifikasi
1. **Automated Test Suite (41/41 lulus, 197 assertions, 0 gagal):**
   - `EcommerceCheckoutAndWebhookHardeningTest.php` (7/7)
   - `PosTransactionHardeningTest.php` (5/5)
   - `MiniCartGuestMergeAndVoucherDedupTest.php` (4/4)
   - Regresi P0–P4: `BuyNowIsolationAndCartStockSyncTest`, `PosManualDiscountServerGuardTest`, `P3WidgetStockAndPosVoidNullSafeTest`, `ProductDetailVariantAndCartGuardTest`, `ChannelIsolationAndPosCustomProductTest`, `DatabasePerformanceIndexesAndCleanupTest` (semua lulus).
2. **Frontend Build:** `npm run build` sukses (`✓ built in 4.22s`).
3. **Changelog:** Diperbarui di [CHANGELOG.md](../../CHANGELOG.md).
