# Laporan: Inspektur — Audit Forensik Fase 2 (Kaitan Produk Varian ↔ Produk Utama & Klik Varian di Detail Produk User)

- **Tanggal:** 2026-11-23
- **Mode:** Audit-Only (100% Read-Only — Tidak mengubah satu baris kode pun)
- **Cakupan Fase 2:** Alur penuh **Produk Berdiri Sendiri (`has_variants = false`)** vs **Produk Induk + Varian (`has_variants = true`)** dari Admin (`VariantsRelationManager`, `ProductVariant`) ➔ Livewire (`ProductDetail.php`) ➔ Frontend Desktop & Mobile (`product-detail.blade.php`).

---

## 1. Cakupan & Anggaran Fase
Berkas yang diaudit pada Fase 2 (~2.228 baris kode):
- [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php) (373 baris)
- [`app/Models/ProductVariant.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/ProductVariant.php) (128 baris)
- [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) (671 baris)
- [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php) (1.056 baris)

---

## 2. Peta Cara Kerja Fitur Saat Ini (Alur Nyata di Kode)
1. **Produk Berdiri Sendiri (`has_variants = false`):**
   - `ProductDetail.php` memuat `$product->images` ke `$galleryUrls`. `availableAttributes()` mengembalikan `[]`, sehingga opsi varian tidak dirender. Harga & stok dibaca langsung dari `$product->effective_price` dan `$product->stock`.
2. **Produk Bervarian (`has_variants = true`):**
   - Di Admin (`VariantsRelationManager.php:121-170`), dropdown `media_id` mengambil daftar foto dari `$product->images` (galeri induk). Pilihan disimpan sebagai `product_variants.media_id` (FK ke `curator.id`).
   - Di Sisi User (`ProductDetail.php:111-145`):
     - Saat user klik tombol opsi varian (`wire:click="selectOption('color', 'Navy')"`), Livewire memanggil `getFirstVariantWithImage()` untuk mencari varian pertama yang cocok dengan pilihan aktif & memiliki `media_id`.
     - Livewire mencocokkan `media_id` tersebut dengan `$this->galleryMediaIds` (atau fallback lewat path/URL di `$this->galleryUrls`), lalu menembakkan event `$this->dispatch('change-gallery-image', index: $index)`.
     - Di Desktop (`product-detail.blade.php:62`), Alpine menangkap `@change-gallery-image.window="activeIndex = $event.detail.index"` dan menggeser slider utama ke indeks tersebut.

---

## 3. Daftar Temuan Terklasifikasi

### [KRITIS] Temuan F2-01: Di Tampilan Mobile (Bottom Sheet), Gambar TIDAK Pernah Mengikuti Varian yang Diklik
- **Lokasi:** [`resources/views/livewire/product-detail.blade.php:814`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php#L814)
- **Bukti Kode:**
  ```html
  <img src="{{ $galleryUrls[0] ?? asset('assets/images/placeholder.webp') }}" class="w-full h-full object-cover">
  ```
- **Dampak Nyata:** Di HP (mayoritas pembeli e-commerce), saat user membuka Bottom Sheet untuk memilih varian warna/ukuran, foto preview di bagian atas Bottom Sheet di-hardcode ke `$galleryUrls[0]`. Ketika user mengklik warna lain, slider di belakang layar mungkin bergeser, tetapi **foto di Bottom Sheet yang sedang dilihat user diam saja di gambar pertama**.

### [KRITIS] Temuan F2-02: Varian Non-Aktif (`is_active = false`) Tetap Muncul dan Bisa Dibeli di Detail Produk
- **Lokasi:**
  - [`ProductDetail.php:54`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L54) (`availableAttributes()`)
  - [`ProductDetail.php:88`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L88) (`isOptionAvailable()`)
  - [`ProductDetail.php:158`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L158) (`getFirstVariantWithImage()`)
  - [`ProductDetail.php:182`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L182) (`getMatchedVariant()`)
- **Kontradiksi:** Di [`ProductDetail.php:218`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L218) (`priceRange()`), kode sudah memfilter `$this->product->variants->where('is_active', true)`. Namun di 4 fungsi pemilihan varian di atas, `is_active` **tidak pernah diperiksa**. Akibatnya, varian non-aktif tetap tampil sebagai pilihan dan bisa masuk keranjang!
- **Di Admin (`VariantsRelationManager.php`):** Kolom `is_active` bahkan **tidak ada di form maupun tabel varian** (pelanggaran *Schema Mirroring*).

### [TINGGI] Temuan F2-03: Celah Logika `addToCart()` — Akumulasi Keranjang Bisa Melebihi Stok Riil
- **Lokasi:** [`ProductDetail.php:466-504`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L466-L504)
- **Bukti:** Pengecekan stok hanya mengecek `$matchedVariant->stock < $this->quantity` (atau `$this->product->stock < $this->quantity` untuk produk berdiri sendiri), lalu langsung mengeksekusi `$cartItem->quantity += $this->quantity`. Jika stok tinggal 3 dan user klik "Tambah ke Keranjang" 2 kali dengan qty 3, jumlah di keranjang menjadi **6 pcs** (melebihi stok).
- **Selain itu (`ProductDetail.php:429`):** Tombol `+` (`incrementQuantity`) di-hardcode batas maksimal `10` (`if ($this->quantity < 10)`), tidak membaca batas stok riil produk/varian.

### [TINGGI] Temuan F2-04: Kerapuhan Kaitan Gambar Varian Saat `products.images` Berupa URL String / Deselect Varian
- **Lokasi:** [`VariantsRelationManager.php:146-155`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php#L146-L155), [`ProductDetail.php:117-129`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L117-L129)
- **Masalah:**
  1. Pada `Media.php:55`, kolom `curator.name` disimpan **tanpa ekstensi** (`.webp` dihapus). Sehingga jika `products.images` masih berupa URL string dan path di `curator.path` memiliki perbedaan struktur folder, pencocokan `whereIn('path', ...)` di dropdown varian maupun `ProductDetail.php` masih rawan gagal. Solusi akarnya tetap pemulihan format ID integer Curator (dari Fase 1).
  2. Ketika user **membatalkan pilihan (deselect)** varian di `ProductDetail.php:117-118`, `getFirstVariantWithImage()` mengembalikan `null` dan slider tidak dikembalikan ke indeks `0` (gambar utama produk).
  3. Tabel varian di Admin (`VariantsRelationManager.php:263-290`) masih belum menampilkan kolom preview thumbnail gambar varian, dan aksi `generate_variants` (baris 340-359) belum punya pengecekan duplikasi.

### [SEDANG] Temuan F2-05: Kebocoran Akses Langsung, *State Bloat* & Inkonsistensi Placeholder
- **Akses Langsung Tanpa Filter (`ProductDetail.php:344`):** `Product::with(...)->where('slug', $slug)->firstOrFail()` tidak memeriksa `is_active = true` dan tidak memeriksa `channel_visibility !== 'pos_only'`. Produk non-aktif atau khusus kasir POS tetap bisa dibuka & dibeli jika link-nya diakses langsung.
- **Livewire State Bloat (`ProductDetail.php:23`):** `public $product;` menyimpan seluruh model beserta semua varian & relasinya ke payload JSON Livewire di setiap klik tombol varian.
- **Inkonsistensi Placeholder:** `ProductDetail.php:419` memuat `placeholder.png` (**626 KB**), sementara Blade (`product-detail.blade.php:75,104,814`) memuat `placeholder.webp` (**16 KB** — 39x lebih ringan).

---

## 4. Matriks Sinkronisasi (FE ↔ BE ↔ DB) — Fase 2

| Alur Fitur | Frontend (Blade/Alpine) | Backend (Livewire/Filament) | Database (`product_variants` / `products`) | Status |
| :--- | :--- | :--- | :--- | :--- |
| Klik Varian ➔ Ganti Gambar (Desktop) | `@change-gallery-image.window` | `ProductDetail::selectOption` | `product_variants.media_id` ↔ `products.images` | **JALAN** *(rapuh jika `images` bukan ID Curator; deselect tidak reset)* |
| Klik Varian ➔ Ganti Gambar (Mobile Bottom Sheet) | Hardcode `$galleryUrls[0]` | Tidak terhubung ke `activeIndex` | - | **PUTUS** |
| Status Aktif Varian (`is_active`) | Tidak difilter | Hanya dipakai di `priceRange()`, diabaikan di `selectOption`/`getMatchedVariant`, hilang dari Form Admin | `product_variants.is_active` | **TIDAK SINKRON** |
| Batas Stok Tambah ke Keranjang | Hardcode max qty `10` | `addToCart()` tidak cek akumulasi qty keranjang | `stock` (produk / varian) | **CELAH LOGIKA** |

---

## 5. File Gemuk (> 300 Baris) yang Terdeteksi di Fase 2
- [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php): **373 baris**
- [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php): **671 baris**
- [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php): **1.056 baris**

---

## 6. Pertanyaan Konfirmasi Bisnis (*Zero-Assumption Gate*)
1. **Perilaku Deselect Varian:** Ketika user mengklik ulang opsi warna untuk membatalkan pilihan (*deselect*), apakah Akang ingin slider gambar otomatis kembali ke foto utama pertama (`index 0`), atau dibiarkan di foto terakhir?
2. **Batas Maksimal Pembelian (`incrementQuantity`):** Saat ini tombol `+` di halaman produk dibatasi maksimal `10` per klik. Apakah angka `10` ini memang aturan bisnis toko, atau seharusnya mengikuti batas stok riil produk/varian?

---

## Status Fase
- **Fase 2 Selesai** (100% Read-Only).
- **Fase Berikutnya:** **Fase 3 — Audit Pemisahan Kanal E-Commerce vs POS & Kebocoran Produk Buatan Kasir** (`PosManager.php`, `Shop.php`, `home.blade.php`, `ProductForm.php`, `ProductsTable.php`).
