# Architecture & Technical Specification

## 1. Stack Teknologi
- **Core Framework:** Laravel 13 (PHP ^8.3) untuk mengelola *database*, logika backend, dan arsitektur *e-commerce* custom secara efisien.
- **Frontend & UI:** 
  - **Laravel Blade + Tailwind CSS v4 + Alpine.js (TALL Stack):** Digunakan untuk merancang antarmuka publik (katalog, produk, cart, checkout). Pendekatan ini dipilih untuk memaksimalkan kecepatan akses, menjaga sistem tetap ringan, dan menjamin SEO yang 100% sempurna tanpa konfigurasi ekstra.
- **Admin Dashboard:** Filament v5 (dibangun di atas Livewire & Tailwind CSS v4) digunakan murni untuk pengelolaan *backend* (produk, pesanan, manajer).
- **Media Management:** awcodes/filament-curator v5 terintegrasi langsung dengan Filament untuk sentralisasi *Media Library* berstandar WordPress.

## 2. Integrasi Layanan Eksternal (Third-Party)
- **Payment Gateway:** Xendit (Untuk pemrosesan pembayaran otomatis).
- **Pengiriman (Logistik):** RajaOngkir (Untuk perhitungan ongkos kirim otomatis).
- **Analytics & Marketing:**
  - TikTok Pixel
  - Google Analytics
  - Google Business
- **Penyimpanan Media:** Penyimpanan internal *server* dengan konversi otomatis ke format **WebP**. Layanan eksternal (CDN/Cloud) akan dipertimbangkan di masa depan hanya jika batas kapasitas penyimpanan server terpenuhi.

## 3. Filosofi Pengembangan
- **Keep it Simple & Fast:** Mengutamakan arsitektur Monorepo yang terpusat. Keranjang (Cart) dan fitur interaktif publik dikelola seminimal mungkin menggunakan Alpine.js atau Livewire agar tidak membebani sisi *client*.
- **Setir Terpisah & Murni:** Halaman admin (Filament) benar-benar terisolasi dari *frontend* publik, sehingga kinerja pelanggan saat berbelanja tidak terganggu oleh proses manajemen data.

## 4. Standar Struktur Folder & Komponen (Atomic/Modular)
Untuk menjaga agar proyek mudah di-*maintain* saat ukurannya membesar, kita menerapkan pemisahan kode (*separation of concerns*) untuk HTML (Blade), CSS, dan JS. **PENTING: Jangan simpan file statis HTML atau folder sisa slicing (seperti `cart/`, `shop/`, `contact.html`) di root directory.** Semua view murni dikelola lewat MVC Laravel (`resources/views/`).

### A. Komponen HTML (Laravel Blade)
Pemisahan struktur berada di dalam direktori `resources/views/`:
1. **Layouts (`components/layouts/`):** Kerangka utama halaman (misal: `app.blade.php` untuk publik, `auth.blade.php` untuk login).
2. **Global Components / Organisms (`components/global/`):** Bagian besar yang dipakai di banyak tempat, seperti `<x-global.navbar>`, `<x-global.footer>`, `<x-global.mobile-nav>`.
3. **UI Components / Atoms & Molecules (`components/ui/`):** Elemen-elemen kecil yang spesifik dan sering digunakan berulang, seperti `<x-ui.button>`, `<x-ui.input>`, `<x-ui.card>`.
4. **Pages:** Halaman spesifik disimpan langsung di bawah `views/` atau `views/pages/` (misal: `home.blade.php`, `shop.blade.php`). Halaman ini hanya bertugas memanggil layout dan komponen.

### B. Styling (CSS)
Sebagian besar styling menggunakan *utility classes* dari Tailwind CSS, namun jika ada *custom CSS* (seperti efek animasi atau styling plugin eksternal), pisahkan di `resources/css/`:
1. `app.css`: File utama (mengi-import file lain).
2. `base/`: Reset CSS, typography global, font-face.
3. `components/`: Custom CSS yang tidak bisa di-handle Tailwind (misal: `.btn-custom`).
4. `utilities/`: Animasi custom, override plugin.

### C. JavaScript (JS)
Berdasarkan filosofi *TALL stack*, interaktivitas ringan menggunakan Alpine.js di dalam file Blade. Namun untuk skrip JS eksternal, pisahkan di `resources/js/`:
1. `app.js`: Entry point utama.
2. `bootstrap.js`: Setup dependencies global (seperti Axios).
3. `components/`: Logika spesifik komponen (misal: slider produk, *carousel*).
4. `utils/`: Fungsi-fungsi *helper* global (format mata uang, kalkulator ongkir).

## 5. Arsitektur Database Produk & Varian
Semua aturan dan logika hierarki mengenai Stok, Harga, Produk, Varian, serta Kombinasi Atribut (Pivot) telah dipindahkan secara khusus ke dokumen **`.docs/DATABASE_SCHEMA.md`**. AI dan Developer wajib merujuk ke file tersebut.

## 6. Arsitektur Admin Dashboard (Filament)
Arsitektur Filament pada proyek ini tidak menggunakan struktur default untuk memastikan User Experience (UX) kelas Enterprise yang rapi, luas, dan modern.

### A. Konfigurasi UI & Layout
1. **Top Navigation (Navigasi Atas):** Sidebar kiri dihilangkan dan digantikan dengan navigasi bergaya *Tab* di header atas (`->topNavigation()`). Hal ini membuat area konten lebih luas.
2. **Lebar Layar Penuh (Full Width):** Menggunakan `->maxContentWidth('full')` agar tabel data tidak terperangkap di tengah layar dan memaksimalkan ruang horizontal.
3. **Tipografi Modern:** Font default telah diubah menjadi **Poppins** (`->font('Poppins')`) untuk menghilangkan kesan kaku dan memberikan nuansa *startup/Gen-Z* yang empuk dan bersahabat. Tema CSS tambahan (Custom Theme) dapat disuntikkan tanpa memodifikasi core vendor.

### B. Hierarki Menu (Sistem Cluster)
Semua menu *Resource* dan *Page* dikelompokkan menggunakan **Filament Clusters** agar tidak menumpuk:
1. **Cluster E-Commerce:**
   - *Katalog:* Produk, Kategori, Atribut, Ulasan Produk
   - *Transaksi:* Pesanan, Laporan Transaksi
   - *Reseller:* Daftar Reseller, Pengaturan Reseller
   - *Promosi:* Banner Promosi, Pengumuman (Topbar), Voucher / Diskon
   - *Pengaturan Toko:* Metode Pembayaran, Metode Pengiriman
2. **Cluster Konten:**
   - *Blog:* Artikel, Kategori, Tags, Komentar
   - *Halaman:* Laman Statis, Halaman Penjualan
3. **Cluster Pengaturan:**
   - *Manajemen Pengguna:* Pengguna, Peran (Roles)

### C. Role-Based Access Control (RBAC)
- Menggunakan **Filament Shield** (Spatie Permission) untuk manajemen otorisasi.
- **PENTING:** Konfigurasi grup untuk resource `Peran` (Role) wajib dikendalikan melalui `config/filament-shield.php`, dengan menetapkan `'cluster' => \App\Filament\Clusters\Settings\SettingsCluster::class` dan `'navigation_group' => 'Manajemen Pengguna'`. Dilarang mengaturnya langsung dari class `RoleResource` karena akan ter-override oleh sistem konfigurasi Shield.
