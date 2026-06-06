# Project Task Tracker (Kanban)

File ini digunakan oleh AI dan Developer untuk melacak progres pengerjaan agar tidak ada iterasi yang berulang dan memastikan sinkronisasi antara Admin Panel dan Frontend Customer.

## 📌 Status Saat Ini: Sprint 2 (Integrasi Fungsional & Lengkap)

### 🏗️ Admin Panel (Filament)
**✅ Selesai (Done):**
- Manajemen Blog (Posts, Kategori Blog, Tag Blog, Komentar).
- E-Commerce Dasar (Order Transaksi, Metode Pengiriman, Metode Pembayaran).
- List Reseller (Terintegrasi lewat Spatie Roles).
- Konten (Halaman Statis, Sales Page, Ulasan Produk).
- Kustomisasi Tema, Cluster, dan Integrasi Media Library (Curator).

**🚧 Belum (To Do - Urutan pengerjaan *Rough Development*, dari termudah/tercepat):**
- [ ] **Perbaikan Pengumuman Topbar:** Memperbaiki topbar yang tidak muncul di frontend, menghapus efek teks berjalan (marquee), dan mengubah input teks menjadi *Rich Editor*.
- [ ] **Logika Banner Promosi (Popup):** Menambahkan fitur *Single Active Toggle* (jika satu diaktifkan, yang lain otomatis mati) dan konfigurasi penempatan halaman tampilnya.
- [ ] **Sistem Voucher Lanjutan:** Mengembangkan fitur Voucher dengan aturan kompleks: Diskon Nominal/Persentase, Spesifik User (Email), Minimal Pembelian, dan Bonus Item (Free Gift).
- [x] **SEO Fields (Blog SEO, Produk SEO, Home SEO):** Menambahkan field *Meta Title & Description* di Resource Post, Product, dan SiteSettings.
- [x] **Kategori Publik:** Mengatur hierarki kategori dan slug yang diakses lewat `/collections/{slug}`.
- [x] **Galeri Builder:** Membuat Single Page Settings menggunakan Filament Builder untuk Galeri Publik (menggantikan sistem CRUD lama) agar lebih dinamis dan modular.
- [x] **Dashboard Overview:** Menambahkan Widget statistik (Total Pendapatan, Order Baru, dll) di halaman depan Admin.
- [x] **Export & Import Data:** Memasang fungsi/plugin export-import Excel pada resource Produk dan Order.
- [x] **Status & Pengaturan Reseller:** Menambahkan kolom status (Pending/Aktif) pada entitas User dan pengaturan diskon *reseller*.
- [x] **Badge Guest Checkout:** Menambahkan label visual pada OrderResource untuk membedakan *Guest* vs *Registered*.
- [x] **Laporan Transaksi:** Menyempurnakan halaman `TransactionReport` yang sudah ada menjadi grafik/tabel yang informatif.
- [x] **Pengaturan Global (Site Settings):** Membuat halaman manajemen terpusat untuk Identitas Toko, Menu Navbar Dinamis (Repeater), Footer, Kontak, dan Integrasi Skrip Eksternal.
- [x] **Halaman Statis (CMS):** Membuat Resource untuk halaman legal/dokumen (T&C, Privacy Policy) lengkap dengan dukungan *SEO Fields*.
- [x] **Sales Page Builder:** Membangun *Filament Builder* modular (Hero, Features, Testimonial) untuk membuat *Landing Page* dinamis.

### 🛒 Frontend Customer (Blade + Livewire)
**✅ Selesai (Done):**
- Slicing UI Utama (Home, About, Contact, Dasbor Customer, Product Detail).
- Katalog Dinamis (`shop`) dengan filter pencarian.
- Transisi Galeri & Logika UI Bottom Sheet untuk pemilihan Varian & QTY.
- Dashboard Customer Ekstensif (Riwayat Pesanan, Detail Invoice, Manajemen Alamat, Voucher, Statistik).
- Sistem Blog Dinamis & Komentar interaktif dengan dukungan *Nested Replies* dan *Auto-Polling Moderation*.

**🚧 Belum (To Do - Urutan pengerjaan *Rough Development*, dari termudah/tercepat):**
- [x] **Halaman Pendaftaran Reseller:** Membuat formulir *Livewire* untuk mendaftar sebagai Reseller.
- [x] **Sistem Keranjang & Beli Sekarang:** Menyelaraskan fungsi masuk ke keranjang dan proses Checkout langsung (*Buy Now*).
- [x] **Sistem Checkout (Frontend):** Form pengisian alamat pengiriman dan ringkasan harga.
- [x] **Dashboard Reseller:** Membuat area khusus (*portal*) untuk Reseller memantau komisi, status diskon, dan histori pembelian (bisa via frontend atau panel Filament baru).
- [x] **Global UI Binding:** Menghubungkan data *Site Settings* (Logo, Navbar, Footer, Nomor Kontak) ke komponen *Frontend* secara dinamis.
- [x] **Refactoring Hardcode Pages:** Mengoptimalkan halaman Home, About, Lokasi & Kontak agar berpadu dengan data dinamis (Galeri sudah di-refactor menggunakan Builder).
- [x] **Dynamic Page Rendering:** Membuat *routing* dan *views* untuk me-render *Halaman Statis* dan *Sales Page* di bawah satu Catch-all route (`/{slug}`).

---

## 🔌 Integrasi API (Fase Berikutnya)
- [x] **Logistik (RajaOngkir Starter):** Cek ongkir JNE/POS/TIKI menggunakan Dropdown Provinsi & Kota (Sesuai keputusan: versi gratis/manual tanpa map search). Lacak resi dilakukan direct via external.
- [x] **Pembayaran (Xendit / Tripay):** Virtual Account, QRIS otomatis dengan Webhook integrasi ke status pesanan.

## 🧊 Backlog / Ekstensi Bisnis (Sprint 3 / Fase 3)
- [ ] **Google Analytics Embed:** Menambahkan halaman *iframe* Looker Studio di Filament untuk metrik kunjungan lengkap (Traffic Source, Pageviews) dengan 0% beban server.
- [ ] **Kasir Sederhana (POS Manual):** Menyempurnakan form *Create Order* di Admin Panel agar kasir *offline* bisa menginput pesanan secara cepat dan otomatis menjumlahkan harga.
- [ ] **Buku Kas (Cashflow):** Membuat tabel tunggal pencatatan *Cash In* (Otomatis dari pesanan lunas) dan *Cash Out* (Input manual biaya operasional).
- [ ] **Quick Edit & Log Stok:** Membuat halaman khusus untuk mengetik/mengubah jumlah stok secara langsung (*editable column*) dilengkapi tabel *Log Keluar-Masuk* barang yang ringan.
- [ ] **Dashboard Metrik Lanjutan:** Menambahkan kumpulan *Widget* statistik di *Homepage* Admin (Total Penjualan Hari Ini, Pesanan Masuk, Pengeluaran, Laba Bersih Harian) yang diolah secara *real-time* dari data pesanan & buku kas.
- [ ] *Virtual Fitting Room* dengan avatar kustom.
- [ ] *Script* Backup Database otomatis di VPS CloudPanel.
