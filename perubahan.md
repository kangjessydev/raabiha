# Ringkasan Perbaikan (3 Juli 2026)

Berikut adalah rekapitulasi seluruh perbaikan sistem yang telah dieksekusi:

### 1. Manajemen Atribut & Varian
- **Atribut Wajib:** Menjadikan form pilihan opsi atribut wajib diisi (`required`) saat admin menambahkan varian baru secara manual agar sistem filter tidak rusak.
- **Pencegahan Duplikat:** Menambahkan validasi `distinct()` dan `unique()` pada form pembuatan opsi atribut untuk menghindari bug admin salah klik/ketik yang memicu pembuatan atribut ganda dengan slug sama (misalnya ukuran "S" 6 kali).

### 2. Fitur Pencarian Katalog (Shop)
- **Cakupan Pencarian:** Kolom pencarian di halaman Shop tidak lagi hanya mencari nama produk utama, tapi kini bisa meraba ke dalam *Nama Varian*, *Opsi Atribut* (misal: "merah", "XL"), dan *Kategori*.
- **Toleransi Spasi:** Query SQL telah dimodifikasi menggunakan `REPLACE(LOWER(kolom), ' ', '')` sehingga kebal terhadap kesalahan ketik spasi (contoh pencarian "BlueJeans", "Blue Jeans", atau "Blue   Jeans" akan membuahkan hasil yang sama).

### 3. Penegakan SKU Unik & Otomatisasi SKU Induk
- **Database Unique Constraint:** Membuat dan menjalankan migrasi baru yang memaksa tabel `products` (Produk Utama) dan `product_variants` (Varian) untuk tidak menerima SKU duplikat di tingkat core database.
- **Deduplikasi Otomatis:** Melakukan pembersihan data sementara via Tinker untuk menyisipkan penanda pada SKU lama yang terlanjur kembar agar database bisa dimigrasikan dengan sukses.
- **SKU Otomatis di Panel:** Saat membuat varian, kolom SKU telah dikonfigurasi agar mewarisi *prefix* (awalan) secara otomatis dari SKU Produk Utamanya. Admin hanya perlu mengetik ujungnya saja. Sistem juga dirancang cerdas untuk mencegah penumpukan prefix ganda jika admin secara tidak sengaja mengetik manual prefix tersebut.

### 4. Navigasi Beranda
- **Link Kategori Akurat:** Memperbaiki *hyperlink* pada deretan kategori di halaman beranda (`home.blade.php`). Tautan yang tadinya `/shop?category=slug` diubah menjadi sintaks query array Livewire `/shop?selectedCategories[0]=id` agar filter di sidebar halaman Shop langsung merespon dan tercentang dengan benar.
