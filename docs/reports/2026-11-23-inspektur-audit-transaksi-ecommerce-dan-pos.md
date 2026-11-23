# Laporan Audit Forensik: Alur Transaksi E-Commerce & POS (Pra-Rilis v1.1.0)

- **Peran:** `role-inspektur` (Audit-Only / Read-Only Mode)
- **Tanggal:** 2026-11-23
- **Rujukan Brief:** [021-asisten-to-inspektur-audit-transaksi-ecommerce-dan-pos.md](../handoff/021-asisten-to-inspektur-audit-transaksi-ecommerce-dan-pos.md)
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))
- **Acuan Standar:** `.agents/references/backend-architecture-best-practices.md` (transaksi DB atomic, row-level locking, idempotency, validasi server-side).
- **Status Kode:** 0 baris kode diubah (100% Read-Only).

---

## 1. Keputusan Kelayakan Rilis (`v1.1.0`)

**Status: NO-GO SEMENTARA — Ditemukan 4 Temuan Kritis (Blocker) & 3 Temuan Sedang** pada alur transaksi inti yang sangat disarankan ditutup terlebih dahulu sebelum merge ke `main` dan deploy ke VPS produksi. Seluruh perbaikan bersifat bedah mikro (*surgical fix*), tidak menyentuh desain/arsitektur besar.

---

## 2. Matriks Verifikasi 6 Area Transaksi

| Area Transaksi | Komponen Diperiksa | Status | Ringkasan Bukti |
| :--- | :--- | :--- | :--- |
| 1. Race Condition & Stock Lock (E-Commerce) | `Checkout.php:1310-1517`, `OrderStockService.php:20-98` | **AMAN** *(1 celah aktif/non-aktif)* | `Cache::lock` non-blocking + `DB::transaction` + `lockForUpdate()` aktif; `OrderStockService` idempoten (`is_stock_restored`). Celah: tidak cek `is_active` produk/varian saat lock (`S-02`). |
| 2. Race Condition & Double Submit (POS) | `PosManager.php:750-836`, `PosTransactionService.php:97-235` | **BLOCKER (`T-02`)** | Bug *double-checked locking* di `PosManager.php:778-808` memungkinkan klik ganda menghasilkan 2 nota POS. Qty `<= 0` belum dijaga (`S-03`). |
| 3. Void POS & Restock Idempotency | `PosManager.php:1206-1347`, `OrderObserver.php:391-401` | **BLOCKER (`T-03`)** | `voidOrder()` tidak memakai `lockForUpdate()` & tidak menyetel `is_stock_restored = true` → rentan double-void & double-restock saat order dihapus. |
| 4. Payment Gateway & Webhook (Tripay/Xendit) | `Checkout.php:1601-1766`, `TripayWebhookController.php`, `XenditWebhookController.php` | **BLOCKER (`T-04`)** | Signature HMAC/token & idempotency `PAID` sudah aman. Namun payload `order_items` Tripay tidak memasukkan diskon (`T-04`), dan webhook `EXPIRED`/`FAILED` belum menolak order yang sudah `paid` (`S-01`). |
| 5. Manipulasi Ongkir, Voucher & Payment Method | `Checkout.php:422`, `:1199-1259`, `:1571`, `VoucherService.php:140-201` | **BLOCKER (`T-01`)** | `public $shippingRates` tidak `#[Locked]` → ongkir bisa dimanipulasi dari klien (`T-01`). `payment_method` belum divalidasi terhadap tabel aktif online (`S-02`). |
| 6. Integritas POS Shift, Diskon Manual & Cashflow | `PosTransactionService.php:42-91`, `:373-417` | **AMAN** | Otorisasi diskon manual server-side (`BKL-001`) & token sesi sekali pakai terverifikasi; pencatatan cashflow split/single konsisten. |

---

## 3. Detail Temuan KRITIS / BLOCKER RILIS

### `T-01` — Manipulasi Ongkir dari Sisi Klien via Properti `$shippingRates` Tidak Terkunci
- **Lokasi:** [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L422) dan [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L1231-L1259)
- **Bukti:**
  - Baris 81 sudah mengunci `#[Locked] public $shipping_cost = 0;`, tetapi baris 422 mendeklarasikan `public $shippingRates = [];` **tanpa `#[Locked]`**.
  - Di `processCheckout()` (baris 1231–1259), `generateShippingRates()` hanya dipanggil jika `empty($this->shippingRates)`, lalu `$actualRatePrice` diambil dari `$this->shippingRates`.
- **Dampak Bisnis:** Pembeli dapat memodifikasi payload Livewire `shippingRates` menjadi ongkir Rp 100 dan lolos sebagai ongkir sah di server.
- **Rekomendasi Perbaikan:** Tambahkan `#[Locked]` pada `public $shippingRates = [];` (dan `public $totalWeight = 0;`).

### `T-02` — Bug *Double-Checked Locking* di POS Checkout (Klik Ganda Bisa Bikin 2 Transaksi)
- **Lokasi:** [PosManager.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L754-L815)
- **Bukti:**
  - Baris 758 mengecek `if (Cache::has($lockKey))` sebelum memperoleh lock.
  - Baris 780 memanggil `$lock->block(5)`. Ketika 2 request datang bersamaan dengan `idempotency_key` sama, Request A selesai dalam <1 detik lalu menulis `Cache::put($lockKey, $order->id)`. Request B yang menunggu di `block(5)` **memperoleh lock tanpa timeout**, melewati blok `catch`, dan langsung mengeksekusi baris 808 `$service->completePosTransaction($data)` untuk kedua kalinya.
- **Dampak Bisnis:** Klik ganda atau *network retry* di kasir membuat 2 nota, memotong stok 2x, dan mencatat kas masuk 2x untuk 1 pembayaran.
- **Rekomendasi Perbaikan:** Lakukan pengecekan ulang `if (Cache::has($lockKey))` tepat **setelah** `$lock->block(5)` berhasil memperoleh lock (sebelum memanggil `completePosTransaction`).

### `T-03` — Race Condition Double-Void POS & Risiko Double-Restock Saat Hapus Order
- **Lokasi:** [PosManager.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1208-L1318) dan [OrderObserver.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Observers/OrderObserver.php#L391-L401)
- **Bukti:**
  - `voidOrder()` mengecek `$order->status === 'cancelled'` di luar transaksi (baris 1214) tanpa `lockForUpdate()`. Dua klik Void bersamaan akan lolos bersama → restock 2x, cashflow `pos_void` keluar 2x (lewat fallback baris 1307), dan stempel loyalti ditarik 2x.
  - `voidOrder()` juga tidak menyetel `is_stock_restored = true`. Jika nota POS yang sudah di-void kemudian dihapus di Admin, `OrderObserver::deleting()` (baris 394) akan memulihkan stok untuk kedua kalinya.
- **Rekomendasi Perbaikan:** Kunci order di dalam `DB::transaction()` menggunakan `Order::where('id', $orderId)->lockForUpdate()->first()`, verifikasi ulang `$lockedOrder->status !== 'cancelled'` dan `$lockedOrder->source === 'pos'`, serta update `'is_stock_restored' => true, 'stock_restored_at' => now()`.

### `T-04` — Ketidakcocokan Total `order_items` vs `amount` pada Transaksi Tripay Saat Ada Diskon/Voucher
- **Lokasi:** [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L1601-L1650)
- **Bukti:**
  - `$amount = (int) $order->grand_total` (baris 1597) sudah dikurangi `$order->discount_total`.
  - Namun `$orderItems` (baris 1602–1638) hanya memasukkan harga produk penuh + ongkir + `paymentFee` tanpa penyesuaian diskon (berbeda dengan Xendit di baris 1710–1717 yang menyertakan item diskon negatif).
- **Dampak Bisnis:** API Tripay mewajibkan `sum(order_items) == amount`. Setiap checkout memakai voucher/diskon pada gateway Tripay berisiko ditolak dan dibatalkan otomatis.
- **Rekomendasi Perbaikan:** Sesuaikan `order_items` Tripay agar jumlahnya selalu tepat sama dengan `(int) $order->grand_total` (misal menyertakan diskon sesuai spesifikasi Tripay atau penyesuaian nominal item bila `discount_total > 0`).

---

## 4. Detail Temuan SEDANG (Disarankan Diperbaiki Bersamaan)

### `S-01` — Webhook `EXPIRED`/`FAILED` Belum Melindungi Order yang Sudah `paid`
- **Lokasi:** [TripayWebhookController.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Http/Controllers/Webhook/TripayWebhookController.php#L94-L102) & [XenditWebhookController.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Http/Controllers/Webhook/XenditWebhookController.php#L93-L101)
- **Bukti:** Cabang `EXPIRED`/`FAILED` hanya mengecek `if ($order->status === 'cancelled')`. Jika webhook `EXPIRED` datang terlambat setelah order sudah `paid`, status akan ditimpa menjadi `cancelled`/`failed` dan stok dipulihkan secara keliru.
- **Rekomendasi:** Tolak perubahan status (`return 200`) jika `$order->payment_status === 'paid'`.

### `S-02` — Validasi `is_active` Produk/Varian & Validasi `payment_method` Online di Checkout
- **Lokasi:** [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L1199-L1228), [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L1328-L1348), [Checkout.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L1571)
- **Bukti:**
  - Saat mengunci stok di baris 1328–1348, tidak diverifikasi apakah `$variant->is_active` dan `$product->is_active` masih `true` serta `channel_visibility !== 'pos_only'`.
  - `$this->payment_method` tidak divalidasi terhadap tabel `payment_methods` aktif untuk kanal online, sehingga manipulasi nilai `'tunai'` dapat melewati payment gateway.
- **Rekomendasi:** Tambahkan cek `is_active` + kanal online saat lock stok, dan validasi `$this->payment_method` terhadap `PaymentMethod` aktif online.

### `S-03` — Validasi Kuantitas `<= 0` di `PosTransactionService::completePosTransaction()`
- **Lokasi:** [PosTransactionService.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L114)
- **Bukti:** `$qty = (int) $item['quantity'];` tidak menolak nilai `<= 0`. Nilai negatif akan melewati cek stok dan malah menambah stok (`decrement('stock', -1)`).
- **Rekomendasi:** Tambahkan `if ($qty <= 0) { throw new Exception('Jumlah item harus lebih dari 0.'); }`.

---

## 5. Rencana Tindak Lanjut
Menunggu persetujuan user untuk membuat tiket handoff perbaikan ke `role-backend` (estimasi 4 file, perubahan ringkas & terisolasi, disertai automated test) sebelum masuk ke Fase 2 `role-devops` (merge ke `main` & tag `v1.1.0`).
