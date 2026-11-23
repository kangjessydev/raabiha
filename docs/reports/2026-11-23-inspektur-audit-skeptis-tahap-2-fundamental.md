# Laporan Audit Skeptis Tahap 2: Fundamental E-Commerce & POS (Pra-Rilis v1.1.0)

- **Peran:** `role-inspektur` (Audit-Only / Read-Only Mode)
- **Tanggal:** 2026-11-23
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))
- **Status Kode:** 0 baris kode diubah (100% Read-Only).
- **Lanjutan dari:** [2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md](./2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md) (Handoff `023` sudah dibuat).

---

## 1. Ringkasan Eksekutif

Audit skeptis tahap 2 menyisir alur dasar E-Commerce & POS di luar checkout inti (MiniCart global, migrasi keranjang Guest → Login/Register, pembatalan order oleh pelanggan, akses invoice/tracking, catch-all route pasca-P4, dan tutup shift/Z-Report kasir POS).

Hasil: Ditemukan **3 Temuan Kritis Tambahan** dan **1 Temuan Sedang** yang berdampak langsung pada operasional jualan harian, serta verifikasi bahwa 5 area fundamental lainnya sudah aman.

---

## 2. Temuan KRITIS Baru

### `K-01` — Perhitungan Uang Laci Kasir (Tutup Shift / Z-Report) Salah pada Void Tunai & Split Payment
- **Lokasi:** [PosManager.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L649-L684)
- **Bukti:**
  1. **Double-Subtract Void Tunai:** Baris 649–655 menghitung `$totalCashSales` dengan `->whereNotIn('status', ['cancelled'])` (nota void sudah dikeluarkan). Namun baris 658–670 kembali mengurangkan `category = 'pos_void'` ke dalam `$voidAndRefundCashOut` lalu dikurangkan lagi di baris 684.
     - *Contoh nyata:* Modal awal Rp 500.000 → jual tunai Rp 100.000 → di-void → uang riil di laci Rp 500.000. Sistem menghitung `expectedEnding = 500.000 + 0 - 100.000 = Rp 400.000` (selisih palsu +Rp 100.000!).
  2. **Split Payment Tunai Tidak Masuk Laci:** Baris 651–654 hanya mengambil `payment_method IN ('cash', 'tunai')`. Transaksi `payment_method = 'split'` yang sebagian dibayar tunai (tercatat di `cashflows` dengan `category = 'pos_sale'` & `payment_channel = 'cash'`) tidak ikut dihitung ke uang laci.
- **Dampak Bisnis:** Laporan tutup shift / Z-Report kasir akan selalu mencatat selisih kas palsu setiap kali ada void tunai atau split payment.
- **Rekomendasi:** Hitung kas masuk tunai shift berdasarkan `Cashflow` (`source = 'pos'`, `category = 'pos_sale'`, `payment_channel IN ('cash', 'tunai')`, `type = 'in'`, terkait order shift aktif / rentang shift) dan hanya kurangkan `pos_void` jika entri `pos_sale` aslinya ikut dihitung (atau hanya hitung order aktif + porsi cash split payment non-cancelled, serta hanya kurangkan `pos_void` untuk order dari shift sebelumnya).

### `K-02` — `MiniCart` Global Rentan IDOR, Bypass Stok, Harga Promo Tidak Sinkron, & Bocor Session Buy Now
- **Lokasi:** [MiniCart.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/MiniCart.php#L47-L87) & [mini-cart.blade.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/mini-cart.blade.php#L42), [:93](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/mini-cart.blade.php#L93)
- **Bukti:**
  - Komponen `<livewire:mini-cart />` aktif dirender di seluruh halaman desktop ([app.blade.php:795](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/components/layouts/app.blade.php#L795)).
  - **IDOR (`MiniCart.php:49,59,69`):** `incrementQuantity()`, `decrementQuantity()`, `removeItem()` memakai `CartItem::find($itemId)` tanpa memastikan item milik `$this->cart` → pengguna dapat memodifikasi/menghapus isi keranjang orang lain.
  - **Bypass Stok (`MiniCart.php:51`):** `incrementQuantity()` langsung memanggil `$item->increment('quantity')` tanpa cek stok riil maupun `is_active`.
  - **Harga Salah (`MiniCart.php:83` & `mini-cart.blade.php:42`):** Memakai `$item->variant->price` / `$item->product->price` (bukan `effective_price`), sehingga harga promo tidak tercermin di MiniCart.
  - **Bocor Session Buy Now (`mini-cart.blade.php:93`):** Tombol `LANJUT PEMBAYARAN` langsung menuju `<a href="/checkout">` tanpa mereset `session('checkout_is_buy_now', false)`. Jika pelanggan sebelumnya batal "Beli Sekarang", tombol ini akan membuka item Beli Sekarang lama alih-alih isi keranjang.
- **Rekomendasi:** Scope semua aksi `MiniCart` ke `$this->cart->items()->find($itemId)`, tambahkan guard stok & `is_active`, pakai `effective_price`, dan ganti link checkout menjadi action Livewire yang mereset `checkout_is_buy_now = false` sebelum redirect ke `/checkout`.

### `K-03` — Keranjang Belanja Guest Hilang Total Saat Login atau Register
- **Lokasi:** [Login.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Auth/Login.php#L43-L55) & [Register.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Auth/Register.php#L39-L50)
- **Bukti:**
  - Guest menambah produk ke keranjang (tersimpan di `carts` dengan `session_id = <old_id>, user_id = null`).
  - Saat login, `Login.php:44` memanggil `session()->regenerate()` (mengganti session ID) tanpa memindahkan/menggabungkan *guest cart* ke `user_id` yang baru login. Begitu pula di `Register.php:48`.
- **Dampak Bisnis:** Pelanggan baru yang sudah pilih barang lalu login/daftar untuk checkout mendapati keranjangnya tiba-tiba kosong (konversi belanja hilang).
- **Rekomendasi:** Tangkap `$oldSessionId = session()->getId()` sebelum `Auth::attempt()` / `Auth::login()` + `session()->regenerate()`, lalu gabungkan (*merge*) item dari guest cart (`session_id = $oldSessionId, user_id = null`) ke cart milik `auth()->id()` (dengan tetap menjaga batas stok riil).

---

## 3. Temuan SEDANG Baru

### `S-04` — Race Condition `Account::cancelOrder()` vs Webhook `PAID`
- **Lokasi:** [Account.php](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Account.php#L453-L478)
- **Bukti:** `cancelOrder()` mencari order di luar `DB::transaction()` tanpa `lockForUpdate()` dan tidak mengecek `payment_status !== 'paid'`. Jika callback `PAID` masuk bersamaan dengan klik "Batalkan Pesanan", status order berisiko tertimpa `cancelled`.
- **Rekomendasi:** Pindahkan query order ke dalam `DB::transaction()` dengan `->lockForUpdate()`, pastikan `status === 'pending'` dan `payment_status !== 'paid'`.

---

## 4. Area Fundamental Lain yang Sudah Terverifikasi AMAN
- **Catch-all Route & Tabel `sales_pages` (`routes/web.php:234`):** Aman. Model `SalesPage` memakai tabel `sales_pages` (masih ada); yang dihapus di P4 Fase 1 adalah tabel duplikat tak terpakai `salespages`.
- **Otorisasi Order Detail, Order Track, Order Success, & Invoice (`OrderDetail.php:17-19`, `OrderTrack.php:18-20`, `routes/web.php:95-155`):** Aman. Seluruhnya memvalidasi kepemilikan `user_id`, role staf, session guest order, atau signed URL.
- **Public Tracking (`PublicOrderTrack.php`):** Aman. Tidak membocorkan PII dari database.
- **CSRF Exemption Webhooks (`bootstrap/app.php:17-19`):** Aman (`webhook/*` sudah dikecualikan).
- **POS Route Protection (`routes/web.php:41`):** Aman (dilindungi `EnsurePosAuthenticated`).
- **Cart.php Ownership Scope (`Cart.php:278`):** Aman (memakai `$this->cart->items()->find($itemId)`).
