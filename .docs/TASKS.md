# Project Task Tracker (Kanban)

File ini digunakan oleh AI dan Developer untuk melacak progres pengerjaan agar tidak ada iterasi yang berulang dan memastikan sinkronisasi antara Admin Panel dan Frontend Customer.

## 📌 Status Saat Ini: Sprint 2 (Integrasi Fungsional & Lengkap)

### 🏗️ Admin Panel (Filament)
**✅ Selesai (Done):**
- Manajemen Blog (Posts, Kategori Blog, Tag Blog, Komentar).
- E-Commerce Dasar (Order Transaksi, Metode Pengiriman, Metode Pembayaran).
- Promosi & Tampilan (Voucher, Banner Promo / Popup Modal, Pengumuman Topbar).
- Konten (Halaman Statis, Sales Page, Ulasan Produk).
- List Reseller (Terintegrasi lewat Spatie Roles).
- Kustomisasi Tema, Cluster, dan Integrasi Media Library (Curator).

**🚧 Belum (To Do - Urutan pengerjaan *Rough Development*, dari termudah/tercepat):**
- [x] **SEO Fields (Blog SEO, Produk SEO, Home SEO):** Menambahkan field *Meta Title & Description* di Resource Post, Product, dan SiteSettings.
- [x] **Galeri CRUD:** Membuat Model dan Resource baru untuk Galeri Publik.
- [x] **Dashboard Overview:** Menambahkan Widget statistik (Total Pendapatan, Order Baru, dll) di halaman depan Admin.
- [x] **Export & Import Data:** Memasang fungsi/plugin export-import Excel pada resource Produk dan Order.
- [x] **Status & Pengaturan Reseller:** Menambahkan kolom status (Pending/Aktif) pada entitas User dan pengaturan diskon *reseller*.
- [x] **Badge Guest Checkout:** Menambahkan label visual pada OrderResource untuk membedakan *Guest* vs *Registered*.
- [x] **Laporan Transaksi:** Menyempurnakan halaman `TransactionReport` yang sudah ada menjadi grafik/tabel yang informatif.

### 🛒 Frontend Customer (Blade + Livewire)
**✅ Selesai (Done):**
- Slicing UI Utama (Home, About, Contact, Dasbor Customer, Product Detail).
- Katalog Dinamis (`shop`) dengan filter pencarian.
- Transisi Galeri & Logika UI Bottom Sheet untuk pemilihan Varian & QTY.

**🚧 Belum (To Do - Urutan pengerjaan *Rough Development*, dari termudah/tercepat):**
- [ ] **Halaman Pendaftaran Reseller:** Membuat formulir *Livewire* untuk mendaftar sebagai Reseller.
- [ ] **Sistem Keranjang & Beli Sekarang:** Menyelaraskan fungsi masuk ke keranjang dan proses Checkout langsung (*Buy Now*).
- [ ] **Dashboard Customer:** Mengaktifkan panel riwayat pesanan (*My Account*) untuk pengguna reguler.
- [ ] **Sistem Checkout (Frontend):** Form pengisian alamat pengiriman dan ringkasan harga.
- [ ] **Dashboard Reseller:** Membuat area khusus (*portal*) untuk Reseller memantau komisi, status diskon, dan histori pembelian (bisa via frontend atau panel Filament baru).

---

## 🔌 Integrasi API (Fase Berikutnya)
- [ ] **Logistik (RajaOngkir):** Cek ongkir otomatis dan gratis ongkir berdasarkan alamat.
- [ ] **Pembayaran (Xendit):** Virtual Account, QRIS otomatis dengan Webhook integrasi ke status pesanan.

## 🧊 Backlog / Opsional (Sprint 3 / Fase 2)
- [ ] *Virtual Fitting Room* dengan avatar kustom.
- [ ] *Script* Backup Database otomatis di VPS CloudPanel.
