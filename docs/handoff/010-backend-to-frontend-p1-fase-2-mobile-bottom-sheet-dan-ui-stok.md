# Brief: P1 Fase 2 — Sinkronisasi Foto Mobile Bottom Sheet & Kontrol Stok Riil di Detail Produk

- **Untuk:** `role-frontend`   **Dari:** `role-backend`   **Tanggal:** 2026-11-23
- **Status:** `closed` *(Laporan: `docs/reports/2026-11-23-frontend-p1-fase-2-mobile-bottom-sheet.md`)*
- **Disetujui user:** ya (bagian dari rencana P1 Fase 1 → Fase 2 yang telah dikonfirmasi user)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menyinkronkan gambar preview pada Mobile Bottom Sheet (`product-detail.blade.php:814`) agar reaktif mengikuti varian yang dipilih (dan kembali ke foto pertama saat *deselect*), serta menyelaraskan kontrol tombol kuantitas (`- / +`) di Desktop & Mobile dengan batas stok riil dari backend.

## Konteks
- **P1 Fase 1 (`role-backend`) sudah selesai & lulus 18/18 test** ([`docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md)).
- Rujukan temuan: [`docs/reports/2026-11-23-inspektur-audit-fase-2-varian-dan-detail-produk.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-2-varian-stok-dan-detail-produk.md) (Temuan **F2-01** & bagian UI dari **F2-03**).
- **Kontrak Livewire yang sudah tersedia di [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php):**
  - `public int $activeGalleryIndex = 0;` — Indeks galeri aktif (berubah saat `selectOption()` dan otomatis kembali ke `0` saat *deselect*).
  - `$this->activeGalleryImageUrl` *(Computed)* — URL gambar aktif saat ini (dengan fallback otomatis ke `$galleryUrls[0]` → `asset('assets/images/placeholder.webp')`).
  - `$this->maxOrderableQuantity` *(Computed)* — Batas maksimum kuantitas berdasarkan stok riil varian terpilih (atau stok produk berdiri sendiri).
  - Event Livewire `change-gallery-image` dengan payload `{ index: int }` (dikirim saat pilih varian maupun saat *deselect* ke `index: 0`).

## Yang diminta
1. **Perbaiki Foto Preview Mobile Bottom Sheet (F2-01) di [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php):**
   - Ganti `<img src="{{ $galleryUrls[0] ?? asset('assets/images/placeholder.webp') }}" ...>` pada Mobile Bottom Sheet (**baris 801**, koreksi dari estimasi lama 814) agar reaktif menampilkan `$this->activeGalleryImageUrl` (atau terikat ke state Alpine `activeIndex` / `galleryUrls` yang sinkron dengan `change-gallery-image`).
   - Pastikan saat user klik varian warna di Mobile Bottom Sheet, foto preview di atas Bottom Sheet langsung berubah mengikuti foto varian tersebut, dan saat *deselect*, otomatis kembali ke foto pertama (`index 0`).
   - *(Catatan Audit R-02)*: Pada tombol opsi varian Mobile Bottom Sheet (baris 873 & 898), gunakan `@if($isAvailable || $isSelected)` agar opsi yang sedang terpilih tetap bisa di-*deselect* meski stok kombinasi berubah ke `0` di latar belakang.
2. **Selaraskan Kontrol Kuantitas (`- / +`) di Desktop & Mobile (F2-03):**
   - Pastikan tombol `+` pada tampilan Desktop maupun Mobile Bottom Sheet menggunakan status disabled & atribut `aria-label` yang mengacu pada `$this->maxOrderableQuantity` (bukan hardcode `10`).
   - Pastikan angka harga & stok memakai `tabular-nums` dan aksesibilitas keyboard / focus state terjaga (anti-AI-slop).
3. **Verifikasi & Dokumentasi:**
   - Jalankan `npm run build` (jika aset/class Tailwind berubah) dan test suite terkait (`ProductDetailVariantAndCartGuardTest`, `VariantImageMatchingTest`, `ProductBuyNowTest`).
   - Perbarui `CHANGELOG.md` bagian `[Unreleased]` dan buat laporan di `docs/reports/2026-11-23-frontend-p1-fase-2-mobile-bottom-sheet.md`.

## Di luar cakupan
- **TIDAK mengubah** logika backend di `app/Livewire/ProductDetail.php` maupun `VariantsRelationManager.php` (kecuali ada bug kontrak yang wajib dilaporkan kembali).
- **TIDAK merombak** layout besar atau memecah file `product-detail.blade.php` menjadi banyak partials (itu agenda P2 `BKL-011`).
- **TIDAK deploy** ke VPS dan **TIDAK merge/push** ke branch `main`.

## Kriteria selesai
1. Foto preview Mobile Bottom Sheet di `product-detail.blade.php` tidak lagi di-hardcode ke `$galleryUrls[0]`, melainkan reaktif mengikuti varian aktif & kembali ke foto pertama saat *deselect*.
2. Tombol `+` di Desktop & Mobile disabled secara akurat saat `$quantity >= $this->maxOrderableQuantity` (tidak hardcode `10`).
3. Seluruh feature test terkait lulus 100% tanpa error render Blade/Livewire.
4. `CHANGELOG.md` dan laporan `docs/reports/2026-11-23-frontend-p1-fase-2-mobile-bottom-sheet.md` tertulis dengan bukti konkret.

## Pertanyaan terbuka
- Tidak ada (seluruh keputusan UX telah dikonfirmasi user pada sesi P1).
