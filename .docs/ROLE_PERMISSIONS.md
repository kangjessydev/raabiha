# Pemetaan Peran dan Hak Akses (Role & Permissions) - Raabiha E-Commerce

Dokumen ini merangkum pemetaan akses untuk setiap peran (*role*) di sistem Raabiha E-Commerce, berdasarkan klaster menu yang ada di Dasbor Admin Filament.

## 1. Cluster Dasbor
*   **Ringkasan:** Owner, Marketing, Finance, Logistics, CS, Admin Kasir
*   **Analitik Pengunjung:** Owner, Marketing
*   **Laporan Bisnis:** Owner, Finance

## 2. Cluster Media Files
*   **Ekspor Data:** Owner, Finance, Logistics
*   **Impor Data:** Owner, Logistics
*   **Pesan Masuk (Inquiries):** CS
*   **Media:** Owner, Marketing

## 3. Cluster Konten
*   **Artikel Blog:** Marketing (Full CRUD)
*   **Kategori Blog:** Marketing (Full CRUD)
*   **Tag Blog:** Marketing (Full CRUD)
*   **Komentar:** CS (Lihat, Ubah untuk moderasi, Hapus)
*   **CMS Halaman Utama:** Owner, Marketing (Full CRUD)
*   **Salespage:** Owner, Marketing (Full CRUD)
*   **Halaman Statis:** Owner, Marketing (Full CRUD)

## 4. Cluster E-Commerce
*   **Pengumuman (Topbar):** Owner (CRUD), Marketing (CRUD)
*   **Banner Promosi:** Owner (CRUD), Marketing (CRUD)
*   **Voucher:** Owner (CRUD), Marketing (CRUD)
*   **Pesanan:**
    *   **Owner:** CRUD (Full Access)
    *   **Admin Kasir:** Lihat, Buat (TIDAK BISA Ubah/Hapus)
    *   **CS:** Lihat, Buat, Ubah Status
    *   **Finance:** Lihat, Ubah (Konfirmasi Pembayaran)
    *   **Logistics:** Lihat, Ubah (Update Resi/Status Pengiriman)
*   **Buku Kas:**
    *   **Owner & Finance:** CRUD (Full Access)
    *   **Admin Kasir:** Lihat, Buat pengeluaran/pemasukan (TIDAK BISA Ubah/Hapus)
*   **Produk:**
    *   **Owner & Marketing:** CRUD (Katalog penuh)
    *   **Logistics, CS, Admin Kasir:** Hanya Lihat
*   **Kategori:** Owner (CRUD), Marketing (CRUD)
*   **Atribut:** Owner (CRUD), Marketing (CRUD)
*   **Ulasan Produk:**
    *   **Owner:** Lihat, Hapus
    *   **CS:** Lihat, Ubah (Moderasi), Hapus
*   **Manajemen Stok:**
    *   **Owner:** CRUD
    *   **Logistics:** Lihat, Tambah, Ubah
*   **Daftar Reseller (Pengguna):**
    *   **Owner:** CRUD
    *   **CS:** Lihat, Ubah (Hanya untuk Approval)
*   **Pengaturan Reseller:** Owner (Akses Halaman Pengaturan)
*   **Metode Pengiriman:**
    *   **Owner:** CRUD
    *   **Logistics:** Lihat, Ubah (Aktif/Nonaktif)
*   **Metode Pembayaran:**
    *   **Owner:** CRUD
    *   **Finance:** Lihat, Ubah (Aktif/Nonaktif)

## 5. Cluster Pengaturan
*   **Peran (Roles):** *Super Admin*
*   **Pengguna (Users):** *Super Admin*, Owner, CS (Sama seperti Daftar Reseller)
*   **Pengaturan Global:**
    *   *Super Admin:* Akses Penuh (Termasuk tab "Integrasi & API" dan tombol "Generate Roles")
    *   *Owner:* Akses Terbatas (Identitas, Mode Libur, Media, Kontak, Navbar, Footer)

---
*Catatan: Seluruh hak akses di atas dapat disinkronkan kapan saja dengan mengeklik tombol **"Generate Roles & Demo Users"** di pojok kanan atas menu **Pengaturan Global** menggunakan akun Super Admin.*
