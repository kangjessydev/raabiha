# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

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
