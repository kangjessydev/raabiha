# Laporan Audit: Analisis Cacat Logika Bisnis (Logic Flaws) Admin Dashboard Raabiha

- **Tanggal Audit:** 2026-11-09
- **Auditor:** `/role-asisten`
- **Cakupan:** Seluruh modul Admin Dashboard non-POS & non-Blog (Katalog Produk, Stok & Inventori, Varian, Pesanan / Order, Voucher, Reseller, Refund Online, Pengguna, dan Laporan Bisnis/Penjualan).
- **Fokus:** Identifikasi anomali logika bisnis, kalkulasi tidak sinkron, inkonsistensi status data, dan kelemahan alur kerja nyata (*real-world workflow flaws*).

---

## 1. Modul Produk, Stok & Varian

### Temuan 1.1: Kolom `products.stock = 0` Mengacaukan Fitur Sortir & Laporan Stok
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Products/Tables/ProductsTable.php:99-120`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L99-L120)
  - [`app/Filament/Pages/LaporanStok.php:31-34`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Pages/LaporanStok.php#L31-L34)
  - [`app/Filament/Widgets/LaporanStokOverviewWidget.php:17-20`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Widgets/LaporanStokOverviewWidget.php#L17-L20)
* **Akar Masalah:**
  Pada produk bervarian (`has_variants = true`), kolom fisik `products.stock` di database dibiarkan bernilai `0` karena stok riil disimpan di tabel `product_variants`.
* **Dampak Logika:**
  1. **Sortir Stok Terbalik:** Saat admin mengurutkan kolom stok di tabel produk secara *Descending* (stok terbanyak ke tersedikit), seluruh produk bervarian yang memiliki stok 50–100 pcs terlempar ke baris paling bawah karena database mengurutkan kolom mentah `products.stock` (bernilai 0).
  2. **Valuasi Modal Rp 0:** Pada menu **Laporan Stok** dan widget ikhtisar, formula valuasi:
     `$products->sum(fn ($p) => $p->purchase_price * $p->stock)`
     menghasilkan nilai **Rp 0** untuk semua produk bervarian, serta menganggap semua produk bervarian sebagai produk "Habis".

### Temuan 1.2: Inkonsistensi Kriteria "Stok Habis" Antar Menu Dashboard
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Products/Widgets/ProductStatsWidget.php:20-26`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php#L20-L26)
  - [`app/Filament/Resources/StockManagement/Pages/ListStockManagement.php:20-35`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/StockManagement/Pages/ListStockManagement.php#L20-L35)
* **Akar Masalah:**
  - Di widget kartu halaman produk, produk varian baru dianggap habis jika **SEMUA variannya bernilai 0** (`whereDoesntHave('variants', fn($q) => $q->where('stock', '>', 0))`).
  - Di halaman Manajemen Stok, produk varian dianggap habis jika **ADA SALAH SATU variannya bernilai 0** (`whereHas('variants', fn($vq) => $vq->where('stock', '<=', 0))`).
* **Dampak Logika:**
  Admin melihat kartu ringkasan tertulis **1 produk habis**, namun saat kartu diklik dan membuka Manajemen Stok, tab *Stok Habis* mencatat **18 produk**.

### Temuan 1.3: Hilangnya Opsi Kanal `both` pada Form & Filter Produk
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Products/Schemas/ProductForm.php:153-160`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php#L153-L160)
  - [`app/Filament/Resources/Products/Tables/ProductsTable.php:158-164`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L158-L164)
* **Akar Masalah:**
  Pilihan dropdown kanal penjualan hanya menyediakan `pos_only` dan `online_only`, mengabaikan opsi `both` (Keduanya: Web & POS).
* **Dampak Logika:**
  Padahal 57 dari 72 produk di database produksi berstatus `both`. Ketika admin mengedit salah satu produk ini lalu menyimpannya, status kanal produk terpaksa ter-override menjadi `online_only`.

### Temuan 1.4: Kerentanan Duplikasi pada Fitur "Buat Varian Otomatis"
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php:339-360`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L339-L360)
* **Akar Masalah:**
  Aksi `generate_variants` melakukan perkalian kombinasi atribut dan langsung mengeksekusi `$product->variants()->create(...)` tanpa memeriksa apakah kombinasi opsi atribut tersebut sudah ada.
* **Dampak Logika:**
  Jika admin mengklik tombol ini lebih dari sekali atau menambah kombinasi baru, produk akan memiliki varian ganda (*duplicate variants*) dengan opsi yang sama persis.

### Temuan 1.5: Tabel Relasi Varian "Buta Gambar"
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php:263-290`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L263-L290)
* **Akar Masalah:**
  Tabel daftar varian produk tidak memiliki kolom thumbnail gambar varian (`media_id`).
* **Dampak Logika:**
  Admin tidak bisa memantau varian mana yang sudah terhubung gambar dan mana yang belum tanpa membuka modal edit satu per satu.

---

## 2. Modul Pesanan (Orders) & Stok

### Temuan 2.1: Pembuatan Pesanan Manual Tanpa Validasi Varian
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Orders/Schemas/OrderForm.php:118-135`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php#L118-L135)
  - [`app/Services/OrderStockService.php:38-76`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/OrderStockService.php#L38-L76)
* **Akar Masalah:**
  Field `product_variant_id` pada repeater item pesanan tidak memiliki validasi `required` ketika produk yang dipilih bertipe varian (`$product->has_variants = true`).
* **Dampak Logika:**
  Admin/staf dapat menyimpan pesanan manual untuk produk bervarian dengan kolom varian kosong (`product_variant_id = null`). Akibatnya, saat stok diproses atau dibatalkan, sistem mengurangi/memulihkan stok produk induk (yang bernilai 0, menjadi minus), sedangkan stok fisik varian tidak pernah berkurang.

---

## 3. Modul Reseller & Otorisasi Diskon

### Temuan 3.1: Persetujuan (*Approve*) Reseller Tidak Sinkron dengan Role Spatie
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Resellers/Tables/ResellersTable.php:48-55`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Resellers/Tables/ResellersTable.php#L48-L55)
  - [`app/Models/Product.php:81`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L81)
  - [`app/Models/ProductVariant.php:117`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/ProductVariant.php#L117)
  - [`app/Livewire/Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php)
* **Akar Masalah:**
  Tombol **Setujui (Approve)** di daftar reseller hanya mengeksekusi:
  `$record->update(['reseller_status' => 'active'])`.
  Tombol ini TIDAK meng-assign peran Spatie Permission `reseller` (`$record->assignRole('reseller')`).
* **Dampak Logika:**
  Pengecekan harga reseller di seluruh web (Produk, Keranjang, Checkout) menggunakan kode:
  `auth()->user()->hasRole('reseller')`.
  Karena role Spatie tidak diberikan saat disetujui, akun reseller yang sudah disetujui admin **tetap dikenakan harga reguler (tidak mendapatkan diskon reseller sama sekali)**.

### Temuan 3.2: Inkonsistensi Nilai Status Reseller Antar Menu (`active` vs `approved`)
* **Lokasi Berkas:**
  - [`app/Filament/Resources/Resellers/Schemas/ResellerForm.php:26-30`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Resellers/Schemas/ResellerForm.php#L26-L30)
  - [`app/Filament/Resources/Users/Schemas/UserForm.php:48-53`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Users/Schemas/UserForm.php#L48-L53)
* **Akar Masalah:**
  - Di menu Reseller, status aktif menggunakan string: `'active' => 'Reseller Aktif'`.
  - Di menu Pengguna (Users), status aktif menggunakan string: `'approved' => 'Reseller Aktif'`.
* **Dampak Logika:**
  Jika admin mengedit pengguna berstatus reseller aktif dari menu Pengguna, dropdown tidak mengenali nilai `'active'`, sehingga saat disimpan statusnya berisiko ter-reset menjadi `'none'`.

---

## 4. Modul Refund Online (E-Commerce)

### Temuan 4.1: Status Refund "Selesai Ditransfer" Tidak Mencatat Pengeluaran Kas
* **Lokasi Berkas:**
  - [`app/Filament/Resources/RefundRequests/RefundRequestResource.php:97-105`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/RefundRequests/RefundRequestResource.php#L97-L105)
* **Akar Masalah:**
  Ketika admin mengubah status refund menjadi `'completed'` (Selesai Ditransfer), aksi ini hanya memperbarui kolom status di database `refund_requests`. Tidak ada pemanggilan pembuatan entri pengeluaran kas (`Cashflow::create(['type' => 'out', ...])`).
* **Dampak Logika:**
  Uang toko yang sudah ditransfer ke pelanggan untuk refund tidak pernah tercatat di modul Keuangan / Laporan Laba Bersih, sehingga laporan laba bersih menjadi *overstated* (tercatat lebih tinggi dari saldo riil).

---

## 5. Modul Laporan Bisnis & Keuangan

### Temuan 5.1: Potensi Pengurangan Ganda (*Double Deduction*) pada Formula Laba Bersih
* **Lokasi Berkas:**
  - [`app/Filament/Widgets/LaporanBisnisOverview.php:50-65`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Widgets/LaporanBisnisOverview.php#L50-L65)
* **Akar Masalah:**
  Formula menghitung:
  `$grossProfit = $totalRevenue - $hpp;`
  `$netProfit = $grossProfit - $totalExpense;`
* **Dampak Logika:**
  Jika admin mencatat pengeluaran belanja stok barang di modul Cashflow (`$totalExpense`), biaya modal barang tersebut akan dikurangkan dua kali (sekali saat belanja barang di Cashflow, dan sekali lagi saat barang terjual melalui kalkulasi HPP). Hal ini menyebabkan angka Laba Bersih tertekan jauh lebih rendah dari riilnya.

---

## 6. Audit Sintaks, Skema Database & Inkonsistensi Penamaan (Naming Mismatch)

### Temuan 6.1: Discrepansi Rumus Harga Reseller Antara Katalog vs Checkout
* **Lokasi Berkas:**
  - [`app/Models/Product.php:81-95`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L81-L95)
  - [`app/Models/ProductVariant.php:117-127`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/ProductVariant.php#L117-L127)
  - [`app/Livewire/Checkout.php:999-1005`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L999-L1005)
* **Akar Masalah:**
  - Di **Katalog (`Product.php` & `ProductVariant.php`)**: Sistem memprioritaskan harga flat khusus reseller (`reseller_price`), dan jika tidak ada, fallback ke diskon global persentase dengan default **20%** jika belum di-set di `SiteSetting`. Syarat: hanya mengecek `hasRole('reseller')`.
  - Di **Checkout (`Checkout.php`)**: Sistem **mengabaikan kolom `reseller_price`**, dan hanya menghitung potongan persentase dengan default fallback **0%**. Syarat: mengecek `hasRole('reseller') && reseller_status === 'active'`.
* **Dampak:**
  Reseller melihat harga diskon flat di halaman produk (misal Rp 150.000 dari normal Rp 200.000), tetapi saat masuk halaman Checkout, harga yang ditagihkan berbeda (misal terpotong 20% menjadi Rp 160.000 atau bahkan tanpa potongan jika setting belum ada).

### Temuan 6.2: 4 Istilah Berbeda untuk Konsep Kanal Penjualan (Omnichannel)
* **Lokasi Berkas:**
  - Produk: `channel_visibility` (`pos_only`, `online_only`, `both`).
  - Voucher: `usable_channel` (`pos_only`, `online_only`, `both`).
  - Pesanan (Order): `source` (`pos`, `ecommerce`).
  - Filter Laporan Penjualan: `channel` (`pos`, `online`).
* **Dampak:**
  Rentan memicu *query mismatch* saat menghubungkan data antar modul (misal filter pesanan e-commerce mencari `source = 'online'`, padahal saat checkout yang tersimpan adalah `source = 'ecommerce'`).

### Temuan 6.3: Tabel Hantu (*Ghost Table*) dari Migrasi Lama
* **Lokasi Berkas:**
  - `coupons` vs `vouchers`: Tabel `coupons` tertinggal dengan kolom `discount_value`, `min_spend`, `valid_from`, padahal seluruh sistem sudah beralih menggunakan tabel `vouchers` (`discount_amount`, `min_purchase`, `starts_at`).
  - `salespages` vs `sales_pages`: Ada 2 tabel duplikat untuk model `SalesPage`.
* **Dampak:**
  Membingungkan proses migrasi database dan pengembang lain karena ada tabel tak bertuan yang tidak pernah dibaca lagi oleh sistem.

### Temuan 6.4: Kolom JSON Hilang dari `$casts` di Model `Order`
* **Lokasi Berkas:**
  - [`app/Models/Order.php:54`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Order.php#L54)
* **Akar Masalah:**
  Kolom `payment_details` terdaftar di `$fillable`, tetapi lupa didaftarkan pada properti `$casts = ['payment_details' => 'array']`.
* **Dampak:**
  Saat data `payment_details` diakses melalui Eloquent model, data berupa string JSON mentah alih-alih array PHP terstruktur, berisiko fatal memicu error string-offset jika diakses via key `['card_type']`.
