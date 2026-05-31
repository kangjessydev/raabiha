# Raabiha UI & Design System Guidelines

Dokumen ini berisi kesepakatan aturan desain (*Design Rules*) untuk menjaga konsistensi antarmuka UI/UX di seluruh ekosistem Raabiha. **Aturan ini wajib ditaati di setiap update/perubahan komponen agar tidak terjadi miskomunikasi UI.**

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
