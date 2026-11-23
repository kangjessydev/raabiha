# Backlog Perbaikan & Rencana Fitur

Dokumen ini mencatat daftar temuan, rencana peningkatan, dan perbaikan tertunda yang menunggu permintaan atau konfirmasi lebih lanjut dari pihak klien/bisnis sebelum diimplementasikan.

---

## 1. Modul Point of Sale (POS) & Terminal Kasir

### BKL-001: Hardening Otorisasi Diskon Manual Kasir di Sisi Server
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #4.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** *Pending Client Request* (Menunggu instruksi klien).
- **Deskripsi:**
  Saat ini, batasan diskon manual kasir (`pos_manual_discount_max_percent_without_pin` sebesar 20% dan `pos_manual_discount_max_rp_without_pin` sebesar Rp 50.000) baru divalidasi pada antarmuka pengguna (`resources/views/livewire/pos-manager.blade.php:7798-7800`). Di backend ([`app/Services/PosTransactionService.php:167`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L167)), nilai diskon manual diterima langsung dari input klien tanpa validasi threshold toko atau kewajiban verifikasi PIN supervisor.
- **Rencana Tindakan:**
  Tambahkan middleware/service guard di `PosTransactionService::createOrder()` untuk memeriksa persentase dan nominal diskon manual terhadap pengaturan toko, serta mewajibkan `supervisor_id` dan `supervisor_pin` yang valid jika melewati ambang batas tanpa PIN.

---

### BKL-002: Null-Safe Guard pada Nama Supervisor saat Pembatalan Nota (`voidOrder`)
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #5.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** *Pending Client Request* (Menunggu instruksi klien).
- **Deskripsi:**
  Pada [`app/Livewire/PosManager.php:1257, 1278, 1292`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1257), string deskripsi dan log stok memanggil `$supervisor->name`. Jika pengaturan toko `pos_require_pin_for_void` disetel menjadi `false` (kasir boleh void tanpa supervisor), variabel `$supervisor` bernilai `null` dan memicu error `Attempt to read property "name" on null` yang menggagalkan proses void nota.
- **Rencana Tindakan:**
  Ubah akses properti menjadi null-safe: `($supervisor?->name ?? Auth::user()->name)`.

---

### BKL-003: Rate Limiting Percobaan Tebak PIN Supervisor POS
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #6.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** *Pending Client Request* (Menunggu instruksi klien).
- **Deskripsi:**
  Pengecekan PIN supervisor (4-6 digit) pada method pengambilalihan sesi paksa (`forceTakeoverWithSupervisor`), kas kecil (`addPettyCash`), buka laci (`openManualDrawer`), dan void nota belum dilengkapi throttling/rate limiter. Kasir dapat melakukan brute-force PIN tanpa ada batas percobaan.
- **Rencana Tindakan:**
  Pasang `Illuminate\Support\Facades\RateLimiter::attempt()` pada fungsi-fungsi verifikasi PIN supervisor dengan batas maksimal 5 kali percobaan salah sebelum dilakukan penguncian sementara (lockout) 10-15 menit.

---

## 2. Modul Admin Dashboard (Katalog, Stok & Varian)

### BKL-004: Sinkronisasi Nilai Kolom Stok & Sortir Tabel Produk
- **Sumber Temuan:** Laporan Audit Logika ([`docs/reports/2026-11-09-admin-logic-audit.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-09-admin-logic-audit.md)) - Temuan #1.1.
- **Tingkat Prioritas:** Tinggi (High).
- **Status Riil Saat Ini:** 🟡 **Sebagian / Baru Tampilan.** Teks tampilan kolom stok sudah menjumlahkan varian (`10 pcs (varian)`), tetapi kolom mentah `products.stock` di database masih bernilai `0`. Sorting tabel `->sortable()` masih menggunakan SQL `ORDER BY products.stock` sehingga produk bervarian tetap jatuh ke paling bawah pada sort DESC.
- **Rencana Tindakan:**
  Tambahkan observer atau query hook agar `products.stock` otomatis merefleksikan total penjumlahan variannya saat varian ditambah/diubah/dihapus, atau sesuaikan query sorting tabel dengan subquery sum varian.

### BKL-005: Penyelarasan Kriteria Stok Habis (Widget vs Manajemen Stok)
- **Sumber Temuan:** Laporan Audit Logika - Temuan #1.2.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.** Di widget atas masih menghitung jika *semua varian habis*, sedangkan di Manajemen Stok menghitung jika *ada salah satu varian habis* (terjadi disparitas angka 1 vs 18).
- **Rencana Tindakan:**
  Sepakati definisi bisnis: apakah produk dianggap "Perlu Restock" jika ada salah satu varian habis atau semua varian habis, lalu seragamkan query di kedua tempat.

### BKL-006: Pembersihan Sisa Kanal `both` & Penegasan 2 Kanal Tunggal
- **Sumber Temuan:** Keputusan Arsitektur User (Kanal sengaja dibatasi hanya `pos_only` atau `online_only`).
- **Tingkat Prioritas:** Sedang (Medium).
- **Status Riil Saat Ini:** 🟡 **Sebagian.** Form input `ProductForm.php` sudah membatasi 2 pilihan (`pos_only` & `online_only`), namun data lama di database (57 produk) masih berstatus `both`, dan Bulk Action `ProductsTable.php:244` masih menyediakan opsi `both`.
- **Rencana Tindakan:**
  Jalankan migrasi/skrip pembersihan data agar semua record yang bernilai `both` di-convert ke `online_only` (atau sesuai peruntukannya), dan hapus opsi `both` dari bulk action.

### BKL-007: Pencegahan Duplikasi pada Fitur "Buat Varian Otomatis"
- **Sumber Temuan:** Laporan Audit Logika - Temuan #1.4.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.** Aksi `generate_variants` di [`VariantsRelationManager.php:349`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L349) langsung membuat baris baru tanpa memeriksa apakah kombinasi atribut sudah ada di database.
- **Rencana Tindakan:**
  Tambahkan pengecekan kombinasi ID atribut sebelum `create()` untuk mencegah duplikasi jika tombol diklik berulang kali.

### BKL-008: Thumbnail Gambar di Tabel Varian Admin
- **Sumber Temuan:** Laporan Audit Logika - Temuan #1.5.
- **Tingkat Prioritas:** Rendah (Low/UX Improvement).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.** Dropdown form sudah menampilkan gambar, tetapi tabel ringkasan varian belum memiliki kolom thumbnail gambar.
- **Rencana Tindakan:**
  Tambahkan `CuratorColumn` atau `ImageColumn` pada tabel varian agar admin langsung melihat foto varian tanpa harus membuka form Edit.

### BKL-009: Validasi Pemilihan Varian pada Pembuatan Pesanan Manual Admin
- **Sumber Temuan:** Laporan Audit Logika - Temuan #2.1.
- **Tingkat Prioritas:** Tinggi (High/Data Integrity).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.** [`OrderForm.php:118`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L118) mengizinkan penyimpanan pesanan untuk produk bervarian tanpa memilih varian (`product_variant_id = null`).
- **Rencana Tindakan:**
  Pasang validasi `required(fn(Get $get) => Product::find($get('product_id'))?->has_variants)` pada field varian.

---

## 3. Optimasi Database & Perampingan Kode (Slimming & Performance)

### BKL-010: Database Indexing & Pembersihan Tabel Hantu
- **Sumber Temuan:** Diskusi Arsitektur Efisiensi Resource VPS.
- **Tingkat Prioritas:** Sedang (Medium/Performance).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.**
  - Tabel lama `coupons` dan `salespages` masih tertinggal di database.
  - Beberapa foreign key dan kolom pencarian transaksi (`payment_status`, `source`) belum memiliki index komposit.
- **Rencana Tindakan:**
  Hapus tabel tak terpakai melalui migrasi drop table, dan tambahkan database index pada tabel transaksi dan katalog.

### BKL-011: Refactoring & Perampingan File Gemuk ("Fat Class")
- **Sumber Temuan:** Diskusi Arsitektur Pemeliharaan Kode.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.** Berkas `Checkout.php` (~900 baris) dan `ProductDetail.php` (~600 baris) menanggung terlalu banyak tanggung jawab secara bersamaan.
- **Rencana Tindakan:**
  Ekstraksi kalkulasi ongkir, voucher, dan pembayaran ke Action Class terpisah (Single Responsibility Principle).

---

## 4. Modul Reseller & Otorisasi Diskon (Fitur Lanjutan)

### BKL-012: Penyelarasan Otorisasi & Rumus Harga Reseller
- **Sumber Temuan:** Laporan Audit Logika - Temuan #3.1 & #6.1.
- **Tingkat Prioritas:** Rendah (Low - Fitur Lanjutan / Non-Wajib Saat Ini).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.**
  - Tombol approve reseller di admin belum meng-assign Spatie Role `reseller`.
  - Halaman checkout mengabaikan kolom `reseller_price` produk dan hanya menghitung potongan persentase subtotal.
  - Inkonsistensi status string `'active'` (di menu Reseller) vs `'approved'` (di menu Users).
- **Rencana Tindakan:**
  Dikerjakan bertahap saat fitur reseller resmi diwajibkan oleh bisnis. Selesaikan penyeragaman status string, assignment Spatie role, dan sinkronisasi formula `reseller_price` antara katalog dan checkout.

---

## 5. Isolasi Buy Now & Sinkronisasi Stok Keranjang

### BKL-013: Pemisahan Penuh Aksi "Beli Sekarang" (`is_buy_now = true`) & Notifikasi Penyesuaian Stok Keranjang
- **Sumber Temuan:** Klarifikasi Aturan Bisnis User pada Audit Verifikasi P1 Fase 1 (2026-11-23).
- **Tingkat Prioritas:** Sedang-Tinggi (UX & Integritas Alur Checkout).
- **Status Riil Saat Ini:** 🔴 **Belum Diterapkan.**
  - Saat ini [`ProductDetail::buyNow()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L549-L571) masih memanggil `addToCart()` ke keranjang reguler (`is_buy_now = false`) lalu mengisolasi lewat `session('checkout_item_ids')`, sementara [`Checkout.php:950-981`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L950-L981) mengambil keranjang pertama tanpa membedakan `is_buy_now`.
  - Akibatnya, jika produk yang sama sudah ada di keranjang reguler mencapai batas stok, klik "Beli Sekarang" ikut terblokir oleh isi keranjang reguler.
  - Selain itu, belum ada notifikasi / penyesuaian otomatis di halaman `/cart` bila stok produk di gudang berkurang sehingga `cart_items.quantity > stock` riil.
- **Rencana Tindakan:**
  1. Pisahkan `ProductDetail::buyNow()` agar memakai `Cart` khusus `is_buy_now = true` (mengabaikan isi keranjang reguler, hanya mengecek stok riil terhadap qty yang dipilih saat itu) dan sesuaikan `Checkout.php` membaca keranjang `is_buy_now` saat mode Buy Now aktif.
  2. Tambahkan deteksi & notifikasi (serta auto-clamp / peringatan jelas) pada halaman `/cart` bila ada item yang kuantitasnya melebihi stok riil terbaru atau variannya sudah dinonaktifkan.
