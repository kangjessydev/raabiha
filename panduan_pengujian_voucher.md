# 📋 Checklist & Panduan Pengujian Sistem (Voucher & Laporan Penjualan)

Dokumen ini berisi daftar pengujian (*QA Checklist*) menyeluruh yang dapat Anda centang satu per satu untuk memastikan seluruh fungsi voucher dan filter laporan penjualan di Admin Panel berjalan sesuai ekspektasi.

---

## 1. Kanal Penggunaan Voucher (`usable_channel`)

- [ ] **1.1 Voucher Hanya Toko Online (Web)**
  * **Pengaturan Form:** `usable_channel = Hanya Toko Online (Web)` (`online_only`)
  * **Langkah Pengujian:**
    1. Buka E-Commerce (Web) -> Masukkan kode voucher di Keranjang / Checkout -> **Ekspektasi:** Berhasil diterapkan dan memotong harga.
    2. Cek Modal "Pilih Voucher" di Web -> **Ekspektasi:** Voucher muncul di daftar.
    3. Buka Kasir (POS) -> Cek daftar voucher -> **Ekspektasi:** Voucher TIDAK muncul di daftar POS.
    4. *(Opsional)* Jika dipaksa submit di POS -> **Ekspektasi:** Sistem menolak transaksi dengan pesan error.

- [ ] **1.2 Voucher Hanya Kasir / Toko Fisik (POS)**
  * **Pengaturan Form:** `usable_channel = Hanya Kasir / Toko Fisik (POS)` (`pos_only`)
  * **Langkah Pengujian:**
    1. Buka E-Commerce (Web) -> Masukkan kode voucher di Keranjang / Checkout -> **Ekspektasi:** Ditolak dengan pesan *"Kode voucher ini hanya berlaku untuk transaksi di Kasir / POS."*
    2. Cek Modal "Pilih Voucher" di Web -> **Ekspektasi:** Voucher TIDAK muncul.
    3. Cek Banner / Badge Promo di Halaman Produk / Header Web -> **Ekspektasi:** Label promo voucher ini TIDAK muncul.
    4. Buka Kasir (POS) -> Cek daftar voucher -> **Ekspektasi:** Voucher muncul dan berhasil memotong total transaksi POS.

- [ ] **1.3 Voucher Keduanya (Web & POS)**
  * **Pengaturan Form:** `usable_channel = Keduanya (Web & POS)` (`both`)
  * **Langkah Pengujian:**
    1. Buka E-Commerce (Web) -> **Ekspektasi:** Voucher muncul di modal dan berhasil digunakan.
    2. Buka Kasir (POS) -> **Ekspektasi:** Voucher muncul di modal kasir dan berhasil digunakan.

---

## 2. Tipe & Jumlah Diskon (`discount_type` & `discount_amount`)

- [ ] **2.1 Diskon Nominal Rp (`fixed`)**
  * **Pengaturan Form:** `discount_type = Nominal (Rp)`, `discount_amount = 20000`
  * **Langkah Pengujian:** Gunakan voucher pada transaksi Rp 100.000.
  * **Ekspektasi:** Potongan harga tepat sebesar **Rp 20.000** (Total jadi Rp 80.000).

- [ ] **2.2 Diskon Persentase % (`percent`)**
  * **Pengaturan Form:** `discount_type = Persentase (%)`, `discount_amount = 10`
  * **Langkah Pengujian:** Gunakan voucher pada transaksi Rp 150.000.
  * **Ekspektasi:** Potongan harga sebesar **10% = Rp 15.000** (Total jadi Rp 135.000).

---

## 3. Maksimal Diskon Persentase (`max_discount`)

- [ ] **3.1 Batas Maksimal Diskon Persentase Terpenuhi**
  * **Pengaturan Form:** `discount_type = percent`, `discount_amount = 20%`, `max_discount = 30000`
  * **Langkah Pengujian:** Belanja senilai Rp 200.000 (20% dari 200rb = 40rb, melebihi batas 30rb).
  * **Ekspektasi:** Potongan diskon dibatasi maksimal **Rp 30.000** (bukan Rp 40.000).

---

## 4. Voucher Potongan Ongkir (`is_shipping_voucher`)

- [ ] **4.1 Voucher Ongkir di Keranjang (Cart)**
  * **Pengaturan Form:** `is_shipping_voucher = True`
  * **Langkah Pengujian:** Masukkan kode voucher di Halaman Keranjang (`/cart`).
  * **Ekspektasi:** Ditolak dengan pesan *"Voucher ongkir hanya dapat digunakan di halaman Checkout."*

- [ ] **4.2 Voucher Ongkir di Checkout**
  * **Langkah Pengujian:** Pilih voucher ongkir di Halaman Checkout (`/checkout`).
  * **Ekspektasi:** Diskon memotong **biaya pengiriman (ongkir)**, bukan memotong harga subtotal produk.

---

## 5. Minimal Belanja & Minimal Item (`min_purchase` & `min_items`)

- [ ] **5.1 Minimal Belanja Nominal (`min_purchase`)**
  * **Pengaturan Form:** `min_purchase = 100000`
  * **Langkah Pengujian:** 
    - Belanja Rp 80.000 -> **Ekspektasi:** Ditolak / ditandai tidak memenuhi syarat minimum belanja.
    - Tambah barang hingga Rp 100.000+ -> **Ekspektasi:** Voucher berhasil diterapkan.

- [ ] **5.2 Minimal Jumlah Item (`min_items`)**
  * **Pengaturan Form:** `min_items = 3`
  * **Langkah Pengujian:** 
    - Belanja 2 item barang -> **Ekspektasi:** Ditolak dengan pesan minimal 3 item.
    - Tambah qty menjadi 3 item -> **Ekspektasi:** Voucher berhasil diterapkan.

---

## 6. Pembatasan Pengguna & Reseller (`specific_users` & `exclude_resellers`)

- [ ] **6.1 Spesifik Pengguna Email (`specific_users`)**
  * **Pengaturan Form:** `specific_users = [userA@gmail.com]`
  * **Langkah Pengujian:**
    - Login akun `userB@gmail.com` -> **Ekspektasi:** Voucher tidak muncul di modal & ditolak saat klaim manual.
    - Login akun `userA@gmail.com` -> **Ekspektasi:** Voucher muncul dan berhasil digunakan.

- [ ] **6.2 Larang Reseller (`exclude_resellers`)**
  * **Pengaturan Form:** `exclude_resellers = True`
  * **Langkah Pengujian:**
    - Login akun ber-role **Reseller** -> **Ekspektasi:** Ditolak dengan pesan *"Maaf, voucher ini tidak berlaku untuk mitra Reseller."*
    - Login akun Customer Biasa -> **Ekspektasi:** Voucher berhasil digunakan.

---

## 7. Penggabungan Voucher / Stackable (`is_stackable`)

- [ ] **7.1 Penumpukan Multi-Voucher di Checkout**
  * **Pengaturan Form:** Buat 2 voucher (misal: 1 Voucher Produk + 1 Voucher Ongkir) yang keduanya di-setting `is_stackable = True`.
  * **Langkah Pengujian:** Pilih kedua voucher tersebut di Halaman Checkout.
  * **Ekspektasi:** Kedua voucher terpasang bersamaan dan potongan akumulatif dihitung dengan benar.

---

## 8. Status & Kuota Penggunaan (`is_active`, `max_uses`, `max_uses_per_user`)

- [ ] **8.1 Status Non-Aktif (`is_active = False`)**
  * **Langkah Pengujian:** Nonaktifkan voucher di Admin -> Coba gunakan kode voucher.
  * **Ekspektasi:** Ditolak dengan pesan *"Kode voucher tidak valid atau sudah kadaluarsa."*

- [ ] **8.2 Batas Kuota Keseluruhan (`max_uses`)**
  * **Pengaturan Form:** `max_uses = 1`, `used_count = 1`
  * **Langkah Pengujian:** Coba gunakan voucher yang kuotanya sudah habis.
  * **Ekspektasi:** Ditolak dengan pesan *"Kode voucher sudah melewati batas penggunaan."*

- [ ] **8.3 Batas Pemakaian Per Akun (`max_uses_per_user`)**
  * **Pengaturan Form:** `max_uses_per_user = 1`
  * **Langkah Pengujian:** Gunakan voucher 1 kali hingga checkout sukses. Setelah order selesai, coba gunakan lagi voucher yang sama untuk akun yang sama.
  * **Ekspektasi:** Ditolak dengan pesan *"Anda sudah melebihi batas penggunaan untuk voucher ini (1 kali)."*

---

## 9. Masa Berlaku Mulai & Kedaluwarsa (`starts_at` & `expires_at`)

- [ ] **9.1 Voucher Belum Mulai (`starts_at`)**
  * **Pengaturan Form:** `starts_at = Besok`
  * **Langkah Pengujian:** Coba gunakan kode voucher hari ini.
  * **Ekspektasi:** Ditolak (belum berlaku).

- [ ] **9.2 Voucher Sudah Kedaluwarsa (`expires_at`)**
  * **Pengaturan Form:** `expires_at = Kemarin`
  * **Langkah Pengujian:** Coba gunakan kode voucher hari ini.
  * **Ekspektasi:** Ditolak dengan pesan kadaluarsa.

---

## 10. Pengujian Filter Laporan Penjualan (`LaporanPenjualan`)

- [ ] **10.1 Filter Omnichannel (Semua Channel)**
  * **Pengaturan Filter:** Kanal Penjualan = `Semua Channel (Omnichannel)`
  * **Langkah Pengujian:** Buka Admin -> Laporan Penjualan & Performa Produk -> Pilih rentang waktu.
  * **Ekspektasi:** Seluruh transaksi (baik transaksi E-Commerce Web maupun POS Kasir & Transaksi Offline) muncul di tabel rincian transaksi dan terhitung di widget produk terlaris/lambat terjual.

- [ ] **10.2 Filter POS Toko Fisik**
  * **Pengaturan Filter:** Kanal Penjualan = `POS Toko Fisik`
  * **Langkah Pengujian:** Pilih filter POS Toko Fisik.
  * **Ekspektasi:** 
    1. Hanya transaksi dari Kasir (POS) & transaksi Toko Offline yang muncul.
    2. Transaksi Web E-Commerce TIDAK muncul.
    3. Badge kolom `Channel` pada tabel berwarna **Hijau (`POS Kasir`)**.
    4. Widget Produk Terlaris & Lambat Terjual memperhitungkan omset & item dari kanal POS.

- [ ] **10.3 Filter E-Commerce Website**
  * **Pengaturan Filter:** Kanal Penjualan = `E-Commerce Website`
  * **Langkah Pengujian:** Pilih filter E-Commerce Website.
  * **Ekspektasi:** 
    1. Hanya transaksi dari Website E-Commerce yang muncul.
    2. Transaksi Kasir POS & Toko Offline TIDAK muncul.
    3. Badge kolom `Channel` pada tabel berwarna **Biru (`E-Commerce`)**.
    4. Widget Produk Terlaris & Lambat Terjual memperhitungkan omset & item khusus transaksi Web.
