# Laporan: Inspektur — Audit Forensik Fase 5 (Efek Samping, Duplikasi, File Gemuk & Peta Prioritas Terpadu)

- **Tanggal:** 2026-11-23
- **Mode:** Audit-Only (100% Read-Only — Tidak mengubah satu baris kode pun)
- **Cakupan Fase 5:** Efek samping lanjutan dari temuan Fase 1–4, peta duplikasi kode, daftar file gemuk (> 300 baris), dan Master Roadmap Prioritas Perbaikan (siap dieksekusi kapan pun User memberi lampu hijau).

---

## 1. Efek Samping Lanjutan yang Baru Terdeteksi di Fase 5

### [KRITIS] Temuan F5-01: Halaman Keranjang (`cart.blade.php`) & Checkout (`checkout.blade.php`) Masih Putus Gambar & Abaikan Gambar Varian
- **Lokasi:**
  - [`resources/views/livewire/cart.blade.php:47-57`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L47-L57)
  - [`resources/views/livewire/checkout.blade.php:526-535`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php#L526-L535)
- **Bukti:**
  1. Keduanya **terlewat** saat tambalan URL string di `v1.0.5` (`c92fdee4`). Selama `products.images` di DB berisi URL string `https://...`, pengecekan `Storage::disk('public')->exists($imageId)` selalu gagal ➔ **di Keranjang & Checkout gambar tampil sebagai placeholder!**
  2. Keduanya **tidak pernah mengecek `$item->variant->media`** — meskipun pelanggan membeli varian warna tertentu, gambar di Keranjang & Checkout selalu memaksa foto pertama produk induk.
  3. Keduanya memanggil `\Awcodes\Curator\Models\Media::find(...)` di dalam loop `@foreach` (**N+1 query**).

### [TINGGI] Temuan F5-02: Duplikasi Logika Resolusi Gambar di 6 Tempat Berbeda
Logika membaca gambar produk di-copy-paste secara terpisah di:
1. `ProductsTable.php:48-62`
2. `shop.blade.php:265-283`
3. `home.blade.php:41-55`
4. `ProductDetail.php:356-420`
5. `cart.blade.php:47-57`
6. `checkout.blade.php:526-535`
*(Plus `OrderForm.php:68-100` yang juga masih hanya baca ID Curator).*
**Solusi Arsitektur Ideal:** Konsolidasi menjadi **1 sumber kebenaran (*Single Source of Truth*)** di Model `Product` / `ProductVariant` + Eager Loading media, lalu kembalikan format data DB ke 100% ID Curator integer.

---

## 2. Peta File Gemuk (> 300 Baris — Non-Blog)
Total terdeteksi **23 file** melampaui batas ideal **300 baris**. Top 12 terbesar yang paling rawan bug & berat dikelola:

| Peringkat | Jumlah Baris | Berkas | Risiko Utama |
| :---: | :---: | :--- | :--- |
| 1 | **8.063** | [`resources/views/livewire/pos-manager.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/pos-manager.blade.php) | Monolitik (HTML + Alpine + JS + CSS jadi 1 file) |
| 2 | **2.163** | [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php) | Fat Livewire Component (kasir, shift, produk kustom, retur campur jadi 1) |
| 3 | **1.927** | [`app/Livewire/Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php) | Fat Livewire Component (ongkir, voucher, reseller, payment gateway jadi 1) |
| 4 | **1.100** | [`resources/views/livewire/account.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/account.blade.php) | View terlalu besar, perlu pecah Blade partials |
| 5 | **1.092** | [`app/Services/EscPosService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/EscPosService.php) | Logika format thermal print menumpuk |
| 6 | **1.056** | [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php) | Desktop + Mobile Bottom Sheet + Lightbox + Review jadi 1 file |
| 7 | **883** | [`resources/views/components/layouts/app.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/components/layouts/app.blade.php) | Layout utama terlalu berat |
| 8 | **866** | [`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php) | Transaksi + auto-create produk + stok + voucher + loyalty jadi 1 |
| 9 | **824** | [`resources/views/livewire/checkout.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/checkout.blade.php) | Form checkout monolitik |
| 10 | **812** | [`app/Filament/Resources/Orders/Schemas/OrderForm.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Orders/Schemas/OrderForm.php) | Skema form order + kalkulasi inline |
| 11 | **671** | [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) | Menyimpan full model `public $product` (*state bloat*) |
| 12 | **629** | [`app/Livewire/Account.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Account.php) | Profil + alamat + order + refund jadi 1 |

---

## 3. Master Peta Prioritas Perbaikan (Ringkasan Gabungan Fase 1–5)
*(Belum dieksekusi — menunggu instruksi & lampu hijau User)*

| Prioritas | Kelompok Perbaikan | Temuan Terkait | Peran Tujuan |
| :--- | :--- | :--- | :--- |
| **P0 (Darurat)** | **Pemulihan Format Gambar Curator & Pengamanan `CuratorPicker`** — Migrasi balik URL string di `products.images` ke ID Curator + perbaiki `ProductImporter` + buat 1 helper gambar terpusat (menuntaskan Admin, Shop, Home, Detail, Cart, Checkout sekaligus). | F1-01, F1-02, F1-04, F5-01, F5-02 | `/role-database` + `/role-backend` |
| **P0 (Darurat)** | **Tutup Kebocoran Produk Kasir (`pos_only`) & Penegasan 2 Kanal** — Tambah filter `channel_visibility = 'online_only'` di `home.blade.php` & `ProductDetail.php`, hapus `'both'` dari Bulk Action (`ProductsTable`), Importer/Exporter, & ubah default DB, plus perbaiki bug stok auto-save POS. | F3-01, F3-02, F3-03 | `/role-database` + `/role-backend` |
| **P0 (Keamanan)** | **Update Dependensi Patch CVE** — `composer update` untuk menambal 49 advisory (termasuk 1 Critical `laravel/framework`, 13 High, & CVE `filament-curator`/`livewire`) + cek `npm audit fix` + verifikasi versi PHP di VPS. | F4 (49 CVE Composer, 4 High NPM) | `/role-devops` / `/role-backend` |
| **P1 (Tinggi)** | **Perbaikan Varian & Detail Produk User** — Foto Mobile Bottom Sheet ikut varian, deselect kembali ke foto `index 0`, filter `is_active` varian + tambah toggle di Admin, fix akumulasi stok `addToCart()` & batas qty ikut stok riil. | F2-01, F2-02, F2-03, F2-04, F2-05 | `/role-backend` + `/role-frontend` |
| **P2 (Menengah)** | **Optimasi N+1 Query & Perampingan File Gemuk (> 300 baris)** — Eager loading media, hilangkan query DB dari Blade, pecah `PosManager`, `Checkout`, `ProductDetail`, dan sinkronisasi stok varian (`BKL-004` s/d `BKL-012`). | F1-03, F2-05, F5 (23 file gemuk) | `/role-backend` + `/role-frontend` |

---

## Rangkuman Sesi Audit 5 Fase
Seluruh 5 fase audit forensik telah selesai 100% dalam mode Read-Only tanpa mengubah satu baris kode pun:
1. [Fase 1 — Gambar Produk Utama & Media Curator](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-1-gambar-dan-curator.md)
2. [Fase 2 — Kaitan Produk Varian ↔ Produk Utama & Klik Varian User](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-2-varian-dan-detail-produk.md)
3. [Fase 3 — Pemisahan Kanal E-Commerce vs POS & Kebocoran Produk Kasir](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-3-kanal-ecommerce-vs-pos.md)
4. [Fase 4 — Versi PHP, Laravel, Dependensi & Indikasi CVE/Malware](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-4-keamanan-versi-dan-cve.md)
5. [Fase 5 — Efek Samping, Duplikasi, File Gemuk & Master Peta Prioritas](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-5-efek-samping-dan-peta-prioritas.md)
