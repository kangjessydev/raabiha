# Raabiha UI & Design System Guidelines

Dokumen ini berisi kesepakatan aturan desain (*Design Rules*) untuk menjaga konsistensi antarmuka UI/UX di seluruh ekosistem Raabiha. **Aturan ini wajib ditaati di setiap update/perubahan komponen agar tidak terjadi miskomunikasi UI.**

## Kebijakan Bahasa (Language Policy)
*   **Customer & Admin UI**: Wajib menggunakan **Bahasa Indonesia** yang baku, sopan, dan jelas untuk semua elemen yang dilihat oleh pengguna akhir (Customer) maupun pengelola toko (Admin). Contoh: tombol "Add to Cart" menjadi "Masukkan Keranjang", "Checkout" menjadi "Bayar Sekarang", "Dashboard" menjadi "Dasbor", dsb.
*   **Source Code & Database**: Penamaan *variable*, fungsi, tabel database, kolom, dan *schema* tetap menggunakan **Bahasa Inggris** (contoh: `Product`, `created_at`, `getOrderItems()`) agar mengikuti standar global *framework* Laravel.

## 1. Dashboard Admin (Internal / Staff Area)
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

## 2. Halaman Publik & Dashboard Customer (External / Public Area)
Area ini mencakup wajah depan toko, tempat pelanggan berbelanja dan mengelola akun mereka.
*   **Ruang Lingkup**: Homepage, Katalog Produk, Single Product, Cart, Checkout, dan halaman **My Account (Dashboard Customer)**.
*   **Style Rules**:
    *   **Estetika**: Wajib menggunakan pedoman visual yang sama dengan *Homepage* yang sudah dibuat sebelumnya (Tema *Charcoal/Inverse-Surface*, *Minimalist Luxury*, gaya elegan nan modern).
    *   **UX Pendekatan**: Fokus pada visual produk (gambar berukuran proporsional), jarak (*whitespace*) yang lega, tipografi yang *clean*, dan interaksi pembeli yang intuitif.
    *   **Larangan**: Dilarang keras membawa gaya kaku "Dashboard Admin" (seperti tabel `.raabiha-card` di atas) ke dalam halaman akun pelanggan (*My Account*). Dashboard pelanggan harus terasa menyatu dan natural dengan gaya toko e-commerce.
    *   **Mobile App-Like Experience**: Khusus di layar *mobile* (HP), interaksi perpindahan ke *child page* (contoh: dari Katalog Halaman Utama ke halaman Detail Produk) **wajib meniru *native app* **. Ini meliputi:
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
