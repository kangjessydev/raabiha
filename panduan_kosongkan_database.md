# Panduan Mengosongkan Database (Pembersihan Data Uji Coba)

Dokumen ini berisi daftar tabel dan kueri SQL untuk membersihkan data uji coba (*cleanup test data*) sebelum website dirilis secara resmi (*Go Live*), dengan tetap mempertahankan data master seperti produk, kategori, akun admin, akun reseller, dan pengaturan integrasi.

---

## 1. Daftar Tabel Yang Dikosongkan vs Dipertahankan

### ⚠️ Tabel yang BISA Dikosongkan (`TRUNCATE`)
Tabel-tabel berikut hanya berisi riwayat pengujian transaksi, log, dan sesi:
* `orders` (Data transaksi pesanan)
* `order_items` (Rincian barang di setiap pesanan)
* `order_requests` (Permintaan pesanan kustom)
* `refund_requests` (Pengajuan pengembalian dana)
* `stock_logs` (Catatan keluar-masuk stok barang)
* `carts` & `cart_items` (Keranjang belanja)
* `inquiries` (Pesan masuk dari form kontak)
* `cashflows` (Catatan laporan arus kas keuangan)
* `wishlists` (Daftar favorit pelanggan)
* `pageviews` (Statistik log kunjungan uji coba)
* `sessions` (Sesi login browser pengujian)
* `cache` & `cache_locks` (Cache sistem)
* `product_reviews` *(Opsional: jika ulasan saat ini hanyalah uji coba)*

---

### 🔒 Tabel yang HARUS DIPELIHARA (Jangan Dihapus)
Tabel-tabel ini berisi data master toko, akun, dan pengaturan konfigurasi:
* `users` (Akun Admin, Reseller, Customer)
* `roles`, `permissions`, `model_has_roles`, dll. (Sistem hak akses)
* `products`, `product_variants`, `attributes`, `attribute_options` (Katalog produk & varian)
* `categories` (Kategori produk)
* `site_settings` (Setting kunci API Xendit, RajaOngkir, dll.)
* `payment_methods` & `shipping_methods` (Jalur pembayaran & kurir aktif)
* `vouchers` (Daftar voucher promosi)
* `media` (Semua file media / gambar produk)
* `posts`, `post_categories` (Artikel blog toko)
* `migrations` (Log struktur tabel Laravel)

---

## 2. Kueri SQL Pembersih (Siap Dijalankan)

> [!CAUTION]
> **Selalu lakukan BACKUP / EXPORT database Anda terlebih dahulu** sebelum menjalankan perintah SQL ini untuk menghindari kehilangan data yang tidak disengaja.

Jalankan perintah SQL berikut di MySQL (lewat **phpMyAdmin**, **DBeaver**, **Navicat**, atau **Terminal MySQL**):

```sql
-- 1. Matikan pengecekan relasi database sementara agar proses hapus sukses
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Kosongkan tabel data transaksi & pengujian dengan DELETE FROM
DELETE FROM `order_items`;
ALTER TABLE `order_items` AUTO_INCREMENT = 1;

DELETE FROM `orders`;
ALTER TABLE `orders` AUTO_INCREMENT = 1;

DELETE FROM `order_requests`;
ALTER TABLE `order_requests` AUTO_INCREMENT = 1;

DELETE FROM `refund_requests`;
ALTER TABLE `refund_requests` AUTO_INCREMENT = 1;

DELETE FROM `stock_logs`;
ALTER TABLE `stock_logs` AUTO_INCREMENT = 1;

DELETE FROM `cart_items`;
ALTER TABLE `cart_items` AUTO_INCREMENT = 1;

DELETE FROM `carts`;
ALTER TABLE `carts` AUTO_INCREMENT = 1;

DELETE FROM `inquiries`;
ALTER TABLE `inquiries` AUTO_INCREMENT = 1;

DELETE FROM `cashflows`;
ALTER TABLE `cashflows` AUTO_INCREMENT = 1;

DELETE FROM `wishlists`;
ALTER TABLE `wishlists` AUTO_INCREMENT = 1;

DELETE FROM `pageviews`;
ALTER TABLE `pageviews` AUTO_INCREMENT = 1;

DELETE FROM `sessions`;

DELETE FROM `cache`;
DELETE FROM `cache_locks`;

-- (Opsional: Hapus tanda '--' di baris bawah jika ingin mengosongkan ulasan produk uji coba)
-- DELETE FROM `product_reviews`;
-- ALTER TABLE `product_reviews` AUTO_INCREMENT = 1;

-- 3. Hidupkan kembali pengecekan relasi database
SET FOREIGN_KEY_CHECKS = 1;
```

### Keuntungan menggunakan kueri kombinasi ini:
* **Kompatibilitas Penuh**: Menghindari error relasi kunci asing (*Foreign Key Constraints*) yang sering memblokir perintah `TRUNCATE` di phpMyAdmin.
* **Auto-Increment Reset**: Dengan perintah `ALTER TABLE ... AUTO_INCREMENT = 1`, ID transaksi baru berikutnya akan otomatis dimulai kembali secara rapi dari angka **1**.
