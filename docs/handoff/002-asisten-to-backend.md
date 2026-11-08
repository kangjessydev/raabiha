# Brief: Perbaikan Resolusi Gambar Produk & Tampilan Stok Varian

- **Untuk:** role-backend   **Dari:** role-asisten   **Tanggal:** 2026-11-08
- **Status:** done

## Tujuan
Memastikan seluruh produk dengan format gambar URL string dan produk bervarian dapat menampilkan foto asli serta kalkulasi total stok secara akurat di Web Storefront (`/shop`, `/product/{slug}`, `/`) dan Filament Admin Dashboard (`ProductsTable.php`), persis seperti yang berjalan di POS.

## Konteks
- Rujukan: [`docs/project-brief.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/project-brief.md), [`docs/project-map.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/project-map.md).
- Profil Risiko: **Ketat** (tidak boleh merusak integritas data transaksi dan inventori).
- Hasil Diagnosa VPS (2026-11-08):
  1. Kolom `products.images` di database menyimpan array URL string lengkap (`["https://www.raabiha.com/storage/ae30c84a-...webp"]`). Terminal POS berhasil menampilkan foto karena memiliki fallback `asset($images[0])` di `pos-manager.blade.php:897`. Sebaliknya, `shop.blade.php`, `ProductDetail.php`, dan `ProductsTable.php` memaksakan pengecekan file fisik lokal atau `Media::find()`, sehingga jatuh ke placeholder kosong.
  2. Sebanyak 70 dari 72 produk adalah produk bervarian (`has_variants = 1`) dengan stok berada di tabel `product_variants.stock`, sedangkan kolom `products.stock` bernilai `0`. POS menghitung total stok varian secara dinamis, sedangkan tabel admin hanya membaca `products.stock` secara mentah sehingga tampak bernilai `0`.

## Yang Diminta
1. **Perbaikan Resolusi Gambar di Admin Dashboard:**
   - Di [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L31): Periksa `$firstMediaId`. Jika merupakan string URL (diawali `http://` atau `https://`) atau path string, gunakan URL tersebut secara langsung sebagai `$imgSrc`. Hanya lakukan `Media::find()` jika nilainya berupa ID integer numerik.
2. **Perbaikan Resolusi Gambar di Web Storefront:**
   - Di [`resources/views/livewire/shop.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php#L265) dan [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L302): Tambahkan fallback jika string gambar sudah berupa URL lengkap (`http://` atau `https://`), maka langsung masukkan ke galeri/URL gambar tanpa melewati validasi `Storage::disk('public')->exists()`.
   - Di [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php#L41): Sesuaikan `$resolveImage` dan `$resolveCatImage` agar menangani string URL lengkap.
3. **Perbaikan Kolom Stok di Admin Dashboard:**
   - Di [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L73): Ubah tampilan kolom `stock` agar jika `$record->has_variants` bernilai `true`, tampilkan total stok dari variannya (misal `$record->total_stock . ' pcs (varian)'` atau gunakan perhitungan relasi varian), dan jika single product tampilkan `$record->stock . ' pcs'`.
4. **Penyelarasan Kartu Ringkasan Stok Habis di Dashboard:**
   - Di [`app/Filament/Resources/Products/Widgets/ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php#L19): Sesuaikan logika penghitungan `outOfStock` agar produk bervarian hanya dianggap "Habis" jika **seluruh** variannya memiliki total stok `<= 0` (bukan jika hanya salah satu varian habis).

## Di Luar Cakupan
- Dilarang memodifikasi skema tabel database produksi (`products`, `product_variants`, dsb.).
- Dilarang mengubah alur kasir POS (`PosManager.php`, `PosTransactionService.php`) yang sudah stabil.
- Tidak melakukan `git push --force` ke branch produksi `main` sebelum diverifikasi lokal.

## Kriteria Selesai
1. Di halaman `/admin/products`, thumbnail foto produk bervarian dan non-varian muncul dengan gambar aslinya (bukan SVG placeholder kosong).
2. Di halaman `/admin/products`, kolom stok menampilkan angka riil penjumlahan varian (misal `35 pcs (varian)` bukannya `0`).
3. Di halaman web e-commerce `/shop` dan `/product/{slug}`, produk menampilkan foto asli (bukan `placeholder.webp`).
4. Seluruh kode lolos `php artisan pint --test` atau `php -l` tanpa syntax error.

## Pertanyaan Terbuka
- Tidak ada. Kebutuhan dan solusinya sudah dipetakan secara presisi dari fakta database VPS.
