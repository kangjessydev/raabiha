# Handoff: Hardening Fundamental E-Commerce & POS (Audit Skeptis Tahap 2)

- **Dari:** role-inspektur   **Untuk:** role-backend   **Tanggal:** 2026-11-23
- **Status:** `closed` (Laporan: [2026-11-23-backend-hardening-transaksi-ecommerce-pos-pra-rilis.md](../reports/2026-11-23-backend-hardening-transaksi-ecommerce-pos-pra-rilis.md))
- **Rujukan Laporan:** [2026-11-23-inspektur-audit-skeptis-tahap-2-fundamental.md](../reports/2026-11-23-inspektur-audit-skeptis-tahap-2-fundamental.md)
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18))

## Ringkasan Tugas
Menutup **3 temuan Kritis** dan **1 temuan Sedang** hasil Audit Skeptis Tahap 2 pada alur dasar E-Commerce & POS.

> **Catatan Phase-Gating (`team-protocol.md`):** Total 5–6 file disentuh. Kerjakan dalam **2 sub-fase** (≤3 file per sub-fase):
> - **Fase A (E-Commerce MiniCart & Guest Cart Merge — 3 file):** `app/Livewire/MiniCart.php`, `resources/views/livewire/mini-cart.blade.php`, `app/Livewire/Auth/Login.php` (+ `Register.php` di batch kecil berikutnya atau via helper bersama).
> - **Fase B (POS Tutup Shift / Z-Report & Cancel Order Race Guard — 2 file):** `app/Livewire/PosManager.php`, `app/Livewire/Account.php`.

---

## Rincian Perbaikan Wajib

### 1. `K-01` [Kritis] Perbaiki Perhitungan Uang Laci Kasir (Tutup Shift / Z-Report)
- **Lokasi:** `app/Livewire/PosManager.php:649-684`
- **Perbaikan:**
  - Hindari *double-subtract* pada void tunai (nota yang sudah `cancelled` di shift yang sama tidak boleh dikurangkan lagi lewat `pos_void`).
  - Sertakan porsi tunai dari transaksi `split` payment (`cashflows` dengan `category = 'pos_sale'` & `payment_channel IN ('cash', 'tunai')`) ke dalam hitungan ekspektasi uang laci kasir.

### 2. `K-02` [Kritis] Hardening `MiniCart` Global (IDOR, Stok, Harga Promo, & Reset Buy Now)
- **Lokasi:** `app/Livewire/MiniCart.php:47-87` & `resources/views/livewire/mini-cart.blade.php:42`, `:93`
- **Perbaikan:**
  - Scope `incrementQuantity()`, `decrementQuantity()`, dan `removeItem()` hanya pada `$this->cart->items()->find($itemId)` (tutup IDOR).
  - Tambahkan validasi stok riil & status `is_active` pada `incrementQuantity()`.
  - Gunakan `effective_price` di `MiniCart::render()` dan `mini-cart.blade.php` agar harga promo konsisten dengan halaman Cart & Checkout.
  - Ganti `<a href="/checkout">` pada tombol `LANJUT PEMBAYARAN` menjadi aksi Livewire `proceedToCheckout()` yang mereset `session(['checkout_is_buy_now' => false])` sebelum redirect ke `/checkout`.

### 3. `K-03` [Kritis] Merge Keranjang Guest Saat Login / Register
- **Lokasi:** `app/Livewire/Auth/Login.php:43-55` & `app/Livewire/Auth/Register.php:39-50`
- **Perbaikan:**
  - Simpan `$oldSessionId = session()->getId()` sebelum `Auth::attempt()` / `Auth::login()` & `session()->regenerate()`.
  - Jika ada guest cart (`session_id = $oldSessionId`, `user_id = null`, `is_buy_now = false`), pindahkan/gabungkan itemnya ke cart milik user yang baru login/daftar (dengan clamp qty ke stok riil) lalu hapus guest cart lama.

### 4. `S-04` [Sedang] Guard Race Condition `Account::cancelOrder()` vs Webhook `PAID`
- **Lokasi:** `app/Livewire/Account.php:453-478`
- **Perbaikan:**
  - Pindahkan pencarian order ke dalam `DB::transaction()` menggunakan `->lockForUpdate()`.
  - Pastikan `status === 'pending'` dan `payment_status !== 'paid'` sebelum mengubah status menjadi `cancelled`.

---

## Kriteria Selesai
- Semua 4 item (`K-01`, `K-02`, `K-03`, `S-04`) diperbaiki dan dicatat di `CHANGELOG.md`.
- Tersedia automated feature test untuk: Z-Report tutup shift (void tunai + split payment tunai), IDOR & stock guard MiniCart, guest cart merge saat login/register, dan `Account::cancelOrder()` menolak order yang sudah `paid`.
- Seluruh test baru & test terkait lulus 100%.
