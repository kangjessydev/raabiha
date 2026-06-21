# Pemetaan Role & Hak Akses (Raabiha E-Commerce)

Dokumen ini memuat daftar lengkap _Role_ (Peran) di Panel Admin beserta detail hak akses (CRUD) untuk masing-masing modul. Gunakan panduan ini untuk mencocokkan hasil pengujian login dengan fungsi yang seharusnya terbuka.

_(Standar Password untuk semua akun demo: `password`)_

---

### 1. Super Admin

- **Fungsi Utama:** Kendali penuh seluruh sistem tanpa batas.
- **Akses Modul:**
    - **Semua Modul Terbuka**, termasuk menu krusial seperti Pengaturan Global (Identitas, Integrasi, Pengaturan E-Commerce) dan _Shield_ (Manajemen User, Role, & Permission).
- **Hak Akses:** `Create`, `Read`, `Update`, `Delete` penuh.

---

### 2. Owner (Pemilik)

- **Akun Demo:** `owner@raabiha.com`
- **Fungsi Utama:** Memantau ringkasan performa bisnis, laporan penjualan, dan lalu lintas keuangan tanpa bisa secara tak sengaja mengubah/menghapus data.
- **Akses Modul:**
    - **Dashboard & Widget:** Grafik Pendapatan dan Statistik Pesanan.
    - **Pesanan (Orders):** Hanya melihat (_Read-Only_).
    - **Produk:** Hanya melihat (_Read-Only_).
    - **User/Pelanggan:** Hanya melihat (_Read-Only_).
    - **Buku Kas (Cashflow):** Hanya melihat (_Read-Only_).
- **Hak Akses Tambahan:** _(Pada konfigurasi demo default, akun Owner juga dirangkap dengan role Marketing & Logistik untuk keperluan micromanagement, namun secara murni hak Owner hanya Read-Only)._

setelah testing (Semua Berhasil Diperbaiki & Dibatasi):

- [x] masih bisa melihat media di cluster Media Files (Memang diizinkan untuk melihat/read-only)
- [x] masih bisa mengedit pesanan (orders) (Sudah dibatasi hanya Read-Only via Spatie permissions)
- [x] masih bisa mengelola pengumuman (topbar) (Sudah dibatasi menjadi Read-Only: field disabled & tombol simpan disembunyikan)
- [x] masih bisa mengelola banner promosi (Sudah dibatasi menjadi Read-Only: toggle dinonaktifkan)
- [x] masih bisa mengelola voucher (Sudah dibatasi hanya Read-Only via Spatie permissions)
- [x] masih bisa menambah dan mengubah produk dan mengatur aktif/nonaktif produk (Sudah dibatasi menjadi Read-Only: toggle dinonaktifkan)
- [x] masih bisa mengelola kategori dan atribut produk (Sudah dibatasi hanya Read-Only via Spatie permissions)
- [x] masih bisa mengedit stok produk melalui manajemen stok dan masih bisa mengatur minimum stok (Sudah dibatasi: tombol edit stok & pengaturan stok disembunyikan)
- [x] masih bisa mengelola reseller dan pengaturan reseller (Sudah dibatasi: tombol approve/reject & tombol simpan pengaturan disembunyikan, field disabled)
- [x] masih bisa mengelola metode pengiriman dan metode pembayaran (Sudah dibatasi menjadi Read-Only: toggle dinonaktifkan)
- [x] masih bisa mengelola CMS di cluster konten (Sudah dibatasi hanya Read-Only via Spatie permissions)
- [x] masih bisa mengelola pengaturan global (Sudah dibatasi menjadi Read-Only: field disabled & tombol simpan disembunyikan)
- [x] apakah owner tidak memiliki akses untuk melihat analisa pengunjung? (Sudah diperbaiki: akses Analitik Pengunjung telah dibuka untuk Owner & Marketing)

---

### 3. Kasir (Admin POS)

- **Akun Demo:** `kasir@raabiha.com`
- **Fungsi Utama:** Menginput pesanan manual (POS Offline) dan mencatat transaksi tunai/masuk.
- **Akses Modul:**
    - **Pesanan (Orders):** Melihat dan MENGINPUT pesanan baru (`View`, `ViewAny`, `Create`). Tidak bisa menghapus pesanan.
    - **Buku Kas (Cashflow):** Melihat dan MENGINPUT pemasukan/pengeluaran kasir harian (`View`, `ViewAny`, `Create`).
    - **Produk:** Melihat daftar produk dan harganya sebagai referensi (`View`, `ViewAny`). Tidak bisa mengedit produk.
    - **Pelanggan (Customers):** Melihat daftar pelanggan dan membuat akun pelanggan baru saat checkout POS (`ViewAny`, `View`, `Create`).
    - **Dashboard:** Melihat halaman depan Dasbor.

---

### 4. Finance (Keuangan)

- **Akun Demo:** `finance@raabiha.com`
- **Fungsi Utama:** Memverifikasi pembayaran, memantau grafik pendapatan, dan mengelola arus kas harian.
- **Akses Modul:**
    - **Buku Kas (Cashflow):** Mengelola penuh pencatatan kas (`View`, `Create`, `Update`).
    - **Pesanan (Orders):** Melihat detail dan mengubah status pembayaran (misal: dari Pending menjadi Lunas). (`View`, `Update`).
    - **Metode Pembayaran:** Melihat daftar VA/Bank untuk referensi (`View`).
    - **Grafik Pendapatan:** Bisa melihat diagram laporan penjualan.

---

### 5. Marketing (Pemasaran)

- **Akun Demo:** `marketing@raabiha.com`
- **Fungsi Utama:** Mengelola etalase produk, kampanye promosi, dan halaman muka toko (_Landing Page_).
- **Akses Modul:**
    - **Produk & Kategori:** Mengelola penuh katalog (`Create`, `Read`, `Update`).
    - **Kupon Promosi (Voucher):** Membuat diskon baru (`Create`, `Read`, `Update`).
    - **Promo Banner:** Mengganti slider/banner di halaman utama (`Create`, `Read`, `Update`).
    - **Sales Page (Halaman Statis):** Membangun/mengubah konten profil perusahaan, kontak, dll. (`Create`, `Read`, `Update`).
- **Catatan:** Tim Marketing tidak memiliki akses untuk melihat Pesanan atau Keuangan pelanggan.

---

### 6. Logistics / Gudang

- **Akun Demo:** `gudang@raabiha.com`
- **Fungsi Utama:** Memproses pemaketan, memperbarui nomor resi pengiriman, dan mengurus keluar/masuk stok.
- **Akses Modul:**
    - **Pesanan (Orders):** Melihat rincian alamat dan mengubah status resi/pengiriman (`View`, `Update`).
    - **Produk:** Bisa melihat dan menyesuaikan sisa stok produk (`View`, `Update`).
    - **Riwayat Stok (Stock Logs):** Mencatat stok masuk (restock) atau stok keluar/rusak (`View`, `Create`).
    - **Metode Pengiriman:** Melihat daftar kurir referensi (`View`).

---

### 7. Customer Service (CS)

- **Akun Demo:** `cs@raabiha.com`
- **Fungsi Utama:** Berkomunikasi dengan pelanggan dan menjaga reputasi toko melalui ulasan.
- **Akses Modul:**
    - **Kontak/Pesan (Inquiry):** Membalas pesan yang masuk dari halaman kontak (`View`, `Update`).
    - **Ulasan Produk (Reviews):** Memoderasi, menyetujui, dan membalas ulasan yang ditinggalkan pembeli (`View`, `Update`).
    - **Pesanan (Orders):** Hanya melihat (_Read-Only_) untuk menjawab tiket "Kapan pesanan saya sampai?".
    - **Produk:** Hanya melihat (_Read-Only_) untuk menjawab detail bahan/ukuran ke pembeli.
