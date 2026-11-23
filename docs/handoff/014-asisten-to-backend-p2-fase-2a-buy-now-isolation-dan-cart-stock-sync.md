# Brief: P2 Fase 2A — Isolasi Penuh Buy Now & Auto-Clamp Sinkronisasi Stok Keranjang (`BKL-013` Backend)

- **Untuk:** `role-backend`   **Dari:** `role-asisten`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-backend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-backend-p2-fase-2a-buy-now-isolation-dan-cart-stock-sync.md`)
- **Disetujui user:** ya (aturan bisnis Buy Now terpisah & Opsi A auto-clamp `/cart` telah dikonfirmasi user pada 2026-11-23)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menyelesaikan sisi backend **`BKL-013`** dalam batas *Phase-Gating* (tepat **3 file produksi** + **1 file test**):
1. Memisahkan alur **"Beli Sekarang" (`is_buy_now = true`)** sepenuhnya dari keranjang reguler (`is_buy_now = false`) agar tidak mencampuri isi/kuantitas keranjang reguler.
2. Menerapkan sinkronisasi stok & **auto-clamp** otomatis pada `/cart` (Opsi A) ketika stok riil berkurang di bawah `cart_items.quantity` atau varian/produk dinonaktifkan, serta memblokir checkout untuk item habis/non-aktif.

## Konteks
- Acuan backlog: [`docs/backlog.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/backlog.md) (`BKL-013`).
- Model [`app/Models/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Models/Cart.php) sudah memiliki kolom `is_buy_now` di `$fillable`.
- Berkas target (tepat 3 file produksi + 1 file test):
  1. [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php) *(method `buyNow()` baris 549–571)*
  2. [`app/Livewire/Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php) *(method `getCartProperty()` baris 944–955, `getCheckoutItemsProperty()` baris 957–981, serta pembersihan session/cart pasca-order di baris 1557, 1654, 1752, 1768)*
  3. [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php) *(properti `$stockNotices`, sinkronisasi & auto-clamp pada `mount()`/`loadCart()`, guard `proceedToCheckout()`, dan `session(['checkout_is_buy_now' => false])`)*
  4. `tests/Feature/BuyNowIsolationAndCartStockSyncTest.php` *(test baru)*

## Yang diminta
1. **Isolasi Penuh `buyNow()` di [`ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L549-L571):**
   - `buyNow()` **TIDAK lagi memanggil `$this->addToCart()`** ke keranjang reguler (`is_buy_now = false`).
   - Validasi langsung pada `buyNow()`: cek `store_holiday_mode`, status produk aktif & visible online, pemilihan varian wajib & `is_active = true` jika `has_variants = true`, serta cek stok riil **hanya terhadap `$this->quantity`** (mengabaikan qty di keranjang reguler).
   - Gunakan/buat record `Cart` terpisah dengan `is_buy_now = true` (berdasarkan `user_id` jika login, atau `session_id` + `user_id = null` jika guest), bersihkan item lama di cart `is_buy_now = true` tersebut (`$buyNowCart->items()->delete()`), lalu simpan 1 `CartItem` baru untuk produk/varian terpilih dengan `$this->quantity`.
   - Set `session(['checkout_is_buy_now' => true, 'checkout_item_ids' => [(string) $buyNowItem->id]])` lalu redirect ke `/checkout`.
   - Pastikan keranjang reguler (`is_buy_now = false`) dan badge keranjang tidak berubah sama sekali.

2. **Isolasi Sumber Keranjang di [`Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L944-L981):**
   - Perbarui `getCartProperty()` agar membaca `is_buy_now = (bool) session('checkout_is_buy_now', false)` (dan untuk guest wajib `->whereNull('user_id')`).
   - Pastikan pada saat order berhasil dibuat (baris 1557, 1654, 1752, 1768), `session()->forget(['checkout_item_ids', 'checkout_is_buy_now'])` ikut membersihkan flag `checkout_is_buy_now`, dan hanya menghapus item/cart yang sedang di-checkout (sehingga cart reguler tetap utuh saat checkout via Buy Now).
   - Pastikan fallback jika `checkout_is_buy_now = true` tetapi cart Buy Now sudah kosong/tidak ditemukan tidak diam-diam mengambil cart reguler.

3. **Sinkronisasi Stok & Auto-Clamp (Opsi A) di [`Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php):**
   - Tambahkan properti publik `public array $stockNotices = [];` dan `public array $itemAvailability = [];` (berisi metadata per `item_id`: `available_stock`, `is_available`, `reason`) sebagai kontrak siap pakai untuk `role-frontend` di Fase 2B.
   - Tambahkan method `syncCartStockState()` yang dipanggil saat `mount()` dan `proceedToCheckout()` (serta menjaga konsistensi saat `loadCart()`):
     - Cek ketersediaan setiap item di keranjang reguler (`is_buy_now = false`):
       - Produk tidak ditemukan / `is_active = false` / bukan `visibleOnline` → tandai tidak tersedia (`reason`: `'Produk sudah tidak tersedia.'`), keluarkan dari `$this->selectedItems`, tambahkan pesan ke `$this->stockNotices`.
       - Produk bervarian tetapi varian hilang / `is_active = false` → tandai tidak tersedia (`reason`: `'Varian sudah tidak aktif.'`), keluarkan dari `$this->selectedItems`, tambahkan pesan ke `$this->stockNotices`.
       - Stok riil `<= 0` → tandai tidak tersedia (`reason`: `'Stok habis.'`), keluarkan dari `$this->selectedItems`, tambahkan pesan ke `$this->stockNotices`.
       - Stok riil `> 0` tetapi `$item->quantity > $availableStock` → **auto-clamp** `$item->update(['quantity' => $availableStock])`, segarkan nilai item, dan tambahkan pesan jelas ke `$this->stockNotices` (misal: `"Stok {nama} berkurang. Jumlah disesuaikan otomatis dari {qtyLama} menjadi {availableStock} pcs."`).
   - Di `proceedToCheckout()`:
     - Jalankan `syncCartStockState()`. Jika ada perubahan auto-clamp atau item terpilih menjadi tidak tersedia (`stock <= 0` / non-aktif), hentikan redirect dan tampilkan `session()->flash('error', ...)` agar user melihat pembaruan sebelum lanjut ke checkout.
     - Jika valid, set `session(['checkout_is_buy_now' => false, 'checkout_item_ids' => $this->selectedItems])` sebelum redirect ke `/checkout`.
   - Di `updatedSelectAll()`: hanya mencentang item yang `is_available === true`.

4. **Automated Tests & Dokumentasi:**
   - Buat `tests/Feature/BuyNowIsolationAndCartStockSyncTest.php` yang membuktikan:
     - User punya item produk X qty 3 di keranjang reguler (stok total 3) → klik `buyNow()` dengan qty 1 pada produk X tetap berhasil (mengabaikan qty keranjang reguler) dan tidak mengubah keranjang reguler.
     - `Checkout` saat `checkout_is_buy_now = true` hanya menampilkan item Buy Now, dan setelah selesai/dihapus, keranjang reguler tetap utuh.
     - `Cart::proceedToCheckout()` mereset `checkout_is_buy_now = false` sehingga checkout reguler tidak tertimpa sesi Buy Now sebelumnya.
     - Saat stok produk/varian turun di bawah qty di `/cart` (misal cart qty 5, stok turun jadi 2) → `/cart` otomatis meng-clamp qty DB menjadi `2` dan mengisi `$stockNotices`.
     - Saat stok turun jadi `0` atau varian jadi `is_active = false` → item otomatis ter-uncheck dari `selectedItems`, masuk `$stockNotices`, dan `proceedToCheckout()` memblokir checkout.
   - Pastikan test existing `ProductBuyNowTest`, `CartPageTest`, `CheckoutPageTest`, dan suite regresi P0/P1/P2 Fase 1 tetap **100% hijau**.
   - Perbarui `CHANGELOG.md` dan tulis laporan di `docs/reports/2026-11-23-backend-p2-fase-2a-buy-now-isolation-dan-cart-stock-sync.md`.

## Di luar cakupan
- **TIDAK mengubah** `resources/views/livewire/cart.blade.php` (akan dikerjakan oleh `role-frontend` di **P2 Fase 2B — handoff `015`** setelah kontrak backend Fase 2A selesai).
- **TIDAK mengubah** modul POS (`BKL-001/002/003`) maupun Widget Dashboard (`BKL-005`) (agenda **P3**).
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. `buyNow()` terisolasi 100% menggunakan `Cart` dengan `is_buy_now = true` tanpa mengubah keranjang reguler.
2. `Checkout` membaca cart sesuai flag `checkout_is_buy_now` dan membersihkan session dengan bersih.
3. `/cart` melakukan auto-clamp stok riil, uncheck & blokir checkout untuk item habis/non-aktif, serta mengekspos `$stockNotices` & `$itemAvailability` untuk UI.
4. Seluruh automated test baru & test regresi lulus 100%, `CHANGELOG.md` & laporan tertulis dengan bukti konkret.

## Pertanyaan terbuka
- Tidak ada (aturan bisnis telah dikonfirmasi user).
