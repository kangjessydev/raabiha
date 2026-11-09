# Laporan: Inspektur — Audit Keamanan & Integritas Data Menyeluruh

- **Tanggal:** 2026-11-08
- **Brief/Handoff Terkait:** Diagnosa Keamanan & Masalah Data (Permintaan Langsung User)
- **Mode:** Normal (Audit-Only / Read-Only Mode)

## Ringkasan
Audit menyeluruh telah dilakukan pada seluruh modul utama: Gateway Pembayaran & Webhook, Autentikasi & Otorisasi Subdomain POS & Admin, Transaksi Checkout & Keranjang Belanja, Terminal POS Kasir, serta Manajemen Akun Pelanggan.
Ditemukan **7 temuan keamanan dan integritas data**, yang terdiri dari **3 temuan berprioritas TINGGI** (IDOR data pribadi invoice, IDOR data finansial refund, dan manipulasi nilai diskon voucher di POS), **3 temuan berprioritas SEDANG** (otorisasi diskon manual kasir hanya di frontend, potensi crash fatal 500 saat void order tanpa PIN, dan tidak adanya rate limit PIN supervisor), serta **1 temuan berprioritas RENDAH** (IDOR cart item).
Seluruh temuan telah didokumentasikan dengan referensi `file:baris` dan kutipan kode nyata. Sesuai batasan wewenang `role-inspektur`, **tidak ada kode yang diubah**. Rekomendasi perbaikan disalurkan melalui tiket handoff [`docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md).

---

## Yang Dikerjakan & Rincian Temuan

### 1. [KEAMANAN - TINGGI] IDOR & Kebocoran Informasi Pribadi Pelanggan (PII) di Rute Invoice & Order Success
- **Lokasi Kode:**
  - [`routes/web.php:63-72`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/routes/web.php#L63-L72)
  - [`routes/web.php:74-80`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/routes/web.php#L74-L80)
  - [`resources/views/invoice.blade.php:16`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/invoice.blade.php#L16)
  - [`resources/views/components/order/invoice-card.blade.php:89-99`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/components/order/invoice-card.blade.php#L89-L99)
- **Bukti:**
  ```php
  Route::get('/invoice/{orderNumber}', function ($orderNumber) {
      $order = \App\Models\Order::with('items.product')
          ->where('order_number', $orderNumber)
          ->firstOrFail();
      return view('invoice', compact('order'));
  })->name('invoice');
  ```
- **Masalah & Dampak:**
  Nomor pesanan menggunakan pola yang mudah ditebak (`ORD-YYYYMMDD-XXXXX` dengan 5 karakter hex di `app/Livewire/Checkout.php:1258`). Siapa pun dapat mengakses URL `/invoice/ORD-...` atau `/order-success?order=ORD-...` tanpa autentikasi atau validasi session token. Halaman ini membocorkan nama lengkap, nomor telepon, alamat lengkap penerima (RT/RW, desa/kelurahan, kecamatan, kota, provinsi, kode pos), rincian belanja, hingga URL pembayaran.
- **Rekomendasi:**
  Terapkan proteksi otorisasi kepemilikan pesanan:
  - Jika user login: `auth()->check() && (auth()->id() === $order->user_id || auth()->user()->hasRole('super_admin'))`.
  - Jika guest checkout: gunakan signed URL (`URL::signedRoute('invoice', ['orderNumber' => ...])`) atau simpan token sesi pesanan pada session pembeli.

---

### 2. [KEAMANAN - TINGGI] IDOR Akses Data Finansial & Pengajuan Refund di `Account::openRefundStatus`
- **Lokasi Kode:**
  - [`app/Livewire/Account.php:251-255`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Account.php#L251-L255)
- **Bukti:**
  ```php
  public function openRefundStatus($refundId)
  {
      $refund = \App\Models\RefundRequest::with(['order', 'user'])->find($refundId);
      if (!$refund) return;
  ```
- **Masalah & Dampak:**
  Pengambilan data refund tidak menyertakan filter `where('user_id', Auth::id())`. Setiap pengguna yang telah login dapat memanggil method Livewire ini dengan sembarang ID integer (`1, 2, 3...`) untuk melihat status refund orang lain, nominal uang, nomor rekening, nama bank, serta catatan internal admin penolak/penyetuju refund.
- **Rekomendasi:**
  Ubah query menjadi:
  `$refund = \App\Models\RefundRequest::with(['order', 'user'])->where('user_id', Auth::id())->find($refundId);`

---

### 3. [KEAMANAN & DATA - TINGGI] Manipulasi Nilai Diskon Voucher POS Tanpa Rekalkulasi Sisi Server
- **Lokasi Kode:**
  - [`app/Services/PosTransactionService.php:168, 176-188`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L168)
- **Bukti:**
  ```php
  $voucherDiscount = (float) ($data['voucher_discount'] ?? 0);
  ...
  $discount = $itemDiscountsTotal + $manualDiscount + $voucherDiscount;
  $grandTotal = max(0, $subtotal - $discount);
  ...
  $voucherId = $data['voucher_id'] ?? null;
  if ($voucherId) {
      $voucher = \App\Models\Voucher::find($voucherId);
      if ($voucher) {
          if ($voucher->usable_channel === 'online_only') {
              throw new Exception("Voucher {$voucher->name} hanya berlaku untuk transaksi Toko Online (Web).");
          }
          if ($voucher->max_uses && $voucher->used_count >= $voucher->max_uses) {
              throw new Exception("Voucher {$voucher->name} sudah mencapai batas maksimum kuota penggunaan.");
          }
          $voucher->increment('used_count');
      }
  }
  ```
- **Masalah & Dampak:**
  Berbeda dengan modul Checkout Web yang memanggil `VoucherService::verifyAndCalculate` secara defensif dengan `lockForUpdate`, modul POS menerima `$voucherDiscount` mentah dari payload klien. Server tidak memverifikasi apakah voucher aktif, tanggal kedaluwarsa, batas minimal belanja (`min_spend`), maupun perhitungan diskon aslinya (`percentage` atau `fixed`). Kasir/klien dapat memotong harga transaksi hingga Rp 0 dengan nilai voucher rekayasa.
- **Rekomendasi:**
  Validasi dan hitung ulang nilai diskon voucher di sisi server melalui `VoucherService` di `PosTransactionService::createOrder()`.

---

### 4. [KEAMANAN - SEDANG] Otorisasi Batas Diskon Manual Kasir POS Hanya di Frontend (Client-Side Only)
- **Lokasi Kode:**
  - [`resources/views/livewire/pos-manager.blade.php:7798-7800`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/pos-manager.blade.php#L7798-L7800)
  - [`app/Services/PosTransactionService.php:167`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L167)
- **Bukti:**
  ```javascript
  manualDiscountMaxPercentWithoutPin: {{ (float) (\App\Models\SiteSetting::where('key', 'pos_manual_discount_max_percent_without_pin')->value('value') ?? 20) }},
  manualDiscountMaxRpWithoutPin: {{ (float) (\App\Models\SiteSetting::where('key', 'pos_manual_discount_max_rp_without_pin')->value('value') ?? 50000) }},
  requirePinForManualDiscount: ...
  ```
  Tetapi di `PosTransactionService.php`:
  ```php
  $manualDiscount = (float) ($data['manual_discount'] ?? $data['discount'] ?? 0);
  ```
- **Masalah & Dampak:**
  Jika kasir membypass UI Alpine.js (misalnya mengubah variabel JS di console atau memodifikasi websocket request), kasir dapat memberikan diskon manual berapapun tanpa otorisasi PIN supervisor karena backend tidak memiliki validasi guard.
- **Rekomendasi:**
  Tambahkan pengecekan threshold `pos_manual_discount_max_percent_without_pin` dan `pos_manual_discount_max_rp_without_pin` di `PosTransactionService.php`. Jika diskon manual melampaui batas, wajib memverifikasi parameter `supervisor_id` dan `supervisor_pin`.

---

### 5. [INTEGRITAS DATA - SEDANG] Potensi Fatal Error (Crash 500) pada Pembatalan Nota POS (`voidOrder`) Jika Mode Bebas PIN Aktif
- **Lokasi Kode:**
  - [`app/Livewire/PosManager.php:1206-1212, 1257, 1278, 1292`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1257)
- **Bukti:**
  ```php
  $supervisor = null;
  if ($requirePin) {
      $supervisor = $this->validateSupervisorPin($supervisorId, $supervisorPin, 'notify');
      if (!$supervisor) return;
  }
  ...
  'notes' => 'Restock Void Nota POS #' . $order->order_number . ' (Disetujui Supervisor: ' . $supervisor->name . ')',
  ...
  'description' => 'Void Transaksi POS #' . $order->order_number . ' ... (Disetujui Supervisor: ' . $supervisor->name . ')',
  ```
- **Masalah & Dampak:**
  Jika setting `pos_require_pin_for_void` diset `false`, variabel `$supervisor` bernilai `null`. Saat baris 1257, 1278, dan 1292 dieksekusi, sistem mengalami fatal error: `Attempt to read property "name" on null`, menggagalkan rollback stok dan pembatalan nota.
- **Rekomendasi:**
  Gunakan null-safe operator atau fallback: `($supervisor?->name ?? Auth::user()->name)`.

---

### 6. [KEAMANAN - SEDANG] Tidak Ada Rate Limiter pada Verifikasi PIN Supervisor POS
- **Lokasi Kode:**
  - [`app/Livewire/PosManager.php:349, 563, 1208`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L349)
- **Bukti:**
  ```php
  if (!$supervisor || !$isSup || empty($supervisor->pos_pin) || !\Illuminate\Support\Facades\Hash::check($pin, $supervisor->pos_pin)) {
      $this->dispatch('notify', ['type' => 'error', 'message' => 'PIN Supervisor tidak valid.']);
      return false;
  }
  ```
- **Masalah & Dampak:**
  PIN supervisor umumnya berupa 4 hingga 6 digit angka. Tanpa pembatasan percobaan (`RateLimiter`), kasir yang nakal dapat melakukan brute-force otomatis ribuan kombinasi dalam beberapa menit tanpa terblokir.
- **Rekomendasi:**
  Bungkus verifikasi PIN dengan `RateLimiter::attempt()` per kasir/IP, dan kunci otorisasi selama 10-15 menit jika gagal 5 kali berturut-turut.

---

### 7. [KEAMANAN - RENDAH] IDOR pada Manipulasi Kuantitas & Hapus Item Keranjang
- **Lokasi Kode:**
  - [`app/Livewire/Cart.php:141, 151, 161`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L141)
- **Bukti:**
  ```php
  public function incrementQuantity($itemId) {
      $item = \App\Models\CartItem::find($itemId);
  ...
  public function decrementQuantity($itemId) {
      $item = \App\Models\CartItem::find($itemId);
  ...
  public function removeItem($itemId) {
      $item = \App\Models\CartItem::find($itemId);
  ```
- **Masalah & Dampak:**
  Pengambilan `CartItem` dilakukan langsung via ID global tanpa memastikan bahwa item tersebut milik cart user aktif (`$this->cart->items()->find($itemId)`). Pengguna dapat memanipulasi atau menghapus item belanja milik pengguna lain dengan menebak ID item.
- **Rekomendasi:**
  Ganti pemanggilan menjadi `$item = $this->cart ? $this->cart->items()->find($itemId) : null;`.

---

## Matriks Sinkronisasi Alur Data

| Alur / Komponen | Endpoint / Layer Backend | Tabel & Kolom Database | Status Alur Data & Keamanan |
| :--- | :--- | :--- | :--- |
| **Checkout Online** | `Checkout.php` | `orders`, `order_items`, `stock_logs` | **AMAN & SOLID** (Atomic Lock, `lockForUpdate`, validasi stok & voucher terpusat) |
| **Webhook Tripay** | `TripayWebhookController` | `orders`, `stock_logs` | **AMAN & SOLID** (HMAC-SHA256, `hash_equals`, idempoten, `lockForUpdate`) |
| **Webhook Xendit** | `XenditWebhookController` | `orders`, `stock_logs` | **AMAN & SOLID** (`x-callback-token`, `hash_equals`, idempoten, `lockForUpdate`) |
| **Invoice Publik** | `/invoice/{orderNumber}` | `orders.shipping_address` | **RENTAN IDOR** (Kebocoran PII publik tanpa otentikasi) |
| **Status Refund Akun** | `Account::openRefundStatus` | `refund_requests` | **RENTAN IDOR** (Data rekening & alasan refund terbuka tanpa scope user) |
| **Keranjang Belanja** | `Cart::increment/decrement/remove` | `cart_items` | **RENTAN IDOR** (Item cart diubah tanpa verifikasi cart_id pemilik) |
| **Diskon Voucher POS** | `PosTransactionService::createOrder` | `vouchers`, `orders` | **RENTAN MANIPULASI** (Diskon diterima mentah dari client tanpa rekalkulasi) |
| **Diskon Manual POS** | `PosTransactionService::createOrder` | `orders.discount_total` | **RENTAN FRAUD** (Threshold diskon manual hanya dicek di frontend) |
| **Void Nota POS** | `PosManager::voidOrder` | `orders`, `cashflows`, `stock_logs` | **RAWAN CRASH 500** (Null pointer jika PIN dinonaktifkan) |

---

## Checklist SOP (Auditor / Inspektur)
- [x] **Hanya membaca (Read-Only):** Tidak ada berkas kode program yang diubah di repositori.
- [x] **Berbasis Bukti Nyata:** Setiap temuan menyertakan lokasi file, baris, dan kutipan kode nyata.
- [x] **Handoff Tiket Dibuat:** Tiket perbaikan telah disusun secara lengkap di `docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`.
- [x] **Kepatuhan Protokol Tim:** Laporan tersimpan resmi di `docs/reports/`.

---

## Handoff yang Dibuat
- [`docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md) (Ditujukan ke `role-backend`).
