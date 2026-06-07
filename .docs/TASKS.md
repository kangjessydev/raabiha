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
- [x] **Perbaikan Pengumuman Topbar:** Memperbaiki bug tidak muncul di frontend. Mengubah input menjadi *Rich Editor* terbatas (hanya bold/italic/link) dengan indikator batas karakter maksimal. Jika teks terlalu panjang, aplikasikan efek *marquee* (teks berjalan) khusus di layar kecil.

**🚧 Belum (To Do - Urutan pengerjaan *Rough Development*, dari termudah/tercepat):**
- [x] **Logika Banner Promosi (Popup):** Menambahkan fitur *Single Active Toggle* (satu aktif, lainnya otomatis mati). Menambahkan dropdown penempatan (*Semua Halaman, Home, Katalog*).
- [x] **Sistem Voucher Lanjutan:** Mengembangkan database voucher dengan kolom: Tipe Diskon (Nominal/%), Syarat Min. Belanja, Minimal Item, Max Diskon, Spesifik User (Email JSON), Free Gift Item, Aturan Ongkir (Stacking), Aturan Reseller, dan Kuota Maksimal Penggunaan.
- [x] **Logika Ulasan Produk:** Menonaktifkan tombol 'Create' di Filament Admin (Hanya bisa dibaca/dimoderasi). Membuat tombol 'Beri Ulasan' di Dasbor Customer untuk pesanan yang sudah selesai, serta menampilkannya di Detail Produk.
- [x] **Sistem Kontak Hybrid (Inquiries):** Membuat tabel `inquiries` untuk menampung pesan *Contact Us*. Di *frontend*, pembeli bisa memilih metode kirim (Radio: *via Email* atau *via WhatsApp*). Di *Site Settings*, admin bisa mengatur daftar Subjek beserta *toggle* apakah subjek tersebut wajib disimpan ke *database* atau langsung dialihkan (otomatis filterable di Filament).
- [x] **Notifikasi Real-time (Lonceng & Suara):** Mengaktifkan fitur *Database Notifications* bawaan Filament di *navbar* atas, dan menyisipkan *script* Alpine.js untuk memutar suara (mp3/wav) saat ada notifikasi baru masuk.
- [x] **SEO Fields (Blog SEO, Produk SEO, Home SEO):** Menambahkan field *Meta Title & Description* di Resource Post, Product, dan SiteSettings.
- [x] **Kategori Publik:** Mengatur hierarki kategori dan slug yang diakses lewat `/collections/{slug}`.
- [x] **Galeri Builder:** Membuat Single Page Settings menggunakan Filament Builder untuk Galeri Publik (menggantikan sistem CRUD lama) agar lebih dinamis dan modular.
- [x] **Dashboard Overview:** Menambahkan Widget statistik (Total Pendapatan, Order Baru, dll) di halaman depan Admin.
- [x] **Export & Import Data:** Memasang fungsi/plugin export-import Excel pada resource Produk dan Order.
- [x] **Manajemen Reseller (B2B):** Membuat *Tabs* filter (Semua, Aktif, Pending) di Daftar Reseller. Proses *Approval* manual oleh Admin. Mengatur nilai diskon *flat percentage* untuk semua reseller aktif di *Site Settings*.
- [x] **Badge Guest Checkout:** Menambahkan label visual pada OrderResource untuk membedakan *Guest* vs *Registered*.
- [x] **Widgets Laporan Pesanan:** Menghapus menu *Laporan Transaksi* yang redundan dan menggantinya dengan *Header Widgets* (Total Pending, Pendapatan Hari Ini) langsung di atas tabel Pesanan.
- [x] **Mode Libur & Jadwal Toko:** Menambahkan pengaturan "Tutup Toko / Mode Libur" di *Site Settings* yang akan memunculkan peringatan otomatis di *frontend* agar pembeli tahu kapan barang dikirim.
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
- [x] **Sistem Keranjang & Beli Sekarang:** Menyelaraskan fungsi masuk ke keranjang dan proses Checkout langsung (*Buy Now*), lengkap dengan perhitungan otomatis Diskon Reseller jika pembeli adalah reseller aktif.
- [x] **Sistem Checkout (Frontend):** Form pengisian alamat pengiriman dan ringkasan harga.
- [x] **Integrasi Dashboard Reseller:** Menggabungkan profil Reseller ke dalam *Dashboard Customer* standar. Menambahkan *Badge* (Lencana) Reseller dan tab Laporan Pembelian Khusus Reseller.
- [x] **Global UI Binding:** Menghubungkan data *Site Settings* (Logo, Navbar, Footer, Nomor Kontak) ke komponen *Frontend* secara dinamis.
- [x] **Refactoring Hardcode Pages:** Mengoptimalkan halaman Home, About, Lokasi & Kontak agar berpadu dengan data dinamis (Galeri sudah di-refactor menggunakan Builder).
- [x] **Dynamic Page Rendering:** Membuat *routing* dan *views* untuk me-render *Halaman Statis* dan *Sales Page* di bawah satu Catch-all route (`/{slug}`).

---

## 🔌 Integrasi API (Fase Berikutnya)
- [ ] **Logistik (RajaOngkir by Komerce):** Menggunakan API *Shipping Cost* Komerce untuk cek ongkir (JNE/POS/TIKI). Termasuk pembuatan antarmuka di `Pengaturan Checkout` untuk *Origin City* dan kurir aktif.
- [ ] **Pembayaran (Xendit / Tripay):** Virtual Account, QRIS otomatis dengan Webhook integrasi ke status pesanan.

## 🧊 Backlog / Ekstensi Bisnis (Sprint 3 / Fase 3)
- [ ] **Google Analytics Embed:** Menambahkan halaman *iframe* Looker Studio di Filament untuk metrik kunjungan lengkap (Traffic Source, Pageviews) dengan 0% beban server.
- [ ] **Kasir Sederhana (POS Manual):** Menyempurnakan form *Create Order* di Admin Panel agar kasir *offline* bisa menginput pesanan secara cepat dan otomatis menjumlahkan harga.
- [ ] **Buku Kas (Cashflow):** Membuat tabel tunggal pencatatan *Cash In* (Otomatis dari pesanan lunas) dan *Cash Out* (Input manual biaya operasional).
- [ ] **Quick Edit & Log Stok:** Membuat halaman khusus untuk mengetik/mengubah jumlah stok secara langsung (*editable column*) dilengkapi tabel *Log Keluar-Masuk* barang yang ringan.
- [ ] **Dashboard Metrik Lanjutan:** Menambahkan kumpulan *Widget* statistik di *Homepage* Admin (Total Penjualan Hari Ini, Pesanan Masuk, Pengeluaran, Laba Bersih Harian) yang diolah secara *real-time* dari data pesanan & buku kas.
- [ ] *Virtual Fitting Room* dengan avatar kustom.
- [ ] *Script* Backup Database otomatis di VPS CloudPanel.
