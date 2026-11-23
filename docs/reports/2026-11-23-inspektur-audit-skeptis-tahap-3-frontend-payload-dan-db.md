# Laporan Audit Skeptis Tahap 3: Keamanan Payload Frontend ➔ Backend & Integritas Database

- **Peran:** `role-inspektur` (Audit-Only / Read-Only Mode)
- **Tanggal:** 2026-11-23
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))
- **Status Kode:** 0 baris kode diubah (100% Read-Only).
- **Fokus:** Mencegah manipulasi data dari Browser Console / DevTools (misal mengubah harga Rp 100.000 menjadi Rp 0), bug sinkronisasi Livewire `#[Locked]`, XSS, serta integritas skema database.

---

## 1. Ringkasan Eksekutif

Audit ini memeriksa seluruh properti publik Livewire, panggilan `$wire.*` dari Blade/Alpine, penerimaan payload JSON di backend, output raw Blade `{!! !!}`, dan skema migrasi database.

Hasil:
- **E-Commerce Harga Produk:** **AMAN** — `Checkout.php:1004,1457` selalu mengambil harga dari database (`effective_price`), tidak pernah membaca harga dari frontend. *(Celah manipulasi ongkir `$shippingRates` sudah tercatat di Handoff `023` `T-01`).*
- **POS Harga & Pembayaran:** **DITEMUKAN CELAH KRITIS (`F-01` & `F-02`)** — Backend POS menerima harga item mentah dari payload frontend JS sehingga harga Rp 100.000 bisa diubah jadi Rp 0 lewat Console, serta tidak memvalidasi kecukupan bayar tunai/split di server.
- **Frontend Checkout Livewire `#[Locked]`:** **DITEMUKAN BUG KRITIS (`F-03`)** — Tombol "Ganti Lokasi" & "Hapus Lokasi" memanggil `$wire.set('shipping_cost', 0)` pada properti `#[Locked]` yang memicu error 500 di Livewire 4.
- **XSS & Skema Database:** **Mayoritas AMAN** (0 XSS pada input user; tipe uang sudah `decimal(15,2)`, `unique` constraint kunci sudah terpasang, `order_items` sudah `nullOnDelete`), dengan 1 catatan operasional FK `cascadeOnDelete` (`DB-01`).

---

## 2. Temuan KRITIS Baru

### `F-01` — Manipulasi Harga Item POS Menjadi Rp 0 (atau Negatif) Lewat Browser Console
- **Lokasi:** [PosTransactionService.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L119), [:176-182](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L176-L182), [:211-217](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L211-L217), [:719-720](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L719-L720), [:742-743](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L742-L743)
- **Bukti:**
  - Di `completePosTransaction()`:
    ```php
    $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float) $item['price'] : null;
    $finalPrice = $itemPrice !== null ? $itemPrice : ($lockedVariant->pos_discount_price ?: $basePrice);
    ```
    Pola yang sama juga ada di produk non-varian (baris 212–216) dan di `processPosReturn()` untuk harga barang tukar (baris 719–720, 742–743). Untuk produk kustom (`is_custom`, baris 119), `$customPrice` tidak dicek `>= 0`.
- **Dampak Bisnis:** Siapa pun yang membuka Browser Console di terminal POS dapat mengubah `price: 0` (atau nilai negatif) pada payload JS sebelum dikirim ke `$wire.processCheckout()` / `$wire.processReturn()`. Barang Rp 100.000 akan lolos sebagai Rp 0 tanpa otorisasi PIN supervisor dan tercatat sebagai diskon promo item.
- **Rekomendasi Perbaikan:** Hitung harga barang katalog (`!is_custom`) **100% di sisi server dari database** (`pos_discount_price` / `pos_price` / `price` serta kalkulasi grosir/promo resmi bila ada di server), abaikan `$item['price']` & `$item['original_price']` mentah dari klien. Untuk `is_custom`, wajib validasi `$customPrice >= 0` dan `$customPurchasePrice >= 0`.

### `F-02` — Pembayaran Tunai & Split POS Tidak Memvalidasi Kecukupan Nominal Bayar di Server
- **Lokasi:** [PosTransactionService.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L283-L323), [:374-404](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L374-L404)
- **Bukti:**
  - Baris 284 menghitung `$cashChange = $cashPaid - $grandTotal;` tanpa memeriksa apakah `$cashPaid >= $grandTotal` untuk metode `cash`/`tunai`, dan tetap menyetel `$paymentStatus = 'paid'` (baris 299).
  - Untuk metode `split`, tidak ada validasi di server bahwa total `sum(split_payments.amount) >= $grandTotal` maupun bahwa setiap `split.amount > 0`.
- **Dampak Bisnis:** Payload termanipulasi dengan `cash_paid = 0` untuk belanja Rp 100.000 tetap dianggap **LUNAS (`paid`)** dengan kembalian negatif `-100000`.
- **Rekomendasi Perbaikan:** Tambahkan validasi server-side: untuk `cash`/`tunai`, wajib `$cashPaid >= $grandTotal`; untuk `split`, wajib setiap `amount > 0` dan total `sum(amount) >= $grandTotal`.

### `F-03` — Tombol "Ganti Lokasi" & "Hapus Lokasi" di Checkout Memicu Error 500 Livewire `#[Locked]`
- **Lokasi:** [checkout.blade.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php#L189), [:205](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php#L205) vs [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L80-L81)
- **Bukti:**
  - `Checkout.php:80-81` mengunci `#[Locked] public $shipping_cost = 0;`.
  - Namun `checkout.blade.php:189` dan `:205` masih mengeksekusi `$wire.set('shipping_cost', 0)` langsung dari tombol frontend. Di Livewire 4, memanggil `$wire.set()` pada properti `#[Locked]` melempar `CannotUpdateLockedPropertyException` (HTTP 500).
- **Dampak Bisnis:** Pelanggan yang salah pilih kecamatan lalu klik tombol "X" atau "Ganti Lokasi" di halaman Checkout akan terkena error 500.
- **Rekomendasi Perbaikan:** Buat method server-side `clearDestination()` di `Checkout.php` untuk mereset `selectedDestinationId`, `selectedDestinationLabel`, `shippingRates`, `shipping_method`, dan `shipping_cost = 0`, lalu panggil `$wire.clearDestination()` dari kedua tombol di `checkout.blade.php`.

---

## 3. Temuan SEDANG & Catatan Database

### `F-04` [Sedang] — Deduplikasi Kode Voucher di `VoucherService::verifyAndCalculate()`
- **Lokasi:** [VoucherService.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/VoucherService.php#L153-L194)
- **Bukti:** Loop `foreach ($appliedVouchersData as $vData)` belum men-deduplikasi `$voucherCode`, sehingga secara defensif jika array berisi kode sama 2x akan dihitung 2x.
- **Rekomendasi:** Simpan `$seenCodes = []` dan `continue` jika kode sudah diproses.

### `DB-01` [Catatan Operasional DB — Non-Blocker] — `cascadeOnDelete` pada `stock_logs` & `pos_returns`
- **Lokasi:** `2026_06_09_072942_create_stock_logs_table.php:16`, `2026_07_25_110000_create_pos_returns_tables.php:19,33`
- **Bukti:** FK `stock_logs.product_id`, `pos_returns.cashier_id`, dan `pos_return_items.product_id` memakai `cascadeOnDelete()`. Jika produk/kasir dihapus permanen, histori log stok/retur ikut hilang.
- **Status:** Non-Blocker untuk rilis `v1.1.0` (karena `orders` & `order_items` sudah aman pakai `restrictOnDelete`/`nullOnDelete`); cukup pastikan di operasional admin memakai toggle `is_active = false` alih-alih hapus permanen produk/kasir yang sudah bertransaksi.

---

## 4. Yang Sudah Terverifikasi AMAN (Frontend & Database)
- **Harga Produk E-Commerce (`Checkout.php:1004,1457`):** 100% dihitung di server dari DB (`effective_price`), tidak bisa diubah lewat Console.
- **XSS Raw Blade (`{!! !!}`):** 0 celah pada input pelanggan/alamat/catatan/review di 5 view inti.
- **Integritas Skema Uang & Unik DB:** Kolom uang sudah `decimal(15,2)`; `orders.order_number`, `vouchers.code`, `products.slug/sku`, `product_variants.sku`, `users.email`, `pos_customers.phone` sudah `unique()`.
- **Proteksi Data Order DB:** `orders.user_id` pakai `restrictOnDelete()` dan `order_items.product_id`/`product_variant_id` pakai `nullOnDelete()`.
