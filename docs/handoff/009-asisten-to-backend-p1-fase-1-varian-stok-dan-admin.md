# Brief: P1 Fase 1 — Perbaikan Logika Varian Aktif, Batas Stok Keranjang & Admin Varian

- **Untuk:** `role-backend`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` *(Selesai pada 2026-11-23 — laporan: `docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md`)*
- **Disetujui user:** ya (Delegasi penuh: diizinkan mengeksekusi hingga seluruh kriteria selesai & tes lulus tanpa menunggu persetujuan di setiap langkah kecil)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menuntaskan celah logika backend pada halaman Detail Produk (filter varian `is_active`, guard akumulasi stok di keranjang, batas qty mengikuti stok riil, reset gambar saat *deselect*) serta melengkapi manajemen varian di Admin Filament (`is_active` toggle, pencegahan duplikasi `generate_variants`, dan kolom thumbnail gambar varian).

## Konteks
- Rujukan utama:
  - [`docs/reports/2026-11-23-inspektur-audit-fase-2-varian-dan-detail-produk.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-2-varian-dan-detail-produk.md) (Temuan **F2-02**, **F2-03**, **F2-04**)
  - [`docs/reports/2026-11-23-inspektur-audit-fase-5-efek-samping-dan-peta-prioritas.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-23-inspektur-audit-fase-5-efek-samping-dan-peta-prioritas.md) (Peta Prioritas **P1**)
  - [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) (`BKL-007`, `BKL-008`)
  - [`docs/decisions.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/decisions.md) (ADR-001: 2 kanal saja; ADR-002: Reseller non-wajib)
- Keputusan bisnis user yang sudah disepakati pada sesi ini:
  1. **Deselect varian:** Saat user mengklik ulang opsi untuk membatalkan pilihan (*deselect*), gambar galeri harus otomatis kembali ke **foto utama pertama (`index 0`)**.
  2. **Batas maksimal qty (`incrementQuantity`):** Mengikuti **stok riil produk/varian** (bukan hardcode `10`). Jika produk bervarian dan varian belum terpilih lengkap, gunakan batas aman yang logis (mis. total/max stok varian aktif atau minimal `1`) dan pastikan saat varian dipilih, `$this->quantity` otomatis di-clamp jika melebihi stok varian tersebut.
- Profil risiko: **Tinggi** (aliran transaksi keranjang & ketersediaan stok).

## Yang diminta
### 1. [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php)
1. **Filter `is_active = true` Konsisten (F2-02):**
   - Pastikan seluruh method pemilihan & validasi varian — `availableAttributes()`, `isOptionAvailable()`, `getFirstVariantWithImage()`, `getMatchedVariant()`, serta `selectOption()` / `addToCart()` / `buyNow()` — **hanya memproses varian dengan `is_active === true`**.
   - Varian non-aktif tidak boleh tampil di opsi pilihan dan tidak boleh bisa masuk keranjang maupun dibeli langsung (`buyNow`).
2. **Guard Akumulasi Stok Keranjang & Batas Qty Riil (F2-03):**
   - Pada `addToCart()` (dan cek `buyNow()` agar konsisten): hitung jumlah item yang **sudah ada di keranjang** (`$existingQty`) untuk produk/varian yang sama (baik user login maupun guest session sesuai mekanisme cart yang ada).
   - Validasi bahwa `$existingQty + $this->quantity <= $availableStock`. Jika melebihi stok riil, tolak penambahan dan tampilkan pesan error/notifikasi yang informatif (menyebutkan sisa stok / qty yang sudah ada di keranjang).
   - Pada `incrementQuantity()` (dan `updatedQuantity()` jika ada): hapus hardcode batas `10`, ganti dengan batas **stok riil** (`maxStock` dari varian terpilih jika `has_variants`, atau `$this->product->stock` jika produk berdiri sendiri).
   - Expose helper/computed property (mis. `maxOrderableQuantity` atau `activeVariantImageUrl` / `activeGalleryIndex`) yang bersih agar mudah dikonsumsi oleh `role-frontend` pada P1 Fase 2 (`product-detail.blade.php` Mobile Bottom Sheet & tombol `+`).
   - Saat user berganti pilihan varian ke varian dengan stok lebih kecil dari `$this->quantity`, otomatis sesuaikan (`clamp`) `$this->quantity` agar tidak melebihi stok varian terpilih (minimal `1` bila stok > 0).
3. **Reset Galeri saat Deselect Varian (F2-04):**
   - Pada `selectOption()`: jika setelah *toggle/deselect* tidak ada varian cocok yang memiliki gambar (atau `$this->selectedOptions` kosong), kirim event `$this->dispatch('change-gallery-image', index: 0)` dan perbarui state indeks/URL aktif ke foto utama pertama (`index 0`).

### 2. [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php)
1. **Schema Mirroring `is_active` (F2-02):**
   - Tambahkan field `Toggle::make('is_active')` (default `true`) pada form tambah/edit varian.
   - Tambahkan kolom `IconColumn::make('is_active')->boolean()` atau `ToggleColumn::make('is_active')` pada tabel varian agar admin bisa mengaktifkan/menonaktifkan varian secara langsung.
2. **Pencegahan Duplikasi `generate_variants` (F2-04 / BKL-007):**
   - Sebelum membuat varian baru pada aksi `generate_variants`, periksa apakah kombinasi `attribute_value_ids` tersebut sudah ada pada produk induk. Lewati kombinasi yang sudah ada dan tampilkan notifikasi ringkas (berapa varian baru dibuat & berapa yang dilewati karena duplikat).
3. **Thumbnail Gambar Varian di Tabel Admin (F2-04 / BKL-008):**
   - Tambahkan kolom preview thumbnail gambar varian pada tabel `VariantsRelationManager` menggunakan `ProductImageResolver` (konsisten dengan P0 Fase 1, fallback ke `assets/images/placeholder.webp`, tidak hardcode `Storage::url` mentah).

### 3. Pengujian & Dokumentasi
- Tambahkan feature test baru [`tests/Feature/ProductDetailVariantAndCartGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/ProductDetailVariantAndCartGuardTest.php) yang memverifikasi:
  1. Varian dengan `is_active = false` tidak muncul di `availableAttributes()`, tidak bisa dipilih/matched, dan gagal masuk keranjang.
  2. `addToCart()` menolak akumulasi qty (`existingCartQty + qty > stock`) baik untuk produk berdiri sendiri maupun produk bervarian.
  3. `incrementQuantity()` mengikuti stok riil (bisa > 10 bila stok > 10, dan berhenti di stok riil bila stok < 10).
  4. Deselect varian memicu event `change-gallery-image` dengan `index: 0`.
  5. Pencegahan duplikasi kombinasi atribut saat generate varian (jika teruji via Livewire/method atau unit test logika duplikasi).
- Perbarui `CHANGELOG.md` di bagian `[Unreleased]` sesuai aturan disiplin jejak perubahan.
- Simpan laporan hasil pengerjaan di `docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md`.

## Di luar cakupan
- **TIDAK mengubah** [`resources/views/livewire/product-detail.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/product-detail.blade.php) — UI Mobile Bottom Sheet (F2-01) dan penyesuaian tampilan Blade adalah tugas **P1 Fase 2 (`role-frontend`)**.
- **TIDAK mengerjakan** `BKL-004` (sorting stok produk di `ProductsTable`), `BKL-009` (`OrderForm` validasi varian), refactoring file gemuk (`BKL-011`), maupun fitur Reseller (`ADR-002`) — seluruhnya tetap di P2/backlog.
- **TIDAK mengubah** skema database / membuat migrasi baru (kolom `product_variants.is_active` sudah ada di DB).
- **TIDAK deploy** ke VPS dan **TIDAK merge/push** ke branch `main`.

## Kriteria selesai
1. Seluruh 4 method pemilihan varian di `ProductDetail.php` memfilter `is_active = true` (terbukti lewat kode `file:baris` dan feature test).
2. Akumulasi keranjang di `addToCart()` tidak dapat melampaui stok riil, dan `incrementQuantity()` mengikuti stok riil (terbukti lewat feature test).
3. Deselect varian men-dispatch `change-gallery-image` dengan `index: 0` (terbukti lewat feature test).
4. `VariantsRelationManager.php` memiliki kontrol `is_active` (form & tabel), pencegahan duplikasi pada `generate_variants`, serta kolom thumbnail gambar berbasis `ProductImageResolver`.
5. `php artisan test --filter="ProductDetailVariantAndCartGuardTest|VariantImageMatchingTest|ProductBuyNowTest|CartPageTest|ChannelIsolationAndPosCustomProductTest|ProductImageSingleSourceOfTruthTest"` lulus **100% tanpa error/regresi**.
6. `CHANGELOG.md` dan laporan `docs/reports/2026-11-23-backend-p1-fase-1-varian-stok-dan-admin.md` tertulis rapi dengan bukti konkret.

## Pertanyaan terbuka
- Tidak ada (seluruh keputusan bisnis telah dikonfirmasi user).
