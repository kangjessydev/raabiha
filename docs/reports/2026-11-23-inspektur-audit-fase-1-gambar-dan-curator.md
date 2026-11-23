# Laporan: Inspektur — Audit Forensik Fase 1 (Gambar Produk Utama & Media Curator)

- **Tanggal:** 2026-11-23
- **Mode:** Audit-Only (100% Read-Only — Tidak mengubah satu baris kode pun)
- **Cakupan Fase 1:** Alur gambar produk utama (produk berdiri sendiri & produk induk bervarian) dari Media Curator ➔ Form Admin ➔ Export/Import CSV ➔ Tabel Admin ➔ Storefront (Beranda & Katalog Shop).

---

## 1. Cakupan & Anggaran Fase
Berkas yang diaudit pada Fase 1 (~1.850 baris kode):
- [`config/curator.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/config/curator.php) (48 baris)
- [`app/Models/Media.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Media.php) (64 baris)
- [`app/Providers/CuratorStorageUrlProvider.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Providers/CuratorStorageUrlProvider.php) (24 baris)
- [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php) (179 baris)
- [`app/Filament/Resources/Products/Schemas/ProductForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php) (234 baris)
- [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php) (259 baris)
- [`app/Jobs/ExportProductsCsvJob.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php) (231 baris)
- [`app/Filament/Imports/ProductImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php) (393 baris)
- [`resources/views/livewire/shop.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php) (440 baris)
- [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php) (454 baris)
- Verifikasi perilaku komponen: [`vendor/awcodes/filament-curator/src/Components/Forms/CuratorPicker.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/vendor/awcodes/filament-curator/src/Components/Forms/CuratorPicker.php#L85-L140)

---

## 2. Peta Cara Kerja Fitur Saat Ini (Akar Penyebab Gambar Putus)

### Kronologi Forensik Mengapa Gambar Tidak Nyambung Padahal di Media Curator Masih Ada
1. **Kondisi Normal (Upload via Admin):**
   `ProductForm.php:98` memakai `CuratorPicker::make('images')->multiple()`. Komponen ini menyimpan **array ID integer** dari tabel `curator` ke kolom JSON `products.images` (contoh: `[12, 13, 14]`).
2. **Titik Rusak #1 — Ekspor CSV Mengubah ID Menjadi URL Teks:**
   Di [`ExportProductsCsvJob.php:219-230`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php#L219-L230), method `formatImages()` mengubah `[12, 13]` menjadi string URL lengkap:
   `https://www.raabiha.com/storage/ae30c84a-...webp, https://www.raabiha.com/storage/...`
3. **Titik Rusak #2 — Impor CSV Menimpa `products.images` Dengan URL Teks Mentah:**
   Di [`ProductImporter.php:106-109`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L106-L109):
   ```php
   ImportColumn::make('images')
       ->castStateUsing(fn (?string $state) => $state ? array_map('trim', explode(',', $state)) : null),
   ```
   Importer **tidak pernah menerjemahkan URL tersebut kembali ke `curator.id`**. Kolom `products.images` di database langsung tertimpa menjadi `["https://www.raabiha.com/storage/ae30c84a-...webp"]`.
4. **Akibat Fatal di Admin (`CuratorPicker`):**
   Di [`CuratorPicker.php:101-102`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/vendor/awcodes/filament-curator/src/Components/Forms/CuratorPicker.php#L101-L102), saat halaman Edit Produk dibuka, komponen memanggil `get_media_items($state)` yang melakukan query `WHERE id IN (...)`. Karena `$state` berisi string URL `"https://..."`, query menghasilkan `[]` (**kosong**). Akibatnya di Form Edit Produk gambar terlihat hilang/tidak nyambung, padahal file & record di Media Curator masih utuh 100%.

---

## 3. Daftar Temuan Terklasifikasi

### [KRITIS] Temuan F1-01: Siklus Export ➔ Import CSV Merusak Format `products.images` (ID Curator ➔ URL String)
- **Lokasi:** [`ExportProductsCsvJob.php:219-230`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php#L219-L230) & [`ProductImporter.php:106-109`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L106-L109)
- **Dampak:** Memutus hubungan antara produk (baik produk berdiri sendiri maupun induk bervarian) dengan tabel `curator`. Menjadi akar masalah kenapa semua foto terlihat hilang di Form Admin & sebelumnya hilang di Storefront.
- **Catatan Tambahan:** Jika kolom `Gambar` di CSV dibiarkan kosong saat update massal via import, `castStateUsing` mengembalikan `null` dan **menghapus seluruh gambar produk yang sudah ada**.

### [KRITIS] Temuan F1-02: Bom Waktu *Data Loss* Saat Edit Produk di Admin (`CuratorPicker`)
- **Lokasi:** [`ProductForm.php:98-106`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Schemas/ProductForm.php#L98-L106) + [`CuratorPicker.php:128-131`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/vendor/awcodes/filament-curator/src/Components/Forms/CuratorPicker.php#L128-L131)
- **Bukti:** Karena `CuratorPicker` gagal me-hydrate URL string menjadi item media (state menjadi `[]`), ketika admin mengedit bidang lain (misal ganti harga/deskripsi) lalu klik **Simpan**, `dehydrateStateUsing` mengembalikan `null` dan **menghapus permanen kolom `products.images` di database menjadi `NULL`**.
- **Catatan Penting:** Tambalan AI kemarin (`c92fdee4` & `8b14a959`) hanya menambahkan pembacaan URL string di tampilan, **tetapi TIDAK memperbaiki data `products.images` di database dan TIDAK menyelesaikan kegagalan `CuratorPicker` di `ProductForm.php`**. Jadi selama data di DB masih berupa URL string, setiap kali admin klik Save di Edit Produk, gambar produk akan terhapus permanen!

### [TINGGI] Temuan F1-03: N+1 Query Media Curator di Tabel Admin, Katalog Shop, dan Beranda
- **Lokasi:**
  - [`ProductsTable.php:57`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L57) (`Media::find($firstMediaId)` di dalam loop baris tabel)
  - [`shop.blade.php:270`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php#L270) (`Media::find($firstImg)` di dalam `@forelse ($products as $product)`)
  - [`home.blade.php:47,63`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php#L47) (`Media::find(...)` di dalam closure `$resolveImage` yang dipanggil berulang per kartu produk/kategori)
- **Dampak:** Setiap kali halaman katalog/admin dibuka, database ditembak puluhan query individual ke tabel `curator`, memboroskan CPU & koneksi MySQL VPS.

### [SEDANG] Temuan F1-04: Duplikasi Logika Resolusi Gambar di 4+ Tempat Berbeda & Inkonsistensi Kelas `Media`
- **Lokasi:** `ProductsTable.php:48-62`, `shop.blade.php:265-283`, `home.blade.php:41-55`, `ProductDetail.php:356-420`.
- **Masalah:**
  - Logika deteksi `is_numeric` vs `http://` vs `Storage::disk('public')->exists()` ditulis ulang (copy-paste) di 4 tempat dengan variasi berbeda-beda (ada yang pakai `$media->url`, ada yang pakai `Storage::url($media->path)`, ada yang cek `Storage::disk('public')->exists()`, ada yang tidak).
  - `config/curator.php:21` mendaftarkan `\App\Models\Media`, tetapi mayoritas kode memanggil `\Awcodes\Curator\Models\Media` secara langsung, sementara `ClientProductImporter.php:189` memanggil `\App\Models\Media`.
  - Seharusnya ada satu helper/accessor terpusat di Model `Product` (misal `$product->primary_image_url` dan `$product->resolved_gallery`).

### [SEDANG] Temuan F1-05: Sisa Fallback `'both'` di Export & Import CSV
- **Lokasi:** [`ExportProductsCsvJob.php:131,177`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php#L131) & [`ProductImporter.php:297-299`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L297-L299)
- **Dampak:** Meskipun ADR-001 menetapkan kanal hanya `pos_only` atau `online_only`, Export & Import CSV masih menyuntikkan `'both'` sebagai default fallback. *(Akan dibedah lebih dalam di Fase 3).*

---

## 4. Matriks Sinkronisasi (FE ↔ BE ↔ DB) — Fase 1

| Komponen / Alur | Format Gambar Diharapkan | Format Riil di DB Produksi (Pasca-Import) | Status |
| :--- | :--- | :--- | :--- |
| `ProductForm.php` (`CuratorPicker`) | `array<int>` (ID tabel `curator`) | `array<string>` (URL `https://...`) | **PUTUS + RISIKO DATA LOSS SAAT SAVE** |
| `ExportProductsCsvJob.php` | Mengubah `int` ➔ URL teks | Menulis URL teks ke CSV | **PENYEBAB PERUBAHAN FORMAT** |
| `ProductImporter.php` | Menerima CSV ➔ simpan mentah | Menimpa `products.images` jadi URL teks | **AKAR MASALAH UTAMA** |
| `ProductsTable.php` | Mendukung ID & URL teks | Tampil (tapi N+1 query jika ID) | **TERTAMBAL (N+1)** |
| `shop.blade.php` & `home.blade.php` | Mendukung ID & URL teks | Tampil (tapi N+1 query jika ID) | **TERTAMBAL (N+1)** |

---

## 5. File Gemuk (> 300 Baris) yang Terdeteksi di Fase 1
- [`app/Filament/Imports/ProductImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php): **393 baris**
- [`resources/views/livewire/shop.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/shop.blade.php): **440 baris** (mengandung logika query PHP/Blade langsung di view)
- [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php): **454 baris** (mengandung query `Product::where(...)` dan `SiteSetting` langsung di file Blade)

---

## 6. Pertanyaan Konfirmasi Bisnis (*Zero-Assumption Gate*)
1. **Konfirmasi Riwayat:** Apakah benar klien/admin sempat menggunakan fitur **Ekspor CSV ➔ Impor CSV** sekitar sebulan lalu sebelum gambar mulai tidak nyambung?
2. **Keputusan Format Tunggal `products.images`:** Apakah Akang sepakat bahwa **satu-satunya format resmi** di kolom `products.images` ke depannya harus dikembalikan menjadi **array ID integer Curator (`[12, 13]`)** (melalui skrip migrasi/pemulihan data satu kali + memperbaiki `ProductImporter` agar menerjemahkan URL balik ke `curator.id`)? Dengan begitu `CuratorPicker`, varian, dan storefront kembali 100% sinkron secara alami tanpa tambal sulam.

---

## Status Fase
- **Fase 1 Selesai** (100% Read-Only).
- **Fase Berikutnya:** **Fase 2 — Audit Kaitan Produk Varian ↔ Produk Utama & Klik Varian di Detail Produk User** (`VariantsRelationManager.php`, `ProductVariant.php`, `ProductDetail.php`, `product-detail.blade.php`).
