# Backlog Perbaikan & Rencana Fitur

Dokumen ini mencatat daftar temuan, rencana peningkatan, dan perbaikan tertunda yang menunggu permintaan atau konfirmasi lebih lanjut dari pihak klien/bisnis sebelum diimplementasikan.

---

## 1. Modul Point of Sale (POS) & Terminal Kasir

### BKL-001: Hardening Otorisasi Diskon Manual Kasir di Sisi Server
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #4.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** ✅ **Selesai (P3 Fase 2 — 2026-11-23).** [`PosTransactionService::completePosTransaction()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L36-L86) kini memvalidasi threshold `pos_manual_discount_max_percent_without_pin` & `pos_manual_discount_max_rp_without_pin` di server, dan [`PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L736-L749) meneruskan otorisasi supervisor lewat token session sekali pakai (`pos_supervisor_discount_auth`) sambil membuang segala upaya pemalsuan dari payload klien. Diverifikasi oleh [`PosManualDiscountServerGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/PosManualDiscountServerGuardTest.php).

---

### BKL-002: Null-Safe Guard pada Nama Supervisor saat Pembatalan Nota (`voidOrder`)
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #5.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** ✅ **Selesai (P3 Fase 1 — 2026-11-23).** [`PosManager::voidOrder()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1214-L1336) kini memakai `$voidActorLabel` null-safe (`Disetujui Supervisor: ...` bila ada supervisor, atau `Oleh Kasir: ...` bila `pos_require_pin_for_void = false`). Diverifikasi oleh [`P3WidgetStockAndPosVoidNullSafeTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/P3WidgetStockAndPosVoidNullSafeTest.php).

---

### BKL-003: Rate Limiting Percobaan Tebak PIN Supervisor POS
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #6.
- **Tingkat Prioritas:** Ditangguhkan (Deferred — lihat **ADR-003** di [`docs/decisions.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/decisions.md)).
- **Status:** ⏸️ **Tidak Diterapkan Saat Ini (Keputusan Klien — 2026-11-23).** Klien menginstruksikan agar verifikasi PIN supervisor POS tidak dibatasi rate limit/lockout karena aturan operasional tidak boleh terlalu ketat.
- **Deskripsi:**
  Pengecekan PIN supervisor (4-6 digit) pada method pengambilalihan sesi paksa (`forceTakeoverWithSupervisor`), kas kecil (`addPettyCash`), buka laci (`openManualDrawer`), dan void nota tidak memakai rate limiter sesuai kebijakan klien saat ini.

---

## 2. Modul Admin Dashboard (Katalog, Stok & Varian)

### BKL-004: Sinkronisasi Nilai Kolom Stok & Sortir Tabel Produk
- **Sumber Temuan:** Laporan Audit Logika ([`docs/reports/2026-11-09-admin-logic-audit.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-09-admin-logic-audit.md)) - Temuan #1.1.
- **Tingkat Prioritas:** Tinggi (High).
- **Status Riil Saat Ini:** ✅ **Selesai (P2 Fase 1 — 2026-11-23).** Sorting kolom `stock` di [`ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php) kini memakai custom SQL sorting berbasis penjumlahan stok varian aktif (`is_active = 1`) untuk `has_variants = 1` dan `products.stock` untuk produk berdiri sendiri, dilengkapi `withSum` (`active_variants_stock_sum`) serta sinkronisasi `formatStateUsing` & `color` ke stok varian aktif. Diverifikasi oleh [`AdminOrderVariantAndProductStockSortTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/AdminOrderVariantAndProductStockSortTest.php).

### BKL-005: Penyelarasan Kriteria Stok Habis (Widget vs Manajemen Stok)
- **Sumber Temuan:** Laporan Audit Logika - Temuan #1.2.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status Riil Saat Ini:** ✅ **Selesai (P3 Fase 1 — 2026-11-23).** [`ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php#L19-L30) kini menghitung produk bervarian sebagai *"Perlu Restock / Stok Habis"* apabila memiliki minimal 1 varian aktif (`is_active = true`) dengan `stock <= 0` atau belum memiliki varian aktif sama sekali (varian non-aktif stok `0` diabaikan). Diverifikasi oleh [`P3WidgetStockAndPosVoidNullSafeTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/P3WidgetStockAndPosVoidNullSafeTest.php).

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
- **Status Riil Saat Ini:** ✅ **Selesai (P2 Fase 1 — 2026-11-23).** [`OrderForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php) kini mereset `product_variant_id` saat `product_id` diganti, menyembunyikan field varian untuk produk non-varian, mewajibkan pemilihan varian (`required`) untuk produk `has_variants = true`, memvalidasi kepemilikan `product_id` & status aktif varian di sisi server (sambil tetap mempertahankan pesanan historis bervarian non-aktif saat Edit), serta memberi label `(Non-Aktif)` pada opsi non-aktif. Diverifikasi oleh [`AdminOrderVariantAndProductStockSortTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/AdminOrderVariantAndProductStockSortTest.php).

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
- **Status Riil Saat Ini:** ✅ **Selesai (P2 Fase 2A & 2B — 2026-11-23).**
  - [`ProductDetail::buyNow()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) kini terisolasi penuh memakai `Cart` (`is_buy_now = true`) dan hanya mengecek stok riil terhadap qty Beli Sekarang tanpa menyentuh keranjang reguler.
  - [`Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php) memilih sumber cart berdasarkan `session('checkout_is_buy_now')` dan membersihkan session pasca-order via `cleanupCheckedOutCart()`.
  - [`Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php) & [`cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php) menerapkan Opsi A: banner notifikasi `$stockNotices`, auto-clamp qty ke stok riil, uncheck & blokir checkout item habis/non-aktif, serta disable tombol `- / +` & checkbox sesuai ketersediaan riil. Diverifikasi oleh [`BuyNowIsolationAndCartStockSyncTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/BuyNowIsolationAndCartStockSyncTest.php).
