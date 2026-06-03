# Arsitektur Database & Skema

Arsitektur database untuk produk dan varian dirancang dengan aturan mutlak sebagai berikut untuk mengakomodasi fleksibilitas e-commerce:

## 1. Hirarki Stok & Ketersediaan
- **Produk Tanpa Varian (`has_variants = false`):**
  - Stok fisik dicatat langsung di tabel utama `products` (kolom `stock`).
  - Total stok produk = `products.stock`.
- **Produk Dengan Varian (`has_variants = true`):**
  - Kolom `products.stock` akan diabaikan/disembunyikan dari antarmuka Admin.
  - Stok fisik dicatat secara individual pada setiap baris di tabel `product_variants` (kolom `stock`).
  - Total stok produk adalah akumulasi (SUM) dari seluruh `product_variants.stock` yang terkait.

## 2. Hirarki Harga (Pricing Override)
1. Harga dasar/default dan harga reseller utama selalu dicatat di tabel `products`.
2. Jika sebuah varian memiliki harga yang sama dengan harga dasar produk, maka *toggle* `is_price_override` diatur ke `false`. Kolom `price` pada tabel `product_variants` dapat dibiarkan kosong (`null`), dan sistem **wajib** mengambil (fallback) harga dari tabel `products`.
3. Jika sebuah varian memiliki harga yang berbeda (lebih mahal/murah), *toggle* `is_price_override` diatur ke `true`. Sistem wajib membaca harga final dari kolom `price` di tabel `product_variants`.

## 3. Logika Kombinasi Atribut (Pivot)
- Varian adalah **satu kesatuan barang fisik** (memiliki SKU, Stok, Harga).
- Kombinasi Atribut (seperti "Warna: Merah", "Ukuran: XL") adalah sekadar **Label/Tag** yang menempel pada Varian tersebut.
- Relasi antara *Varian Fisik* dengan *Opsi Atribut* disambungkan melalui tabel pivot `attribute_option_product_variant` (memiliki primary key `id`).
- Form input Filament menggunakan metode *Nested Repeater* (Cascading Dropdown) untuk menempelkan Label/Tag pada Varian, tetapi **DILARANG KERAS** memindahkan kolom `stok` atau `harga` ke dalam tabel pivot/kombinasi atribut tersebut. Harga dan stok mutlak milik entitas Varian.

## 4. Arsitektur Keranjang (Cart)
Berdasarkan keputusan CTO, keranjang belanja wajib menggunakan pendekatan **Database Storage (Opsi A)**:
- **Tabel `carts` dan `cart_items`:** Digunakan untuk menyimpan sesi keranjang agar *persistent* (tersimpan meski ganti *device* jika login).
- **Guest vs Registered User:** Jika *user* login, cart diikat ke `user_id`. Jika *user* adalah tamu (Guest), cart diikat ke `session_id` di database. Jika Guest kemudian login, sistem wajib "menyatukan" (*merge*) keranjang Guest tersebut ke akun *user* yang bersangkutan.
- **Isolasi "Beli Sekarang":** Fitur klik "Beli Sekarang" (langsung ke *checkout* 1 barang tanpa menghapus isi keranjang reguler) dikelola menggunakan kolom boolean `is_buy_now` pada tabel keranjang, atau disimpan di dalam *session* khusus yang tidak menyentuh `cart_items` utama.

## 5. Arsitektur Pesanan (Order) & Guest Checkout
- **Fleksibilitas Checkout:** Pelanggan tidak diwajibkan mendaftar akun (*Guest Checkout Allowed*).
- **Relasi Database `orders`:** Tabel pesanan harus mengizinkan `user_id` menjadi `nullable`. Jika `user_id` bernilai `null`, pesanan diidentifikasi sebagai pesanan Guest.
- **Pembedaan Admin Panel:** Di Filament Admin, tabel/resource Orders **wajib** memiliki *badge* atau filter visual untuk membedakan secara jelas mana transaksi "User Terdaftar" dan mana transaksi "Guest".
- **Pelacakan (Tracking):** User yang login dapat melihat riwayat di Dasbor Pelanggan. Guest akan melacak pesanan menggunakan kombinasi "ID Pesanan" dan "Email" secara manual di halaman publik "Track Order".

## 6. Rencana Pengembangan Skema (Masa Depan)
*Dokumentasi ini wajib diperbarui setiap kali ada penambahan tabel atau perubahan struktur, seperti sistem ongkir (Shipping) dan Pembayaran (Payments).*
