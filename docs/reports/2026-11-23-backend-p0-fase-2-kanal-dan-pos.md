# Laporan Sesi: P0 — Fase 2 Penutupan Kebocoran Produk Kasir POS & Penegakan 2 Kanal (ADR-001)

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Handoff Acuan:** [`docs/handoff/006-asisten-to-backend-p0-fase-2-kanal-dan-pos.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/006-asisten-to-backend-p0-fase-2-kanal-dan-pos.md)
- **Status:** Selesai & Terverifikasi (Lokal)

---

## 1. Ringkasan Perubahan

1. **Isolasi Total Produk Kasir POS (`pos_only` & `is_custom = true`) dari E-Commerce:**
   - Menambahkan scope terpusat `scopeVisibleOnline()` & `scopeVisibleInPos()` serta default atribut `$attributes = ['channel_visibility' => 'online_only']` pada [`app/Models/Product.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Product.php#L17-L92) (dan default atribut pada [`app/Models/ProductVariant.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/ProductVariant.php#L15-L18)).
   - Menerapkan `Product::visibleOnline()` pada:
     - [`resources/views/home.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/home.blade.php#L253) (`$latestProducts` & `$featuredProducts` Lookbook).
     - [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L343-L347) (`mount()` kini otomatis mengembalikan **HTTP 404** jika URL produk `pos_only` / `is_custom = true` diakses langsung, dan `relatedProducts()` hanya mengambil produk online).
     - [`app/Livewire/Shop.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Shop.php#L67-L68) & [`app/Livewire/Search.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Search.php#L23-L24).
   - Menerapkan `Product::visibleInPos()` pada [`app/Livewire/PosManager.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1420-L1421).
2. **Penegakan 2 Kanal (ADR-001) + Dukungan Pemilahan Legacy `'both'` (Opsi A):**
   - [`app/Filament/Resources/Products/Tables/ProductsTable.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Tables/ProductsTable.php#L121-L246):
     - Bulk Action `change_channel_visibility` kini hanya menyediakan 2 opsi (`online_only` / `pos_only`).
     - Kolom badge & `SelectFilter` menambahkan label merah **"Legacy (Perlu Dipilah)"** untuk data lama `'both'` agar admin/klien mudah memfilter, mencentang serentak, lalu memindahkannya via Bulk Action.
   - Menghapus opsi & fallback `'both'` dari [`ProductImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductImporter.php#L202-L216), [`ProductVariantImporter.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Imports/ProductVariantImporter.php#L87-L95), [`ExportProductsCsvJob.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Jobs/ExportProductsCsvJob.php#L131), [`ListProducts.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Resources/Products/Pages/ListProducts.php#L47-L50), dan [`ExportData.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Filament/Pages/ExportData.php#L67-L70).
   - Menambahkan migrasi non-destruktif [`2026_11_23_070000_set_channel_visibility_default_to_online_only.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/database/migrations/2026_11_23_070000_set_channel_visibility_default_to_online_only.php) untuk mengubah default kolom `channel_visibility` menjadi `'online_only'` tanpa mengubah 57 data legacy `'both'`.
3. **Perbaikan Bug Stok Auto-Save Produk Kustom POS:**
   - Memperbarui [`app/Services/PosTransactionService.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L56-L90): mengikat `$lockedCustomProduct = $newProduct` saat `$saveToCatalog = true` sehingga stok berkurang akurat dari `$qty` ke `0` dan tercatat pada `StockLog`.

---

## 2. Bukti Verifikasi

| Pemeriksaan | Perintah / Target | Hasil |
| :--- | :--- | :--- |
| PHP Syntax Lint (`php -l`) | 15 file PHP baru/diubah | **0 syntax errors** |
| Code Style (`vendor/bin/pint --test`) | Migrasi & `ChannelIsolationAndPosCustomProductTest.php` | **PASS** (`{"tool":"pint","result":"passed"}`) |
| Feature Test Suite | `php artisan test --filter="ChannelIsolationAndPosCustomProductTest\|ProductImageSingleSourceOfTruthTest\|VariantImageMatchingTest\|ShopPageTest\|CartPageTest\|ProductBuyNowTest\|SecurityHardeningTest"` | **18/18 passed, 98 assertions** (3.28s) |

---

## 3. Catatan Operasional Produksi (Saat Nanti Deploy)
- Jalankan `php artisan migrate --force` untuk menerapkan default kolom `online_only` (non-destruktif, aman untuk 57 baris legacy `'both'`).
- Admin kemudian dapat masuk ke **Produk ➔ Filter Kanal Penjualan: "Legacy (Perlu Dipilah)" ➔ Centang Bulk ➔ Ubah Visibilitas (POS/Web)**.
- *(Belum ada push/deploy ke VPS pada sesi ini).*

---

## 4. Log Penyimpangan
- Tidak ada penyimpangan dari brief handoff `006`.
