# Brief: P0 — Fase 1: Pemulihan & Pengamanan Gambar Produk + Media Curator (Single Source of Truth)

- **Status:** closed (selesai 2026-11-23 — laporan: `docs/reports/2026-11-23-backend-p0-fase-1-gambar-curator.md`)
- **Untuk:** role-backend
- **Dari:** role-asisten
- **Tanggal:** 2026-11-23
- **Disetujui user:** ya (termasuk **Izin Delegasi Berkelanjutan** — boleh dieksekusi sampai kriteria selesai tercapai tanpa berhenti di setiap langkah kecil)

## Tujuan
Menyatukan seluruh logika resolusi gambar produk utama (berdiri sendiri & bervarian) serta varian ke dalam satu sumber kebenaran (*Single Source of Truth*), mengamankan `CuratorPicker` dari risiko *data loss*, memperbaiki `ProductImporter`, dan menyediakan perintah pemulihan idempoten untuk mengonversi `products.images` dari URL string kembali ke ID Curator.

## Konteks
- Rujukan laporan audit:
  - [`docs/reports/2026-11-23-inspektur-audit-fase-1-gambar-dan-curator.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-1-gambar-dan-curator.md) (Temuan F1-01, F1-02, F1-03, F1-04)
  - [`docs/reports/2026-11-23-inspektur-audit-fase-5-efek-samping-dan-peta-prioritas.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-5-efek-samping-dan-peta-prioritas.md) (Temuan F5-01, F5-02)
- Rujukan proyek: `docs/project-brief.md`, `docs/decisions.md` (ADR-001 & ADR-002), `CHANGELOG.md`.
- Profil risiko: **Standar–Ketat** (Zero Data Loss — dilarang menghapus atau merusak data gambar yang sudah ada di DB produksi).

## Yang diminta
1. **Helper Resolusi Gambar Terpusat (*Single Source of Truth*):**
   - Buat method/helper terpusat di Model [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php) (dan/atau Service kecil khusus media produk, misal `ProductImageResolver` / method di `Product` & `ProductVariant`) untuk:
     - Menyelesaikan URL gambar utama produk (`getPrimaryImageUrlAttribute` / `resolveImageUrl`) secara konsisten untuk format ID Curator numerik, relative path, maupun URL lengkap (`http://`/`https://`), dengan fallback `asset('assets/images/placeholder.webp')` (16 KB, bukan `.png` 626 KB).
     - Menyelesaikan daftar galeri `[['url' => ..., 'media_id' => ...]]` secara paralel 1-ke-1.
     - Pada `ProductVariant`: helper untuk mengambil URL gambar varian dengan fallback otomatis ke gambar utama produk induk (`$variant->image_url`).
     - Seragamkan pemakaian model Media ke `\App\Models\Media` (sesuai `config/curator.php:21`).
2. **Pengamanan `CuratorPicker` di [`app/Filament/Resources/Products/Schemas/ProductForm.php:98-106`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php#L98-L106):**
   - Pastikan saat form Edit Produk dibuka untuk produk yang `images`-nya masih berupa URL string, `CuratorPicker` secara otomatis menerjemahkan URL/path tersebut ke `curator.id` (via `formatStateUsing` / `afterStateHydrated` yang aman) sehingga foto tetap tampil di Admin dan **tidak pernah terhapus menjadi `NULL` saat tombol Simpan diklik**.
3. **Artisan Command Pemulihan Data Idempoten (`products:normalize-images`):**
   - Buat command Artisan (`app/Console/Commands/NormalizeProductImages.php`) yang mendukung flag `--dry-run`.
   - Tugasnya: memindai seluruh baris `products.images` yang masih berisi string URL/path, mencocokkannya dengan tabel `curator` (via `path` / `basename`), dan mengubahnya kembali ke array integer `[curator.id, ...]` secara aman. Jika suatu URL tidak ditemukan di tabel `curator`, pertahankan string aslinya agar **zero data loss** dan catat di log output.
4. **Perbaikan [`app/Filament/Imports/ProductImporter.php:106-109`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L106-L109):**
   - Terjemahkan URL/path gambar dari CSV ke ID Curator (`curator.id`) saat impor.
   - Jika kolom `Gambar` di CSV kosong saat update produk eksisting, **jangan menimpa `products.images` menjadi `null`** (pertahankan gambar eksisting).
5. **Konsolidasi Pembacaan Gambar di 6 Lokasi (Eliminasi Duplikasi & Perbaiki Keranjang/Checkout):**
   - Perbarui pemanggilan gambar agar memakai helper terpusat (serta minimalkan N+1 query):
     - [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php)
     - [`resources/views/livewire/shop.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php)
     - [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php)
     - [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php)
     - [`resources/views/livewire/cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php) *(tampilkan foto varian jika ada, fallback ke produk utama)*
     - [`resources/views/livewire/checkout.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php) *(tampilkan foto varian jika ada, fallback ke produk utama)*

## Di luar cakupan
- **JANGAN** mengubah logika kanal `channel_visibility` / `both` / kebocoran produk kasir POS (itu masuk **P0 — Fase 2**).
- **JANGAN** menjalankan `composer update` / `npm audit fix` (itu masuk **P0 — Fase 3**).
- **JANGAN** mengubah perilaku Mobile Bottom Sheet, `is_active` varian, atau batas qty `addToCart()` (itu masuk **P1**).
- **JANGAN** mengubah skema DDL database (tidak perlu drop/alter kolom).
- **JANGAN** melakukan push/deploy ke VPS produksi tanpa persetujuan eksplisit user.

## Kriteria selesai
- [ ] Helper gambar terpusat tersedia dan dipakai di seluruh 6 lokasi (Admin Table, Shop, Home, Detail Produk, Cart, Checkout) dengan fallback `placeholder.webp`.
- [ ] `ProductForm.php` (`CuratorPicker`) aman terhadap format URL string & tidak menghapus `products.images` saat Save.
- [ ] Command `php artisan products:normalize-images --dry-run` berjalan sukses dan teruji idempoten.
- [ ] `ProductImporter.php` mengonversi URL gambar CSV ke ID Curator dan tidak menghapus gambar saat kolom CSV kosong.
- [ ] Feature test baru/terbarui membuktikan:
  1. Produk berdiri sendiri & produk bervarian dengan format ID Curator maupun URL string merender gambar dengan benar di Shop, Detail Produk, Cart (prioritas foto varian), dan Checkout.
  2. Command `products:normalize-images` berhasil mengonversi URL ➔ ID Curator tanpa kehilangan data.
  3. Seluruh test terkait (`VariantImageMatchingTest`, `ShopPageTest`, `CartPageTest`, `ProductBuyNowTest`, `SecurityHardeningTest`) lulus 100% hijau.
- [ ] `php -l` bersih, `vendor/bin/pint` bersih, `CHANGELOG.md` diperbarui di `[Unreleased]`, dan laporan tersimpan di `docs/reports/2026-11-23-backend-p0-fase-1-gambar-curator.md`.

## Pertanyaan terbuka
- *(Semua sudah dikonfirmasi user: format resmi harus aman & normal di Admin, User, dan Kasir; delegasi berkelanjutan disetujui).*
