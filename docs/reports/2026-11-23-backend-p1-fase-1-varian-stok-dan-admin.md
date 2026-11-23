# Laporan: Backend — P1 Fase 1 (Logika Varian Aktif, Guard Stok Keranjang & Admin Varian)

- **Tanggal:** 2026-11-23
- **Peran:** `role-backend`
- **Handoff Rujukan:** [`docs/handoff/009-asisten-to-backend-p1-fase-1-varian-stok-dan-admin.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/009-asisten-to-backend-p1-fase-1-varian-stok-dan-admin.md)
- **Branch:** `development` *(belum di-commit / tidak deploy ke VPS)*

---

## 1. Ringkasan Perubahan & Bukti (`file:baris`)

| Berkas | Baris Diubah | Ringkasan Implementasi |
| :--- | :--- | :--- |
| [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) | `+131 / -27` | 1. Menambahkan helper `activeVariants()` (`L46-53`) dan menerapkannya secara konsisten pada `availableAttributes()` (`L92`), `isOptionAvailable()` (`L126`), `getFirstVariantWithImage()` (`L205`), `getMatchedVariant()` (`L233`), `priceRange()` (`L284`), dan JSON-LD `inStock` (`L629`).<br>2. Menambahkan properti `activeGalleryIndex` (`L32`) serta computed `activeGalleryImageUrl` (`L55-61`) & `maxOrderableQuantity` (`L63-80`) untuk konsumsi UI P1 Fase 2.<br>3. Memperbaiki `selectOption()` (`L149-193`): mengizinkan *deselect* kapan saja, auto-clamp `$this->quantity` jika melebihi stok varian baru, serta otomatis mengembalikan galeri ke `index 0` (`$activeGalleryIndex = 0` & `dispatch('change-gallery-image', index: 0)`) saat varian di-*deselect*.<br>4. Mengubah `incrementQuantity()` (`L419-425`) dan menambahkan `updatedQuantity()` (`L434-446`) agar batas qty mengikuti stok riil (`maxOrderableQuantity`), bukan hardcode `10`.<br>5. Menambahkan guard akumulasi stok di `addToCart()` (`L448-518`): menolak penambahan bila `$existingQty + $this->quantity > $availableStock` dengan pesan sisa stok yang jelas. |
| [`app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/RelationManagers/VariantsRelationManager.php) | `+78 / -7` | 1. Menambahkan `Toggle::make('is_active')` di form (`L257-260`), `ToggleColumn::make('is_active')` di tabel (`L286-288`), dan `TernaryFilter::make('is_active')` di filter (`L308-311`).<br>2. Menambahkan `->modifyQueryUsing(fn ($query) => $query->with('media'))` (`L269`) dan `ImageColumn::make('thumbnail')` berbasis `ProductImageResolver::resolveVariantUrl()` (`L273-277`) tanpa N+1 query.<br>3. Memperbaiki aksi `generate_variants` (`L356-412`): membangun signature kombinasi ID atribut eksisting untuk melewati duplikat (`BKL-007`), mem-preload `AttributeOption` dalam 1 query, dan mengirim `Notification::make()` ringkasan hasil. |
| [`tests/Feature/ProductDetailVariantAndCartGuardTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/ProductDetailVariantAndCartGuardTest.php) | File baru (5 test case) | Verifikasi otomatis untuk isolasi varian `is_active = false`, guard akumulasi stok `addToCart()` (standalone & varian), batas `incrementQuantity()` mengikuti stok riil + clamp saat pindah varian, reset galeri ke `index 0` saat *deselect*, serta anti-duplikasi `generate_variants`. |

---

## 2. Bukti Verifikasi Otomatis

- **Perintah:**
  ```bash
  php artisan test --filter="ProductDetailVariantAndCartGuardTest|VariantImageMatchingTest|ProductBuyNowTest|CartPageTest|ChannelIsolationAndPosCustomProductTest|ProductImageSingleSourceOfTruthTest"
  ```
- **Hasil:** **18/18 passed (114 assertions, 4.49s)** — exit code `0`.

---

## 3. Checklist SOP & Batasan Cakupan
- [x] `resources/views/livewire/product-detail.blade.php` **tidak disentuh** (diserahkan ke `role-frontend` via handoff `010`).
- [x] Tidak ada perubahan skema DB / migrasi baru.
- [x] `BKL-004`, `BKL-009`, `BKL-011`, dan Reseller (`ADR-002`) tidak disentuh (tetap di P2/backlog).
- [x] `CHANGELOG.md` diperbarui pada bagian `[Unreleased]`.
- [x] Tidak melakukan deploy ke VPS maupun menyentuh branch `main`.

---

## 4. Handoff yang Dibuat
- [`docs/handoff/010-backend-to-frontend-p1-fase-2-mobile-bottom-sheet-dan-ui-stok.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/010-backend-to-frontend-p1-fase-2-mobile-bottom-sheet-dan-ui-stok.md) — Untuk **`/role-frontend`** (P1 Fase 2: sinkronisasi gambar Mobile Bottom Sheet & kontrol batas qty stok riil di `product-detail.blade.php`).
