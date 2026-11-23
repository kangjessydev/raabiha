# Catatan Keputusan Arsitektur (ADR)

Dokumen ini mencatat keputusan arsitektur, pilihan stack, dan trade-off yang disepakati bersama User.

---

## ADR-001: Peniadaan Kanal Penjualan 'both' (Tunggal: Web atau POS Saja)
- **Tanggal:** 2026-11-09
- **Status:** Diterima (Accepted)
- **Konteks:**
  Sebelumnya ada opsi kanal `both` yang mengizinkan satu produk tampil di E-Commerce Web dan Kasir POS fisik secara bersamaan. Namun dalam model operasional bisnis yang diputuskan User, aturan ini sengaja dihilangkan.
- **Keputusan:**
  Kanal penjualan produk dibatasi tegas hanya menjadi 2 opsi:
  1. `online_only` (Hanya tampil dan dijual di Website E-Commerce).
  2. `pos_only` (Hanya tampil dan dijual di Mesin Kasir / Toko Fisik).
  Opsi `both` tidak boleh dimunculkan kembali di form produk maupun tabel admin.

---

## ADR-002: Klasifikasi Fitur Reseller sebagai Fitur Lanjutan (Non-Wajib Saat Ini)
- **Tanggal:** 2026-11-09
- **Status:** Diterima (Accepted)
- **Konteks:**
  Fitur reseller saat ini baru terpasang sebagian dan memiliki inkonsistensi otorisasi serta kalkulasi harga antara halaman produk dan checkout.
- **Keputusan:**
  Fitur reseller secara resmi diklasifikasikan sebagai **Fitur Lanjutan (Backlog / Non-Fundamental)**. Fokus utama saat ini diprioritaskan pada stabilitas fundamental katalog produk, manajemen stok, dan keandalan sistem inti. Perbaikan reseller ditunda sampai ada permintaan resmi dari bisnis.
