# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Ditambahkan
- *(Belum ada)*

### Diubah
- *(Belum ada)*

### Dihapus & Alasan
- *(Belum ada)*

## [1.0.7] - 2026-11-09

### Ditambahkan
- Automated feature test untuk sinkronisasi gambar varian dan slider frontend (`tests/Feature/VariantImageMatchingTest.php`).
- Berkas rencana backlog perbaikan fitur POS (`docs/backlog.md`) mencakup otorisasi diskon manual, null-safe supervisor void, dan rate limiting PIN.
- Laporan audit menyeluruh keamanan & integritas data (`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`).
- Tiket handoff perbaikan keamanan & hardening integritas data (`docs/handoff/004-inspektur-to-backend-security-and-data-hardening.md`).
- Laporan pengerjaan perbaikan backend (`docs/reports/2026-11-08-backend-security-and-data-hardening.md`).
- Automated feature test untuk verifikasi keamanan (`tests/Feature/SecurityHardeningTest.php`).

### Diubah
- Resolusi gambar varian pada `VariantsRelationManager.php` kini mendukung gambar produk utama dalam format ID Media numerik maupun URL/path string, mencocokkan media dari tabel `curator` berdasarkan path atau nama file.
- Pemetaan galeri pada `ProductDetail.php` (`mount()`) kini menjaga keselarasan urutan 1-ke-1 antara `$galleryUrls` dan `$galleryMediaIds`, serta menambahkan penanganan fallback pencocokan via path/URL media pada `selectOption()`, memastikan slider gambar otomatis meluncur ke foto varian yang dipilih user.
- Proteksi otorisasi rute invoice (`/invoice/{orderNumber}`) dan konfirmasi pesanan (`/order-success`) agar data pribadi tidak bocor ke publik tanpa autentikasi/sesi pesanan yang sah.
- Pengetatan akses data pengajuan refund pada `Account::openRefundStatus` dengan filter kepemilikan pengguna (`user_id`).
- Pembatasan mutasi item keranjang belanja pada `Cart::incrementQuantity`, `decrementQuantity`, dan `removeItem` agar terikat ke cart aktif pengguna/sesi.
- Validasi status aktif, saluran penggunaan (`usable_channel`), kuota, dan rekalkulasi nilai diskon voucher transaksi POS di sisi server melalui `PosTransactionService` dan `VoucherService`.

### Dihapus & Alasan
- *(Belum ada penghapusan berkas/fitur)*

## [1.0.6] - 2026-11-08

### Ditambahkan
- Dokumentasi pemetaan arsitektur proyek awal (`docs/project-brief.md`, `docs/project-map.md`, `docs/glossary.md`).
- Laporan audit awal baseline pengujian (`docs/reports/2026-11-08-onboard-initial.md`).
- Tiket handoff stabilisasi test suite (`docs/handoff/001-onboard-to-backend.md`).
- Tiket brief handoff perbaikan resolusi gambar & kalkulasi stok varian (`docs/handoff/002-asisten-to-backend.md`).
- Tiket handoff audit mutu dan keamanan (`docs/handoff/003-backend-to-inspektur.md`).
- Laporan pengerjaan backend (`docs/reports/2026-11-08-backend-image-and-variant-stock-fix.md`).
- Laporan audit inspektur mutu & keamanan alur data (`docs/reports/2026-11-08-inspektur-audit-image-and-stock.md`).

### Diubah
- Perbaikan resolusi URL string gambar pada `ProductsTable.php`, `ProductDetail.php`, `shop.blade.php`, dan `home.blade.php`.
- Penambahan eager loading `variants` dan format total stok varian pada `ProductsTable.php`.
- Penyesuaian kalkulasi kartu produk habis pada `ProductStatsWidget.php`.

### Dihapus & Alasan
- *(Belum ada penghapusan berkas/fitur)*
