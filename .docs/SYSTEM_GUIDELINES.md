# Raabiha E-Commerce: Master System Guidelines & Documentation

Selamat datang di dokumentasi induk proyek Raabiha E-Commerce. Dokumen ini menggabungkan seluruh panduan arsitektur, standar coding, keamanan, integrasi, dan operasional proyek ke dalam satu kesatuan panduan terpadu.

---

## Daftar Isi
- [1. AI Persona & Role Definition](#1-ai-persona--role-definition)
- [2. Product Requirements Document (PRD)](#2-product-requirements-document-prd)
- [3. Architecture & Technical Specification](#3-architecture--technical-specification)
- [4. Arsitektur Database & Skema](#4-arsitektur-database--skema)
- [5. Integrasi API Pihak Ketiga](#5-integrasi-api-pihak-ketiga)
- [6. UI & Design System Guidelines](#6-ui--design-system-guidelines)
- [7. Security & Access Control Rules](#7-security--access-control-rules)
- [8. Panduan Instalasi & Persiapan Proyek (Setup Guide)](#8-panduan-instalasi--persiapan-proyek-setup-guide)
- [9. Git Workflow & Syarat Pengembangan](#9-git-workflow--syarat-pengembangan)
- [10. Deployment & Build Strategy](#10-deployment--build-strategy)

---

## 1. AI Persona & Role Definition

Dokumen ini berfungsi sebagai instruksi mutlak (System Prompt) bagi setiap model AI yang berinteraksi dengan proyek Raabiha E-Commerce. Sebelum memulai sesi atau melakukan perubahan kode, AI WAJIB meresapi peran ini.

### 1.1 Hubungan Kerja
- **Manusia (User):** Bertindak sebagai **CTO (Chief Technology Officer)** dan Pemilik Produk. User adalah pemberi arah visi, pengambil keputusan akhir untuk arsitektur, dan penentu strategi bisnis.
- **Kamu (Sistem AI):** Berperan sebagai 3 entitas sekaligus:
  1. **Assistant CTO:** Memberikan saran arsitektur tingkat tinggi, meninjau implikasi keamanan sistem, dan mempertimbangkan batasan infrastruktur (seperti VPS 2GB RAM / 20GB SSD).
  2. **Project Manager:** Mengelola dan memperbarui papan tugas (`TASKS.md`), memastikan proyek tetap fokus pada prioritas MVP, dan mencegah proyek melebar (*scope creep*).
  3. **Senior Full-Stack Programmer:** Mengeksekusi penulisan kode dengan standar *Production*. Sangat teliti, memastikan kode aman (berkiblat pada aturan keamanan), dan ketat dalam hal estetika antarmuka (berkiblat pada panduan UI).

### 1.2 Aturan Komunikasi & Eksekusi AI
- **Kritis dan Proaktif:** Jangan sekadar menuruti perintah layaknya robot pasif. Jika CTO menyarankan pendekatan yang berisiko menimbulkan *bug*, lambat, atau melanggar aturan keamanan, AI **wajib** memberikan peringatan dan menawarkan alternatif solusi yang lebih stabil.
- **Pemahaman Konteks (Wajib Baca):** Di setiap awal interaksi atau sebelum menulis kode untuk fitur baru, AI harus meninjau semua file di folder `.docs/` untuk memahami *state* proyek.
- **Riset MCP Server (Wajib):** Sebelum memulai koding untuk framework (seperti Laravel, Filament, Livewire, Tailwind), AI **wajib** verifikasi sintaks dan dokumentasi terbaru menggunakan **Context7** melalui MCP Server. Hal ini untuk mencegah kode *usang* (*deprecated*).
- **Eksekusi Bertahap (Iteratif):** Kerjakan satu tugas/file dalam satu waktu. Pastikan CTO menyetujui pengujian sebelum melompat ke tugas berikutnya.
- **Disiplin Desain:** AI harus secara insting membedakan mana yang merupakan desain operasional staf (Filament Admin) dan mana yang merupakan desain wajah publik (Blade/Tailwind), serta tidak pernah menukar-nukar gaya tersebut.
- **Kewajiban Update Dokumentasi:** Setiap kali ada penambahan fitur besar, perubahan *Tech Stack*, penambahan tabel *Database*, atau integrasi API baru, AI **wajib secara otomatis** memperbarui file markdown di folder `.docs/` yang relevan untuk memastikan dokumentasi tetap sinkron dengan kode.

### 1.3 Resolusi Masalah (Debugging)
- Jika ada *bug* (seperti gagal render, *timeout*, atau masalah *styling*), investigasi secara terukur (baca log, periksa DOM, cek respons jaringan) sebelum menebak solusinya.
- Taati alur kerja pengembangan: Bangun dan uji logika inti di `Filament Admin` (Backend) terlebih dahulu, baru buatkan "setir" antarmukanya di `Frontend Publik` (Blade/Livewire).

---

## 2. Product Requirements Document (PRD)

### 2.1 Latar Belakang & Tujuan
Raabiha e-commerce dibangun sebagai solusi strategis untuk mengurangi ketergantungan pada *marketplace* pihak ketiga (seperti TikTok Shop dan Shopee) yang memiliki potongan biaya admin yang semakin membengkak. 
Tujuan utamanya adalah meningkatkan margin keuntungan penjualan langsung (Direct-to-Consumer) sekaligus memberikan penawaran eksklusif (seperti sistem *Reseller* dan promo khusus) yang tidak tersedia di *marketplace*.

### 2.2 Target Audiens
- **Demografi:** Wanita muslim.
- **Usia:** 18 - 30 tahun.
- **Segmen:** Semua kalangan (inklusif).

### 2.3 Fitur Utama (Fase 1 - MVP)
Fase MVP mengacu pada pembuatan kapabilitas *e-commerce* secara custom dari nol menggunakan Laravel, disesuaikan persis dengan kebutuhan Raabiha agar lebih ringan, cepat, dan mudah di-maintain.
- **Katalog & Belanja:** Etalase produk lengkap dengan struktur database custom.
- **Sistem Pengiriman:** Cek ongkir otomatis & fitur gratis ongkir (Integrasi RajaOngkir).
- **Pembayaran:** *Payment Gateway* yang terotomatisasi (Integrasi Tripay / Xendit).
- **Manajemen Pengguna:** Dasbor pelanggan publik yang SEO-friendly dan ringan.
- **Dasbor Admin Eksekutif:** Menggunakan **Filament Admin Panel** sebagai pusat komando operasional toko (manajemen pesanan, produk, user, dan laporan) yang terpisah dari area publik.

### 2.4 Fitur Masa Depan (Fase 2)
- **Virtual Fitting Room (Simulasi Ganti Baju):** 
  - Konsep *gamification* mirip kustomisasi karakter.
  - Pengguna dapat mengunggah *selfie* agar wajahnya diterapkan pada avatar.
  - Kustomisasi ukuran tubuh yang presisi (tinggi badan, berat badan, lingkar dada, perut, pinggang, dll).
  - Pengguna dapat mencoba pakaian dari katalog Raabiha pada avatar mereka.
  - Sistem rekomendasi ukuran dan gaya otomatis berdasarkan preferensi bentuk tubuh pengguna.

---

## 3. Architecture & Technical Specification

### 3.1 Stack Teknologi
- **Core Framework:** Laravel 13 (PHP ^8.3) untuk mengelola *database*, logika backend, dan arsitektur *e-commerce* custom secara efisien.
- **Frontend & UI:** 
  - **Laravel Blade + Tailwind CSS v4 + Alpine.js (TALL Stack):** Digunakan untuk merancang antarmuka publik (katalog, produk, cart, checkout). Pendekatan ini dipilih untuk memaksimalkan kecepatan akses, menjaga sistem tetap ringan, dan menjamin SEO yang 100% sempurna tanpa konfigurasi ekstra.
- **Admin Dashboard:** Filament v5 (dibangun di atas Livewire & Tailwind CSS v4) digunakan murni untuk pengelolaan *backend* (produk, pesanan, manajer).
- **Media Management:** awcodes/filament-curator v5 terintegrasi langsung dengan Filament untuk sentralisasi *Media Library* berstandar WordPress.

### 3.2 Integrasi Layanan Eksternal (Third-Party)
- **Payment Gateway:** Tripay (Untuk pemrosesan pembayaran otomatis seperti Virtual Account, QRIS, Retail).
- **Pengiriman (Logistik):** RajaOngkir (Untuk perhitungan ongkos kirim otomatis).
- **Analytics & Marketing:**
  - TikTok Pixel
  - Google Analytics
  - Google Business
- **Penyimpanan Media:** Penyimpanan internal *server* dengan konversi otomatis ke format **WebP**. Layanan eksternal (CDN/Cloud) akan dipertimbangkan di masa depan hanya jika batas kapasitas penyimpanan server terpenuhi.

### 3.3 Filosofi Pengembangan
- **Keep it Simple & Fast:** Mengutamakan arsitektur Monorepo yang terpusat. Keranjang (Cart) dan fitur interaktif publik dikelola seminimal mungkin menggunakan Alpine.js atau Livewire agar tidak membebani sisi *client*.
- **Setir Terpisah & Murni:** Halaman admin (Filament) benar-benar terisolasi dari *frontend* publik, sehingga kinerja pelanggan saat berbelanja tidak terganggu oleh proses manajemen data.

### 3.4 Standar Struktur Folder & Komponen (Atomic/Modular)
Untuk menjaga agar proyek mudah di-*maintain* saat ukurannya membesar, kita menerapkan pemisahan kode (*separation of concerns*) untuk HTML (Blade), CSS, dan JS. **PENTING: Jangan simpan file statis HTML atau folder sisa slicing (seperti `cart/`, `shop/`, `contact.html`) di root directory.** Semua view murni dikelola lewat MVC Laravel (`resources/views/`).

#### A. Komponen HTML (Laravel Blade)
Pemisahan struktur berada di dalam direktori `resources/views/`:
1. **Layouts (`components/layouts/`):** Kerangka utama halaman (misal: `app.blade.php` untuk publik, `auth.blade.php` untuk login).
2. **Global Components / Organisms (`components/global/`):** Bagian besar yang dipakai di banyak tempat, seperti `<x-global.navbar>`, `<x-global.footer>`, `<x-global.mobile-nav>`.
3. **UI Components / Atoms & Molecules (`components/ui/`):** Elemen-elemen kecil yang spesifik dan sering digunakan berulang, seperti `<x-ui.button>`, `<x-ui.input>`, `<x-ui.card>`.
4. **Pages:** Halaman spesifik disimpan langsung di bawah `views/` atau `views/pages/` (misal: `home.blade.php`, `shop.blade.php`). Halaman ini hanya bertugas memanggil layout dan komponen.

#### B. Styling (CSS)
Sebagian besar styling menggunakan *utility classes* dari Tailwind CSS, namun jika ada *custom CSS* (seperti efek animasi atau styling plugin eksternal), pisahkan di `resources/css/`:
1. `app.css`: File utama (mengi-import file lain).
2. `base/`: Reset CSS, typography global, font-face.
3. `components/`: Custom CSS yang tidak bisa di-handle Tailwind (misal: `.btn-custom`).
4. `utilities/`: Animasi custom, override plugin.

#### C. JavaScript (JS)
Berdasarkan filosofi *TALL stack*, interaktivitas ringan menggunakan Alpine.js di dalam file Blade. Namun untuk skrip JS eksternal, pisahkan di `resources/js/`:
1. `app.js`: Entry point utama.
2. `bootstrap.js`: Setup dependencies global (seperti Axios).
3. `components/`: Logika spesifik komponen (misal: slider produk, *carousel*).
4. `utils/`: Fungsi-fungsi *helper* global (format mata uang, kalkulator ongkir).

### 3.5 Arsitektur Admin Dashboard (Filament)
Arsitektur Filament pada proyek ini tidak menggunakan struktur default untuk memastikan User Experience (UX) kelas Enterprise yang rapi, luas, dan modern.

#### A. Konfigurasi UI & Layout
1. **Top Navigation (Navigasi Atas):** Sidebar kiri dihilangkan dan digantikan dengan navigasi bergaya *Tab* di header atas (`->topNavigation()`). Hal ini membuat area konten lebih luas.
2. **Lebar Layar Penuh (Full Width):** Menggunakan `->maxContentWidth('full')` agar tabel data tidak terperangkap di tengah layar dan memaksimalkan ruang horizontal.
3. **Tipografi Modern:** Font default telah diubah menjadi **Poppins** (`->font('Poppins')`) untuk menghilangkan kesan kaku dan memberikan nuansa *startup/Gen-Z* yang empuk dan bersahabat. Tema CSS tambahan (Custom Theme) dapat disuntikkan tanpa memodifikasi core vendor.

#### B. Real-Time Interactivity (Auto-Polling)
Tabel data krusial yang membutuhkan pemantauan langsung (*real-time*) seperti **Produk**, **Artikel Blog**, dan **Komentar Blog** dikonfigurasi dengan fitur `->poll('5s')`. Fitur ini berbasis Livewire untuk memperbarui data (*refresh*) otomatis setiap 5 detik tanpa perlu me-reload halaman browser secara penuh, memastikan tim admin selalu melihat aliran data terbaru tanpa disrupsi UI.

#### C. Hierarki Menu (Sistem Cluster)
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

#### D. Role-Based Access Control (RBAC)
- Menggunakan **Filament Shield** (Spatie Permission) untuk manajemen otorisasi.
- **PENTING:** Konfigurasi grup untuk resource `Peran` (Role) wajib dikendalikan melalui `config/filament-shield.php`, dengan menetapkan `'cluster' => \App\Filament\Clusters\Settings\SettingsCluster::class` dan `'navigation_group' => 'Manajemen Pengguna'`. Dilarang mengaturnya langsung dari class `RoleResource` karena akan ter-override oleh sistem konfigurasi Shield.

---

## 4. Arsitektur Database & Skema

Arsitektur database untuk produk dan varian dirancang dengan aturan mutlak sebagai berikut untuk mengakomodasi fleksibilitas e-commerce:

### 4.1 Hirarki Stok & Ketersediaan
- **Produk Tanpa Varian (`has_variants = false`):**
  - Stok fisik dicatat langsung di tabel utama `products` (kolom `stock`).
  - Total stok produk = `products.stock`.
- **Produk Dengan Varian (`has_variants = true`):**
  - Kolom `products.stock` akan diabaikan/disembunyikan dari antarmuka Admin.
  - Stok fisik dicatat secara individual pada setiap baris di tabel `product_variants` (kolom `stock`).
  - Total stok produk adalah akumulasi (SUM) dari seluruh `product_variants.stock` yang terkait.

### 4.2 Hirarki Harga (Pricing Override)
1. Harga dasar/default dan harga reseller utama selalu dicatat di tabel `products`.
2. Jika sebuah varian memiliki harga yang sama dengan harga dasar produk, maka *toggle* `is_price_override` diatur ke `false`. Kolom `price` pada tabel `product_variants` dapat dibiarkan kosong (`null`), dan sistem **wajib** mengambil (fallback) harga dari tabel `products`.
3. Jika sebuah varian memiliki harga yang berbeda (lebih mahal/murah), *toggle* `is_price_override` diatur ke `true`. Sistem wajib membaca harga final dari kolom `price` di tabel `product_variants`.

### 4.3 Logika Kombinasi Atribut (Pivot)
- Varian adalah **satu kesatuan barang fisik** (memiliki SKU, Stok, Harga).
- Kombinasi Atribut (seperti "Warna: Merah", "Ukuran: XL") adalah sekadar **Label/Tag** yang menempel pada Varian tersebut.
- Relasi antara *Varian Fisik* dengan *Opsi Atribut* disambungkan melalui tabel pivot `attribute_option_product_variant` (memiliki primary key `id`).
- Form input Filament menggunakan metode *Nested Repeater* (Cascading Dropdown) untuk menempelkan Label/Tag pada Varian, tetapi **DILARANG KERAS** memindahkan kolom `stok` atau `harga` ke dalam tabel pivot/kombinasi atribut tersebut. Harga dan stok mutlak milik entitas Varian.

### 4.4 Arsitektur Keranjang (Cart)
Berdasarkan keputusan CTO, keranjang belanja wajib menggunakan pendekatan **Database Storage (Opsi A)**:
- **Tabel `carts` dan `cart_items`:** Digunakan untuk menyimpan sesi keranjang agar *persistent* (tersimpan meski ganti *device* jika login).
- **Guest vs Registered User:** Jika *user* login, cart diikat ke `user_id`. Jika *user* adalah tamu (Guest), cart diikat ke `session_id` di database. Jika Guest kemudian login, sistem wajib "menyatukan" (*merge*) keranjang Guest tersebut ke akun *user* yang bersangkutan.
- **Isolasi "Beli Sekarang":** Fitur klik "Beli Sekarang" (langsung ke *checkout* 1 barang tanpa menghapus isi keranjang reguler) dikelola menggunakan kolom boolean `is_buy_now` pada tabel keranjang, atau disimpan di dalam *session* khusus yang tidak menyentuh `cart_items` utama.

### 4.5 Arsitektur Pesanan (Order) & Guest Checkout
- **Fleksibilitas Checkout:** Pelanggan tidak diwajibkan mendaftar akun (*Guest Checkout Allowed*).
- **Relasi Database `orders`:** Tabel pesanan harus mengizinkan `user_id` menjadi `nullable`. Jika `user_id` bernilai `null`, pesanan diidentifikasi sebagai pesanan Guest.
- **Pembedaan Admin Panel:** Di Filament Admin, tabel/resource Orders **wajib** memiliki *badge* atau filter visual untuk membedakan secara jelas mana transaksi "User Terdaftar" dan mana transaksi "Guest".
- **Pelacakan (Tracking):** User yang login dapat melihat riwayat di Dasbor Pelanggan. Guest akan melacak pesanan menggunakan kombinasi "ID Pesanan" dan "Email" secara manual di halaman publik "Track Order".

### 4.6 Manajemen Alamat Pelanggan (Address Book)
- Pelanggan (User) dapat memiliki banyak alamat yang disimpan dalam tabel `user_addresses`.
- **Primary Address:** Menggunakan boolean `is_primary` untuk menandai alamat utama. Sistem harus memastikan hanya ada maksimal 1 alamat utama per user (jika alamat baru diset utama, yang lama harus di-set `false`).
- Alamat ini hanya berlaku untuk *User Login* dan dipersiapkan untuk fitur *autofill* saat Checkout. Guest tidak menggunakan tabel ini.

### 4.7 Arsitektur Komentar Blog
- **Tabel `post_comments`:** Digunakan untuk menyimpan interaksi diskusi pada setiap artikel (`post_id`).
- **Autentikasi Dinamis:** Mendukung *Registered User* (menggunakan `user_id`) dan *Guest* (menggunakan `guest_name` dan `guest_email`).
- **Diskusi Bersarang (Nested Replies):** Menggunakan `parent_id` untuk menghubungkan komentar balasan (*reply*) ke komentar induk. Saat ini mendukung hierarki dasar satu tingkat.
- **Moderasi & Transparansi Edit:**
  - `is_approved`: Secara *default* diset `true` (langsung tayang) agar tidak mematikan *engagement*, namun tetap bisa ditolak/dihapus (*Reject/Delete*) dari panel admin.
  - `is_edited_by_admin` & `admin_edit_reason`: Jika admin menyensor atau mengubah isi teks komentar pengunjung, sistem wajib melacaknya dan menampilkan alasan pengubahan (*Edit Reason*) tersebut di *frontend* secara transparan.

### 4.8 Arsitektur Media & Gambar (Filament Curator)
- Pengelolaan gambar (seperti foto produk) menggunakan **Filament Curator**.
- Kolom `images` pada tabel `products` menggunakan tipe `json`, tetapi isinya adalah **Array of Integers (Media IDs)** yang berelasi ke tabel `media` milik Curator.
- Pada tampilan *Front End*, URL gambar harus di-*resolve* secara manual dari model `Media` berdasarkan ID yang ada di kolom JSON tersebut, dengan *fallback* untuk format *string path* lama agar kompatibel dengan data sebelum migrasi Curator.

### 4.9 Arsitektur Voucher & Promosi
Tabel `vouchers` dikembangkan dengan logika berlapis untuk mencegah kerugian toko (double discount):
- **Tipe Diskon:** Mendukung potongan nominal tetap (`fixed`) dan persentase (`percent`) dengan batas maksimal (`max_discount`).
- **Batasan Minimal:** Bisa disyaratkan berdasarkan nominal transaksi (`min_purchase`) maupun jumlah barang fisik di keranjang (`min_items`).
- **Target Potongan (Stacking Rules):** 
  - Secara bawaan, sebuah pesanan hanya boleh menggunakan maksimal **1 voucher diskon belanja**.
  - Terdapat fitur `is_shipping_voucher`. Jika `true`, voucher ini memotong biaya logistik (ongkir). Voucher ini **diizinkan digabung (stackable)** dengan 1 voucher diskon belanja biasa.
- **Proteksi Reseller:** Terdapat toggle `exclude_resellers` (default `true`). Jika aktif, pelanggan dengan role Reseller **tidak diizinkan** menggunakan voucher ini, mengingat mereka sudah menerima sistem diskon persentase bawaan dari `Site Settings`.

### 4.10 Rencana Pengembangan Skema (Masa Depan)
*Dokumentasi ini wajib diperbarui setiap kali ada penambahan tabel atau perubahan struktur, seperti sistem ongkir (Shipping) dan Pembayaran (Payments).*

---

## 5. Integrasi API Pihak Ketiga

File ini akan digunakan sebagai pusat dokumentasi untuk mengelola interaksi sistem dengan layanan eksternal.

### 5.1 RajaOngkir
- **Tujuan:** Menghitung ongkos kirim otomatis berdasarkan alamat *Customer*.
- **Endpoint/Webhook:** (Belum diintegrasikan)
- **Logika Sistem:**
  - Cache hasil pencarian biaya kirim untuk wilayah yang sering dicari.

### 5.2 Tripay / Xendit Payment Gateway
*(Sudah Diintegrasikan dengan Tripay)*
- **Tujuan:** Membuat Virtual Account, QRIS, atau metode pembayaran otomatis.
- **Endpoint/Webhook:** `POST /webhook/tripay`
- **Logika Sistem:**
  - Saat pembeli mengklik "Bayar Sekarang", sistem membuat *order* (Status: PENDING) dan memanggil API Tripay (`/transaction/create`).
  - Pembeli diarahkan ke *Payment Page* Tripay (`checkout_url`).
  - Webhook Tripay akan dipanggil oleh server Tripay jika status pembayaran berubah. Skrip webhook akan memvalidasi *signature* HMAC SHA256 menggunakan `TRIPAY_PRIVATE_KEY` dan otomatis memperbarui `payment_status` dan `status` pesanan menjadi PAID atau CANCELLED.

---

## 6. UI & Design System Guidelines

Dokumen ini berisi kesepakatan aturan desain (*Design Rules*) untuk menjaga konsistensi antarmuka UI/UX di seluruh ekosistem Raabiha. **Aturan ini wajib ditaati di setiap update/perubahan komponen agar tidak terjadi miskomunikasi UI.**

### Kebijakan Bahasa (Language Policy)
*   **Customer & Admin UI**: Wajib menggunakan **Bahasa Indonesia** yang baku, sopan, dan jelas untuk semua elemen yang dilihat oleh pengguna akhir (Customer) maupun pengelola toko (Admin). Contoh: tombol "Add to Cart" menjadi "Masukkan Keranjang", "Checkout" menjadi "Bayar Sekarang", "Dashboard" menjadi "Dasbor", dsb.
*   **Source Code & Database**: Penamaan *variable*, fungsi, tabel database, kolom, dan *schema* tetap menggunakan **Bahasa Inggris** (contoh: `Product`, `created_at`, `getOrderItems()`) agar mengikuti standar global *framework* Laravel.

### 6.1 Dashboard Admin (Internal / Staff Area)
Area ini khusus untuk Administrator, Owner, Kasir, dan Manajer.
*   **Ruang Lingkup**: Semua komponen manajer (`ProductManager`, `PageManager`, `UserManager`, `OrderManager`, dll).
*   **Style Rules**:
    *   **Container**: Wajib menggunakan *class* `.raabiha-card` (ujung melengkung `rounded-xl`, `shadow-sm`, *background* putih). Hindari penggunaan `border` kasar buatan sendiri.
    *   **Typography**: *Heading* halaman harus besar dan jelas (`font-sans text-[28px] md:text-[32px] font-bold text-[#1e1e1e]`).
    *   **Buttons**: Wajib memanggil utilitas global `.btn-primary` (Warna biru utama) dan `.btn-secondary` (Teks dengan *border* putih/abu).
    *   **Tabel & Responsivitas (Mobile-First)**:
        *   Tabel dan filter *toolbar* wajib dibungkus `overflow-x-auto` agar bisa di-*swipe* ke samping di HP.
        *   Tabel kolom dilarang gepeng (*squished*). Wajib diset `min-w` pada judul kolom.
        *   Beri jarak `pl-3` pada teks header setelah kotak *checkbox*.
        *   **No Hover Trap**: Tombol "Aksi" (Hapus/Edit) harus **selalu muncul di layar HP** (opacity 100%). Efek muncul-saat-hover HANYA berlaku di mode Desktop.

### 6.2 Halaman Publik & Dashboard Customer (External / Public Area)
Area ini mencakup wajah depan toko, tempat pelanggan berbelanja dan mengelola akun mereka.
*   **Ruang Lingkup**: Homepage, Katalog Produk, Single Product, Cart, Checkout, dan halaman **My Account (Dashboard Customer)**.
*   **Style Rules**:
    *   **Estetika**: Wajib menggunakan pedoman visual yang sama dengan *Homepage* yang sudah dibuat sebelumnya (Tema *Charcoal/Inverse-Surface*, *Minimalist Luxury*, gaya elegan nan modern).
    *   **UX Pendekatan**: Fokus pada visual produk (gambar berukuran proporsional), jarak (*whitespace*) yang lega, tipografi yang *clean*, dan interaksi pembeli yang intuitif.
    *   **Larangan**: Dilarang keras membawa gaya kaku "Dashboard Admin" (seperti tabel `.raabiha-card` di atas) ke dalam halaman akun pelanggan (*My Account*). Dashboard pelanggan harus terasa menyatu dan natural dengan gaya toko e-commerce.
    *   **Mobile App-Like Experience**: Khusus di layar *mobile* (HP), interaksi perpindahan ke *child page* (contoh: dari Katalog Halaman Utama ke halaman Detail Produk) **wajib meniru *native app***. Ini meliputi:
        *   **Sub Navigation Bar (Header & Topbar)**:
            *   Menggantikan header utama dengan navbar khusus (ada panah *Back* di sebelah kiri dan judul halaman di tengah).
            *   **Announcement Marquee**: Sembunyikan bar teks berjalan di bagian atas agar tidak memadati layar pertama.
            *   **Transparent Subnav**: Navbar sub-navigation wajib bersifat transparan (`bg-transparent` dan `border-none`) serta diposisikan melayang di atas gambar (`absolute top-0 left-0 w-full z-50`).
            *   **Floating Capsules**: Tombol kembali (*back arrow*) dan judul halaman wajib dibungkus kapsul lingkaran/oval semi-transparan putih dengan efek blur (`bg-white/80 backdrop-blur-sm shadow-sm`) untuk memastikan keterbacaan di atas gambar produk apa pun.
            *   **Wishlist Icon in Header**: Khusus mobile, ikon love (wishlist) diposisikan di sisi kanan navbar (`:wishlist="true"`) sehingga membentuk keseimbangan tata letak: Kiri = Back button, Tengah = Judul halaman, Kanan = Wishlist.
        *   **Product Gallery & Hero Image (Mobile)**:
            *   **Aspect Ratio**: Gambar utama wajib berukuran sama sisi (1:1 / `aspect-square`) untuk mencegah gambar memanjang ke bawah dan menutupi thumbnail galeri di bawahnya pada pandangan pertama.
            *   **Full-Width (Edge-to-Edge)**: Gambar utama diposisikan langsung nempel ke bagian paling atas layar (`pt-0` pada layout induk) dan memenuhi lebar penuh layar (`w-[calc(100%+3rem)] -mx-6` untuk melompati padding default container).
            *   **Repositioned Badges**: Lencana status produk (seperti "NEW ARRIVAL" atau "SALE") diletakkan secara horizontal (menyamping) di sudut kiri bawah di dalam kontainer gambar utama (`absolute bottom-4 left-4 flex-row gap-2`) pada mobile, bukan vertikal atau menumpuk di atas.
            *   **Bleed Thumbnail Gallery**: Galeri gambar kecil (thumbnails) diatur nempel ke tepi layar (`w-[calc(100%+3rem)] -mx-6` dengan padding `px-6` pada track) sehingga tombol panah next/previous berada di ujung kanan-kiri layar, membuat visual galeri terasa lega.
        *   **Sticky Bottom Action Bar**:
            *   Bilah aksi pembelian mobile (`CHAT`, `CART`, `BELI SEKARANG`) wajib menempel (*sticky*) di bawah layar HP (`fixed bottom-0 left-0 w-full z-[99]`).
            *   *Catatan CSS*: Pastikan bilah aksi ini diposisikan di tingkat root DOM (di luar kontainer transisi `.page-slide-in` atau kontainer transformatif lainnya) agar posisi `fixed` tidak terganggu oleh konteks koordinat lokal.
        *   **Hiding Global Footer & Newsletter**:
            *   Seluruh bagian footer global (baik blok pendaftaran newsletter hitam besar maupun footer hak cipta/link minimalis) **wajib disembunyikan sepenuhnya di mobile** pada halaman sub-navigation ini untuk menghindari redundansi dan bentrokan tata letak dengan bilah aksi *sticky*.
        *   **Slide Transition**: Efek transisi antar halaman harus memiliki nuansa *slide-in* (menggeser dari kanan ke kiri) atau animasi halus lainnya agar tidak terkesan patah-patah seperti website tradisional.
    *   **Global UI & UX Principles**:
        *   **Visual Hierarchy (Focal Point)**: Setiap halaman harus memiliki aksi utama (CTAs seperti Beli, Bayar, dsb) yang mendominasi secara visual. Dilarang mencampuradukkan tombol utama dengan elemen sekunder di area atau baris yang sama.
        *   **Clear Labeling & Grouping**: Setiap fungsi atau input (Size, Color, QTY, Filter, dsb) wajib dikelompokkan dalam baris/area terpisah dan harus selalu disertai label penjelasan yang seragam (misal: `QUANTITY`, `SELECT SIZE`).
        *   **Generous Whitespace & Proportion**: Hindari desain yang berdesakan (*cramped*). Pastikan ada ruang kosong (*whitespace*) yang cukup antar elemen. Tombol aksi sekunder harus menggunakan *styling* yang lebih ringan (misal: tombol putih dengan *border* hitam) agar tidak menyaingi fokus utama. Aturan ini berlaku untuk *seluruh* elemen antarmuka di semua halaman, bukan hanya di detail produk.

---

## 7. Security & Access Control Rules

*Panduan penting yang wajib diikuti oleh sistem dan AI saat melakukan pemrograman.*

### 7.1 Manajemen Akses & Role (RBAC)
Sistem ini menggunakan pembagian *role* yang ekstensif. Setiap pembuatan fitur atau halaman **wajib** memeriksa kapabilitas pengguna.
Daftar Role yang ada di sistem:
1. **Administrator** (Akses Penuh)
2. **Co-Administrator** (Akses Terbatas ke sistem inti)
3. **Store Manager** (Pengelolaan operasional toko)
4. **Finance** (Pengelolaan laporan keuangan dan pencairan)
5. **Admin Kasir** (Pembuatan pesanan/Point of Sale)
6. **Stock Manager** (Manajemen inventaris barang)
7. **Customer** (Pelanggan biasa)

### 7.2 Autentikasi & Pendaftaran
- **Open Registration:** Sistem pendaftaran terbuka untuk umum.
- **SSO (Single Sign-On):** Mendukung login menggunakan Email standar dan **Google Login**.
- *Catatan Developer:* Pastikan endpoint API pendaftaran terlindungi dari *spam bot* (misal: dengan verifikasi token atau CAPTCHA/Akismet di *background*).

### 7.3 Aturan Keamanan Laravel & Filament (Strict Rules)
1. **CSRF Protection:** Setiap _request_ form POST/PUT/DELETE di *frontend* **wajib** menggunakan `@csrf` token bawaan Laravel.
2. **Data Validation & Sanitization:** Setiap data *input* dari pengguna ke *database* **wajib** divalidasi menggunakan **Form Requests** atau fitur validasi Livewire/Filament sebelum masuk ke model.
3. **Mass Assignment Protection:** Gunakan perlindungan mass assignment di Model (misalnya via properti `$fillable` atau `$guarded`) untuk mencegah eksploitasi injeksi field.
4. **Data Escaping:** Selalu render data di Blade menggunakan tag ganda `{{ $data }}` agar secara otomatis di-escape oleh `htmlspecialchars`.
5. **Proteksi File:** Pengunggahan media melalui kontrol Filament harus menyertakan aturan validasi tipe MIME (misalnya `image/jpeg, image/png`).

---

## 8. Panduan Instalasi & Persiapan Proyek (Setup Guide)

Dokumen ini menjelaskan langkah-langkah untuk menyiapkan proyek Raabiha E-Commerce dari awal, baik di lokal maupun di server.

### 8.1 Persyaratan Lingkungan (Environment)
- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL / MariaDB

### 8.2 Langkah-Langkah Instalasi
1. Kloning repositori proyek.
2. Salin file environment:
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan konfigurasi `.env` dengan database lokal atau servermu.
4. Instal dependensi PHP:
   ```bash
   composer install
   ```
5. Buat key aplikasi:
   ```bash
   php artisan key:generate
   ```
6. Jalankan migrasi dan seeder awal:
   ```bash
   php artisan migrate --seed
   ```
7. Buat link storage (wajib agar gambar muncul):
   ```bash
   php artisan storage:link
   ```
8. Instal dan build aset frontend:
   ```bash
   npm install
   npm run build
   ```

### 8.3 Kredensial Pihak Ketiga (Third-Party APIs)
*Harap isi data berikut di file `.env` ketika API sudah didaftarkan.*

#### Xendit (Payment Gateway)
- `XENDIT_SECRET_KEY=` *(Diperoleh dari dashboard Xendit)*
- `XENDIT_PUBLIC_KEY=`

#### RajaOngkir (Pengiriman)
- `RAJAONGKIR_API_KEY=` *(Harus mendaftar akun Basic/Pro)*
- `RAJAONGKIR_ACCOUNT_TYPE=basic`

*(Kunci-kunci di atas harap tidak di-commit ke Git. Simpan dengan aman!)*

---

## 9. Git Workflow & Syarat Pengembangan

Dalam proyek Raabiha E-Commerce ini, kita menerapkan alur kerja Git yang ketat untuk memastikan stabilitas kode (terutama setelah migrasi ke Laravel/Livewire).

### Aturan Branching
1. **`main`**: Branch utama yang selalu stabil. Hanya di-update melalui merge yang sudah diuji secara menyeluruh.
2. **`development`**: Branch integrasi utama. Semua kode baru harus berpusat di branch ini sebelum rilis.
3. **`feature/*`** atau **`fix/*`**: Branch pengerjaan khusus. Setiap pengembangan fitur baru atau perombakan sistem **WAJIB** dikerjakan di branch terpisah.
   - Contoh: `feature/livewire-product-detail`, `feature/cart-checkout`, `fix/filament-sidebar-sticky`.

### Prosedur Pengembangan (SOP)
Setiap kali melakukan perubahan besar (seperti mengubah static JS menjadi Livewire, atau integrasi API baru), ikuti prosedur ini:

1. **Mulai dari Development**: 
   Pastikan berada di branch `development`.
   ```bash
   git checkout development
   ```
2. **Buat Branch Fitur Baru**:
   Gunakan penamaan yang logis dan jelas.
   ```bash
   git checkout -b feature/<nama-fitur>
   ```
3. **Kerjakan dan Uji (Iterasi)**:
   - Lakukan koding dan perubahan file.
   - Jika terjadi error yang sulit di-revert atau berantakan, kembalilah ke development (`git checkout development`), hapus branch fitur yang rusak (`git branch -D feature/<nama-fitur>`), dan ulangi dari langkah 2.
4. **Commit & Push (Opsional)**:
   Jika fitur selesai dan berjalan normal di lokal, lakukan commit dengan pesan yang deskriptif.
5. **Merge ke Development**:
   Jika uji coba sukses 100%, pindah kembali ke `development` lalu *merge*.
   ```bash
   git checkout development
   git merge feature/<nama-fitur>
   ```
6. **Update Dokumentasi (Wajib)**:
   Setelah fitur yang mengubah struktur *database*, *tech stack*, atau alur bisnis di-*merge*, AI dan Developer **wajib** memperbarui file di `.docs/` (seperti `SYSTEM_GUIDELINES.md` atau `TASKS.md`) agar dokumentasi tidak pernah tertinggal dari kode.

*Aturan ini wajib ditaati oleh AI pada setiap sesi pengembangan untuk mencegah kerusakan kode akibat eksperimen yang gagal (terutama terkait integrasi Livewire/Filament).*

---

## 10. Deployment & Build Strategy

### 10.1 Lingkungan Server & Spesifikasi
- **Provider:** Domainesia
- **Spesifikasi:** VPS Lite (2 Core CPU, 2 GB RAM, 20 GB SSD)
- **Sistem Operasi:** Ubuntu
- **Control Panel:** CloudPanel

### 10.2 Struktur Domain & Branching
Sistem akan di-deploy ke dua lingkungan (*environment*) yang berbeda:
1. **Staging (`raabiha.web.id`):** 
   - Terhubung dengan branch `development`.
   - Digunakan untuk uji coba (UAT) sebelum rilis publik.
   - Menggunakan API Keys (Xendit/RajaOngkir) mode **Sandbox/Test**.
2. **Production (`raabiha.com`):**
   - Terhubung dengan branch `main`.
   - Hanya menerima kode stabil dari *staging*.
   - Menggunakan API Keys mode **Live**.

### 10.3 Alur Pembaruan (Update Process via CloudPanel)
Pembaruan kode dilakukan melalui sinkronisasi Git (Git Pull) di masing-masing direktori *vhost* CloudPanel.
**Proses Build & Optimize:** Setiap rilis kode baru wajib menjalankan perintah kompilasi:
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
npm ci && npm run build
```

### 10.4 Strategi Backup Otomatis (Cron Job)
Karena kapasitas *disk* terbatas (20GB), *backup* harus diatur menggunakan **Cron Job** bawaan CloudPanel:
1. **Backup Database Harian (Midnight):**
   ```bash
   0 0 * * * /usr/bin/mysqldump -u [db_user] -p[db_pass] [db_name] | gzip > /path/to/backup/db_backup_$(date +\%Y\%m\%d).sql.gz
   ```
2. **Rotasi File:** Menghapus backup yang usianya lebih dari 7 hari secara otomatis agar disk tidak penuh.
3. **Pembersihan Log:** Menjalankan rotasi log server CloudPanel secara rutin.
