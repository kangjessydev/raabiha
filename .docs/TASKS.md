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
- [x] **Perbaikan Pengumuman Topbar:** Memperbaiki bug tidak muncul di frontend. Mengubah input menjadi _Rich Editor_ terbatas (hanya bold/italic/link) dengan indikator batas karakter maksimal. Jika teks terlalu panjang, aplikasikan efek _marquee_ (teks berjalan) khusus di layar kecil.

**Belum (To Do - Urutan pengerjaan _Rough Development_, dari termudah/tercepat):**

- [x] **Logika Banner Promosi (Popup):** Menambahkan fitur _Single Active Toggle_ (satu aktif, lainnya otomatis mati). Menambahkan dropdown penempatan (_Semua Halaman, Home, Katalog_).
- [x] **Sistem Voucher Lanjutan:** Mengembangkan database voucher dengan kolom: Tipe Diskon (Nominal/%), Syarat Min. Belanja, Minimal Item, Max Diskon, Spesifik User (Email JSON), Free Gift Item, Aturan Ongkir (Stacking), Aturan Reseller, dan Kuota Maksimal Penggunaan.
- [x] **Logika Ulasan Produk:** Menonaktifkan tombol 'Create' di Filament Admin (Hanya bisa dibaca/dimoderasi). Membuat tombol 'Beri Ulasan' di Dasbor Customer untuk pesanan yang sudah selesai, serta menampilkannya di Detail Produk.
- [x] **Sistem Kontak Hybrid (Inquiries):** Membuat tabel `inquiries` untuk menampung pesan _Contact Us_. Di _frontend_, pembeli bisa memilih metode kirim (Radio: _via Email_ atau _via WhatsApp_). Di _Site Settings_, admin bisa mengatur daftar Subjek beserta _toggle_ apakah subjek tersebut wajib disimpan ke _database_ atau langsung dialihkan (otomatis filterable di Filament).
- [x] **Notifikasi Real-time (Lonceng & Suara):** Mengaktifkan fitur _Database Notifications_ bawaan Filament di _navbar_ atas, dan menyisipkan _script_ Alpine.js untuk memutar suara (mp3/wav) saat ada notifikasi baru masuk.
- [x] **SEO Fields (Blog SEO, Produk SEO, Home SEO):** Menambahkan field _Meta Title & Description_ di Resource Post, Product, dan SiteSettings.
- [x] **Kategori Publik:** Mengatur hierarki kategori dan slug yang diakses lewat `/collections/{slug}`.
- [x] **Galeri Builder:** Membuat Single Page Settings menggunakan Filament Builder untuk Galeri Publik (menggantikan sistem CRUD lama) agar lebih dinamis dan modular.
- [x] **Dashboard Overview:** Menambahkan Widget statistik (Total Pendapatan, Order Baru, dll) di halaman depan Admin.
- [x] **Export & Import Data:** Memasang fungsi/plugin export-import Excel pada resource Produk dan Order.
- [x] **Manajemen Reseller (B2B):** Membuat _Tabs_ filter (Semua, Aktif, Pending) di Daftar Reseller. Proses _Approval_ manual oleh Admin. Mengatur nilai diskon _flat percentage_ untuk semua reseller aktif di _Site Settings_.
- [x] **Badge Guest Checkout:** Menambahkan label visual pada OrderResource untuk membedakan _Guest_ vs _Registered_.
- [x] **Widgets Laporan Pesanan:** Menghapus menu _Laporan Transaksi_ yang redundan dan menggantinya dengan _Header Widgets_ (Total Pending, Pendapatan Hari Ini) langsung di atas tabel Pesanan.
- [x] **Mode Libur & Jadwal Toko:** Menambahkan pengaturan "Tutup Toko / Mode Libur" di _Site Settings_ yang akan memunculkan peringatan otomatis di _frontend_ agar pembeli tahu kapan barang dikirim.
- [x] **Pengaturan Global (Site Settings):** Membuat halaman manajemen terpusat untuk Identitas Toko, Menu Navbar Dinamis (Repeater), Footer, Kontak, dan Integrasi Skrip Eksternal.
- [x] **Halaman Statis (CMS):** Membuat Resource untuk halaman legal/dokumen (T&C, Privacy Policy) lengkap dengan dukungan _SEO Fields_.
- [x] **Sales Page Builder:** Membangun _Filament Builder_ modular (Hero, Features, Testimonial) untuk membuat _Landing Page_ dinamis.

### Frontend Customer (Blade + Livewire)

**Selesai (Done):**

- Slicing UI Utama (Home, About, Contact, Dasbor Customer, Product Detail).
- Katalog Dinamis (`shop`) dengan filter pencarian.
- Transisi Galeri & Logika UI Bottom Sheet untuk pemilihan Varian & QTY.
- Dashboard Customer Ekstensif (Riwayat Pesanan, Detail Invoice, Manajemen Alamat, Voucher, Statistik).
- Sistem Blog Dinamis & Komentar interaktif dengan dukungan _Nested Replies_ dan _Auto-Polling Moderation_.
- **Halaman Pendaftaran Reseller & Welcome Onboarding:** Membuat formulir _Livewire_ dan sistem deposit awal.
- **Sistem Keranjang & Beli Sekarang:** Menyelaraskan fungsi masuk ke keranjang dan proses Checkout langsung, dengan perhitungan Diskon Reseller otomatis.
- **Sistem Checkout (Frontend):** Form pengisian alamat pengiriman dan ringkasan harga.
- **Integrasi Dashboard Reseller:** Menggabungkan profil Reseller ke dalam _Dashboard Customer_ standar.
- **Global UI Binding (Dasar):** Menghubungkan data _Site Settings_ (pengumuman, status mode libur, tautan menu, dll) ke komponen _Frontend_ secara dinamis.
- **Refactoring Hardcode Pages:** Mengoptimalkan halaman Home, About, Lokasi & Kontak.
- **Dynamic Page Rendering:** Membuat _routing_ untuk me-render _Halaman Statis_ dan _Sales Page_ (`/{slug}`).
- [x] **Penyempurnaan Global UI Binding:** Menghubungkan logo (Light & Dark Mode) dan nama web di Navbar dan Footer secara dinamis ke pengaturan **Identitas Toko** (termasuk Favicon, judul tab browser dinamis dengan slogan/SEO beranda).
- [x] **Pencarian Global Navbar (Search):** Berhasil memfungsikan ikon search di navbar agar mengarah ke halaman pencarian dinamis yang mencari produk dan artikel sekaligus.
- [x] **Logika Filter Katalog Produk:** Berhasil menyelesaikan filter Ukuran, Warna, dan Rentang Harga di halaman `/shop` agar terhubung ke Livewire/database secara real-time.

**Belum (To Do):**

- _Semua fitur frontend customer utama telah diimplementasikan._

---

## Integrasi API (Fase Berikutnya)

- [x] **Logistik (RajaOngkir by Komerce):** Menggunakan API _Shipping Cost_ Komerce untuk cek ongkir (JNE/POS/TIKI). Termasuk pembuatan antarmuka di `Pengaturan Checkout` untuk _Origin City_ dan kurir aktif.
- [x] **Pembayaran (Xendit / Tripay):** Sinkronisasi Virtual Account, QRIS, e-Wallet otomatis dengan _Webhook_ integrasi ke status pesanan, serta _Dual-Gateway Switcher_ di Admin Panel.

## Backlog / Ekstensi Bisnis (Sprint 3 / Fase 3)

- [x] **Fitur Wishlist Pelanggan:** Membuat tabel `wishlists` (user_id, product_id) dan fitur simpan produk untuk pengguna terdaftar yang dapat diakses langsung dari halaman katalog dan dashboard akun.
- [ ] **Fitur Login & Register Customer (Frontend):** Membuat formulir pendaftaran (Register) dan masuk (Login) untuk pelanggan di frontend (mendukung email & password, opsi Google OAuth), serta pengiriman email konfirmasi/verifikasi.
- [x] **Role & Manajemen Akses Lanjutan (Spatie):**
    - Mendefinisikan hak akses CRUD untuk Owner, Marketing, Finance, Logistics, CS, dan Admin Kasir.
    - Membatasi akses menu _Integrasi & API_ khusus untuk Super Admin saja.
    - [x] **Uji Coba Role:** Uji coba login menggunakan akun demo (Owner, dll) untuk memverifikasi fungsionalitas pembatasan UI secara langsung telah selesai dilaksanakan.
    - [x] **Sistem Pengajuan Perubahan/Pembatalan Pesanan Kasir:** Membuat tabel dan alur pengajuan persetujuan ke Owner agar Kasir dapat melakukan perubahan/pembatalan pesanan secara aman.
- [x] **Fitur Refund Pelanggan:** Merancang tabel `refund_requests` dan alur antarmuka (UI) khusus di Dasbor Customer & Admin Panel untuk menangani pengembalian dana.
- [x] **Simpan Alamat Otomatis di Checkout:** Menambahkan _checkbox_ kecil ("Simpan alamat ini ke akun saya") di halaman Checkout khusus bagi pelanggan yang belum memiliki alamat tersimpan, agar alamat otomatis masuk ke daftar alamat di akun mereka.
- [x] **Validasi Import/Export Media Cluster:** Memastikan fungsi ekspor data (Produk, Pesanan, User, dsb.) yang berada di cluster _Media Files_ berjalan dengan benar dan tersinkronisasi dengan perubahan _database_ terbaru.
- [x] **Google Analytics Embed:** Menambahkan halaman _iframe_ Looker Studio di Filament untuk metrik kunjungan lengkap (Traffic Source, Pageviews) dengan 0% beban server.
- [x] **Kasir Sederhana (POS Manual):** Menyempurnakan form _Create Order_ di Admin Panel agar kasir _offline_ bisa menginput pesanan secara cepat dan otomatis menjumlahkan harga.
- [x] **Buku Kas (Cashflow):** Mencatat _Cash In_ otomatis dari pesanan lunas (via Observer & tombol Tarik Data), _Cash Out_ manual oleh admin, reversal entry saat pembatalan, widget statistik dengan cache, dan filter rentang tanggal.
- [x] **Quick Edit & Log Stok:** Membuat halaman khusus untuk mengetik/mengubah jumlah stok secara langsung dilengkapi tabel _Log Keluar-Masuk_ barang yang ringan.
- [x] **Dashboard Metrik Lanjutan:** Menambahkan kumpulan _Widget_ statistik di _Homepage_ Admin (Total Penjualan Hari Ini, Pesanan Masuk, Pengeluaran, Laba Bersih Harian) yang diolah secara _real-time_ dari data pesanan dan buku kas. Dilengkapi _Line Chart_ tren penjualan vs pengeluaran 30 hari terakhir dengan cache 5 menit untuk efisiensi VPS.
- [x] _Script_ Backup Database otomatis di VPS CloudPanel.
- [ ] **File Impor Produk:** Menyiapkan file template impor produk (.xlsx / .csv) yang lengkap dan siap digunakan untuk migrasi data massal.
- [ ] **Sistem Notifikasi Email Otomatis:** Mengimplementasikan kelas Mailable Laravel untuk mengirim email otomatis pada kejadian berikut:
    - **Untuk Customer:**
        - Registrasi User Baru (Welcome Email & Link Verifikasi).
        - Pembuatan Pesanan Baru (Konfirmasi Order & Detail Pembayaran).
        - Pembayaran Berhasil (Tanda Terima Pembayaran/Kuitansi).
        - Pesanan Gagal/Dibatalkan (Pemberitahuan Pembatalan).
        - Pesanan Dikirim (Notifikasi Nomor Resi & Status Pengiriman).
    - **Untuk Admin/Tim Toko:**
        - Notifikasi Pesanan Baru Masuk (Untuk CS & Owner).
        - Notifikasi Pembayaran Diterima (Untuk Finance).
        - Notifikasi Permintaan Perubahan/Pembatalan Baru dari Kasir (Untuk Owner).

---

## 🚀 Fase Rilis & Optimasi (VPS & SEO)

- [ ] **Deploy ke VPS:** Melakukan migrasi database, SSL setup, build assets, dan deployment aplikasi Raabiha ke CloudPanel VPS agar dapat diakses publik.
- [ ] **Daftar Search Console:** Mendaftarkan sitemap e-commerce ke Google Search Console untuk memastikan indeksasi halaman produk dan artikel.
- [ ] **Daftar Google Bisnisku (Google Business):** Membuat profil Google Business Profil (GBP) resmi untuk meningkatkan SEO lokal.
- [ ] **Cek Performance & Core Web Vitals:** Melakukan audit performa frontend via Google PageSpeed Insights dan optimasi gambar/asset jika diperlukan.
