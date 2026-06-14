# Project Task Tracker (Kanban)

File ini digunakan oleh AI dan Developer untuk melacak progres pengerjaan agar tidak ada iterasi yang berulang dan memastikan sinkronisasi antara Admin Panel dan Frontend Customer.

## Status Saat Ini: Sprint 2 (Integrasi Fungsional & Lengkap)

### Admin Panel (Filament)
**Selesai (Done):**
- Manajemen Blog (Posts, Kategori Blog, Tag Blog, Komentar).
- E-Commerce Dasar (Order Transaksi, Metode Pengiriman, Metode Pembayaran).
- List Reseller (Terintegrasi lewat Spatie Roles).
- Konten (Halaman Statis, Sales Page, Ulasan Produk).
- Kustomisasi Tema, Cluster, dan Integrasi Media Library (Curator).
- [x] **Perbaikan Pengumuman Topbar:** Memperbaiki bug tidak muncul di frontend. Mengubah input menjadi *Rich Editor* terbatas (hanya bold/italic/link) dengan indikator batas karakter maksimal. Jika teks terlalu panjang, aplikasikan efek *marquee* (teks berjalan) khusus di layar kecil.

**Belum (To Do - Urutan pengerjaan *Rough Development*, dari termudah/tercepat):**
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

### Frontend Customer (Blade + Livewire)
**Selesai (Done):**
- Slicing UI Utama (Home, About, Contact, Dasbor Customer, Product Detail).
- Katalog Dinamis (`shop`) dengan filter pencarian.
- Transisi Galeri & Logika UI Bottom Sheet untuk pemilihan Varian & QTY.
- Dashboard Customer Ekstensif (Riwayat Pesanan, Detail Invoice, Manajemen Alamat, Voucher, Statistik).
- Sistem Blog Dinamis & Komentar interaktif dengan dukungan *Nested Replies* dan *Auto-Polling Moderation*.
- **Halaman Pendaftaran Reseller & Welcome Onboarding:** Membuat formulir *Livewire* dan sistem deposit awal.
- **Sistem Keranjang & Beli Sekarang:** Menyelaraskan fungsi masuk ke keranjang dan proses Checkout langsung, dengan perhitungan Diskon Reseller otomatis.
- **Sistem Checkout (Frontend):** Form pengisian alamat pengiriman dan ringkasan harga.
- **Integrasi Dashboard Reseller:** Menggabungkan profil Reseller ke dalam *Dashboard Customer* standar.
- **Global UI Binding (Dasar):** Menghubungkan data *Site Settings* (pengumuman, status mode libur, tautan menu, dll) ke komponen *Frontend* secara dinamis.
- **Refactoring Hardcode Pages:** Mengoptimalkan halaman Home, About, Lokasi & Kontak.
- **Dynamic Page Rendering:** Membuat *routing* untuk me-render *Halaman Statis* dan *Sales Page* (`/{slug}`).
- [x] **Penyempurnaan Global UI Binding:** Menghubungkan logo (Light & Dark Mode) dan nama web di Navbar dan Footer secara dinamis ke pengaturan **Identitas Toko** (termasuk Favicon, judul tab browser dinamis dengan slogan/SEO beranda).
- [x] **Pencarian Global Navbar (Search):** Berhasil memfungsikan ikon search di navbar agar mengarah ke halaman pencarian dinamis yang mencari produk dan artikel sekaligus.
- [x] **Logika Filter Katalog Produk:** Berhasil menyelesaikan filter Ukuran, Warna, dan Rentang Harga di halaman `/shop` agar terhubung ke Livewire/database secara real-time.

**Belum (To Do):**
- *Semua fitur frontend customer utama telah diimplementasikan.*

---

## Integrasi API (Fase Berikutnya)
- [x] **Logistik (RajaOngkir by Komerce):** Menggunakan API *Shipping Cost* Komerce untuk cek ongkir (JNE/POS/TIKI). Termasuk pembuatan antarmuka di `Pengaturan Checkout` untuk *Origin City* dan kurir aktif.
- [x] **Pembayaran (Xendit / Tripay):** Sinkronisasi Virtual Account, QRIS, e-Wallet otomatis dengan *Webhook* integrasi ke status pesanan, serta *Dual-Gateway Switcher* di Admin Panel.

## Backlog / Ekstensi Bisnis (Sprint 3 / Fase 3)
- [ ] **Fitur Wishlist Pelanggan:** Membuat tabel `wishlists` (user_id, product_id) dan fitur simpan produk untuk pengguna terdaftar yang dapat diakses langsung dari halaman katalog dan dashboard akun.
- [ ] **Role & Manajemen Akses Lanjutan (Spatie):** 
  - Membuat Role **CS** yang hanya bisa mengakses Pesan Masuk (Inquiry) dan menyetujui Ulasan Produk.
  - Membatasi akses menu *Integrasi & API* khusus untuk Super Admin saja.
- [ ] **Fitur Refund Pelanggan:** Merancang tabel `refund_requests` dan alur antarmuka (UI) khusus di Dasbor Customer & Admin Panel untuk menangani pengembalian dana.
- [ ] **Simpan Alamat Otomatis di Checkout:** Menambahkan *checkbox* kecil ("Simpan alamat ini ke akun saya") di halaman Checkout khusus bagi pelanggan yang belum memiliki alamat tersimpan, agar alamat otomatis masuk ke daftar alamat di akun mereka.
- [ ] **Validasi Import/Export Media Cluster:** Memastikan fungsi ekspor data (Produk, Pesanan, User, dsb.) yang berada di cluster *Media Files* berjalan dengan benar dan tersinkronisasi dengan perubahan *database* terbaru.
- [x] **Google Analytics Embed:** Menambahkan halaman *iframe* Looker Studio di Filament untuk metrik kunjungan lengkap (Traffic Source, Pageviews) dengan 0% beban server.
- [x] **Kasir Sederhana (POS Manual):** Menyempurnakan form *Create Order* di Admin Panel agar kasir *offline* bisa menginput pesanan secara cepat dan otomatis menjumlahkan harga.
- [x] **Buku Kas (Cashflow):** Mencatat *Cash In* otomatis dari pesanan lunas (via Observer & tombol Tarik Data), *Cash Out* manual oleh admin, reversal entry saat pembatalan, widget statistik dengan cache, dan filter rentang tanggal.
- [x] **Quick Edit & Log Stok:** Membuat halaman khusus untuk mengetik/mengubah jumlah stok secara langsung dilengkapi tabel *Log Keluar-Masuk* barang yang ringan.
- [x] **Dashboard Metrik Lanjutan:** Menambahkan kumpulan *Widget* statistik di *Homepage* Admin (Total Penjualan Hari Ini, Pesanan Masuk, Pengeluaran, Laba Bersih Harian) yang diolah secara *real-time* dari data pesanan dan buku kas. Dilengkapi *Line Chart* tren penjualan vs pengeluaran 30 hari terakhir dengan cache 5 menit untuk efisiensi VPS.
- [x] *Script* Backup Database otomatis di VPS CloudPanel.
