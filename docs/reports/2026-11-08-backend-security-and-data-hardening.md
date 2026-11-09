# Laporan: Backend — Hardening Keamanan & Integritas Data (Prioritas Tinggi & Rendah)

- **Tanggal:** 2026-11-08
- **Brief/Handoff Terkait:** [`docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md)
- **Mode:** Normal

## Ringkasan
Telah diselesaikan implementasi hardening keamanan dan integritas data untuk seluruh temuan berprioritas **TINGGI** (IDOR Invoice/Order Success, IDOR Pengajuan Refund, dan Manipulasi Diskon Voucher POS) serta prioritas **RENDAH** (IDOR Keranjang Belanja). Temuan prioritas sedang telah dialihkan dan didokumentasikan rapi ke dalam dokumen rencana backlog [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) sesuai arahan user. Seluruh implementasi diverifikasi menggunakan automated feature test [`tests/Feature/SecurityHardeningTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/SecurityHardeningTest.php) dan berstatus 100% lulus (hijau).

---

## Yang Dikerjakan & Bukti Konkret

### 1. Dokumentasi Backlog Prioritas Sedang
- **Berkas Baru:** [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md)
- **Isi:**
  - `BKL-001`: Hardening Otorisasi Diskon Manual Kasir POS di Sisi Server (PIN Supervisor).
  - `BKL-002`: Null-Safe Guard pada Nama Supervisor saat Pembatalan Nota (`voidOrder`) tanpa PIN.
  - `BKL-003`: Rate Limiting Percobaan Tebak PIN Supervisor POS.

### 2. Hardening IDOR & Kebocoran Informasi Pribadi Pelanggan (PII)
- **Lokasi Kode:**
  - [`routes/web.php:63-80`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/routes/web.php#L63-L80)
  - [`app/Livewire/Checkout.php:1359-1360`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L1359-L1360)
- **Perubahan:**
  - Pada rute `/invoice/{orderNumber}` dan `/order-success`, ditambahkan pengecekan otorisasi ketat:
    - Pengguna login pemilik pesanan (`$order->user_id === auth()->id()`).
    - Staf toko / admin berwenang (`hasAnyRole(['super_admin', 'owner', 'kasir', 'cs', 'marketing'])`).
    - Sesi pesanan pembeli tamu yang sah (`session('guest_order_' . $order->order_number)` atau `session('last_placed_order') === $order->order_number`).
    - Request dengan signed URL valid (`request()->hasValidSignature()`).
  - Akses ilegal tanpa izin akan langsung mengembalikan HTTP 403 Forbidden pada invoice atau mengosongkan data PII kartu pesanan pada halaman order-success.

### 3. Hardening IDOR Akses Data Finansial Pengajuan Refund
- **Lokasi Kode:** [`app/Livewire/Account.php:251-255`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Account.php#L251-L255)
- **Perubahan:**
  - Membatasi pencarian data pengajuan refund dengan filter kepemilikan pengguna:  
    `RefundRequest::with(['order', 'user'])->where('user_id', Auth::id())->find($refundId);`
  - Jika ID refund tidak dimiliki oleh pengguna aktif, modal status refund tidak akan dibuka dan data pengguna lain tidak bocor.

### 4. Hardening IDOR Keranjang Belanja
- **Lokasi Kode:** [`app/Livewire/Cart.php:141, 153, 165`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L141)
- **Perubahan:**
  - Pada method `incrementQuantity`, `decrementQuantity`, dan `removeItem`, pencarian item dibatasi hanya pada cart milik pengguna aktif:  
    `$item = $this->cart ? $this->cart->items()->find($itemId) : null;`
  - Mencegah manipulasi item belanja antar pengguna melalui penembakan ID secara acak.

### 5. Validasi & Rekalkulasi Diskon Voucher POS Sisi Server
- **Lokasi Kode:**
  - [`app/Services/PosTransactionService.php:165-195`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L165-L195)
  - [`app/Services/VoucherService.php:41`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/VoucherService.php#L41)
- **Perubahan:**
  - Mengambil model `Voucher` dengan `lockForUpdate()`.
  - Memvalidasi voucher melalui `VoucherService::validateVoucher` (status aktif, periode tanggal, kanal POS vs Online, kuota `max_uses`, dan `min_purchase`).
  - Menghitung diskon yang sah menggunakan `VoucherService::calculateDiscount`.
  - Membatasi nilai diskon akhir agar tidak pernah melebihi batas kalkulasi resmi database (`min($calculatedDiscount, $clientVoucherDiscount)`), menggagalkan manipulasi nilai voucher dari payload klien.

---

## Bukti Pengujian & Verifikasi

### 1. Eksekusi Unit & Feature Test Otomatis
Perintah:
```bash
php artisan test tests/Feature/SecurityHardeningTest.php tests/Feature/CartPageTest.php tests/Feature/ShopPageTest.php tests/Feature/ProductBuyNowTest.php
```
Hasil:
```text
Pass: 10, Fail: 0, Assertions: 29, Duration: 2.08s
- Tests\Feature\SecurityHardeningTest::test_unauthorized_user_cannot_access_invoice (PASSED)
- Tests\Feature\SecurityHardeningTest::test_account_cannot_view_other_users_refund_status (PASSED)
- Tests\Feature\SecurityHardeningTest::test_cart_item_mutation_is_scoped_to_active_cart (PASSED)
- Tests\Feature\SecurityHardeningTest::test_pos_voucher_discount_is_calculated_server_side (PASSED)
- Tests\Feature\CartPageTest (2 tests PASSED)
- Tests\Feature\ShopPageTest (2 tests PASSED)
- Tests\Feature\ProductBuyNowTest (2 tests PASSED)
```

### 2. Standar Gaya Kode (Linting Pint)
Perintah:
```bash
vendor/bin/pint --test tests/Feature/SecurityHardeningTest.php routes/web.php app/Livewire/Account.php app/Livewire/Cart.php app/Livewire/Checkout.php app/Services/PosTransactionService.php app/Services/VoucherService.php
```
Hasil:
```text
Passed: 0 code style issues.
```

---

## Checklist SOP (Peran Penulis Kode)
- [x] Solusi terkecil yang memenuhi kebutuhan tanpa over-engineering.
- [x] Tidak mengubah skema tabel database produksi.
- [x] Pola arsitektur konsisten dan menggunakan `lockForUpdate` untuk konkurensi.
- [x] Format kode dipoles dengan `vendor/bin/pint`.
- [x] Sintaks PHP lolos uji `php -l` tanpa syntax error.
- [x] Automated feature test baru dibuat untuk memastikan anti-regresi.

---

## Perubahan Kode
- **File Diubah:**
  - `routes/web.php`
  - `app/Livewire/Account.php`
  - `app/Livewire/Cart.php`
  - `app/Livewire/Checkout.php`
  - `app/Services/PosTransactionService.php`
  - `app/Services/VoucherService.php`
  - `CHANGELOG.md`
- **File Baru:**
  - `docs/backlog.md`
  - `tests/Feature/SecurityHardeningTest.php`

---

## Belum Terverifikasi
- Deployment ke server VPS produksi (menunggu konfirmasi user).

---

## Log Penyimpangan
| Langkah/Pertanyaan Tambahan | Alasan | Disetujui User? |
|---|---|---|
| Penundaan temuan prioritas sedang | Sesuai permintaan eksplisit user untuk menunda hingga ada kebutuhan dari klien | Ya, disetujui user |
| Pembuatan automated test baru | Memastikan perlindungan IDOR dan validasi voucher teruji permanen | Sesuai SOP backend |

---

## Handoff yang Dibuat
- Tiket [`docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md) diperbarui statusnya menjadi **done**.
