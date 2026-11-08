# Laporan: Backend — Perbaikan Resolusi Gambar & Kalkulasi Stok Varian

- **Tanggal:** 2026-11-08
- **Brief/Handoff Terkait:** [`docs/handoff/002-asisten-to-backend.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/002-asisten-to-backend.md)
- **Mode:** Normal

## Ringkasan
Telah diselesaikan implementasi perbaikan resolusi gambar produk dan tampilan stok varian pada Filament Admin Dashboard (`admin.raabiha.com`) serta Web Storefront (`raabiha.com`). Seluruh gambar dengan format URL string lengkap kini dapat ditampilkan dengan sempurna, dan tabel admin menampilkan kalkulasi total stok varian riil tanpa memicu N+1 query.

## Yang Dikerjakan
1. **[`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php):**
   - Menambahkan deteksi format string URL (`http://` / `https://`) pada format thumbnail produk, sehingga item gambar langsung dirender tanpa dipaksa melalui `Media::find()`.
   - Mengubah kolom `stock` agar menampilkan total stok varian (`totalVariantStock . ' pcs (varian)'`) jika `$record->has_variants == true`.
   - Mencegah N+1 query dengan menambahkan `modifyQueryUsing(fn ($query) => $query->with(['category', 'variants']))`.
2. **[`app/Filament/Resources/Products/Widgets/ProductStatsWidget.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Widgets/ProductStatsWidget.php):**
   - Menyelaraskan kriteria kartu "Stok Habis / Menipis" dengan `whereDoesntHave('variants', fn($q) => $q->where('stock', '>', 0))` agar produk yang masih memiliki varian aktif tidak dicatat sebagai produk habis.
3. **[`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) & [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php):**
   - Menambahkan dukungan URL string langsung pada galeri detail produk dan produk terkait.
4. **[`resources/views/livewire/shop.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php) & [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php):**
   - Menambahkan fallback pembacaan URL gambar langsung pada kartu katalog produk belanja dan produk unggulan beranda.

## Checklist SOP (Peran Penulis Kode)
- [x] Solusi terkecil yang memenuhi kebutuhan tanpa over-engineering.
- [x] Tidak mengubah skema tabel database produksi.
- [x] Tidak ada N+1 query (eager loading dipasang).
- [x] Format kode dipoles dengan `vendor/bin/pint`.
- [x] Sintaks PHP lolos uji `php -l` di seluruh file terkait.
- [x] Uji regresi fitur `ShopPageTest`, `CartPageTest`, dan `ProductBuyNowTest` berstatus hijau (6 passed).

## Perubahan Kode
- **File Diubah:**
  - `app/Filament/Resources/Products/Tables/ProductsTable.php`
  - `app/Filament/Resources/Products/Widgets/ProductStatsWidget.php`
  - `app/Livewire/ProductDetail.php`
  - `resources/views/livewire/product-detail.blade.php`
  - `resources/views/livewire/shop.blade.php`
  - `resources/views/home.blade.php`
- **Baris +/-:** ~50 baris ditambahkan / disesuaikan.

## Belum Terverifikasi
- Deployment ke server VPS produksi (menunggu konfirmasi user dan hasil audit `/role-inspektur`).

## Log Penyimpangan
| Langkah/Pertanyaan Tambahan | Alasan | Disetujui User? |
|---|---|---|
| Penambahan eager loading `variants` di ProductsTable | Menghindari query N+1 saat menghitung total stok varian per baris tabel | Sesuai SOP backend |

## Handoff yang Dibuat
- `docs/handoff/003-backend-to-inspektur.md`: Permintaan audit mutu end-to-end, verifikasi tampilan gambar, kalkulasi stok, serta sanitasi keamanan.

## Rangkuman Sesi
Fase perbaikan backend dan tampilan selesai 100%. Kode siap diaudit oleh `/role-inspektur` sebelum dideploy ke produksi.
