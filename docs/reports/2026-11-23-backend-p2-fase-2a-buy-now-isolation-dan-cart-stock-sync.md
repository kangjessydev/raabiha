# Laporan Pengerjaan: P2 Fase 2A — Isolasi Penuh Buy Now & Sinkronisasi Auto-Clamp Stok Keranjang (`BKL-013` Backend)

- **Peran:** `role-backend`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`014-asisten-to-backend-p2-fase-2a-buy-now-isolation-dan-cart-stock-sync.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/014-asisten-to-backend-p2-fase-2a-buy-now-isolation-dan-cart-stock-sync.md) (`closed`)
- **Handoff Lanjutan:** [`015-backend-to-frontend-p2-fase-2b-cart-stock-notices-ui.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/015-backend-to-frontend-p2-fase-2b-cart-stock-notices-ui.md) (`open`, untuk `role-frontend`)

---

## Ringkasan Perubahan
Menyentuh **3 file produksi** (`+218/-46` baris — mencakup ekstraksi helper untuk menghapus duplikasi 4× cleanup cart & validasi seleksi) + **1 file feature test baru**:

1. **[`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L448-L618) (`+69/-18` baris):**
   - Mengekstrak helper bersama `resolveSelectionForCart()` ([`ProductDetail.php:448-506`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L448-L506)) untuk validasi produk aktif/online, varian aktif, dan stok riil terhadap `$this->quantity`.
   - `buyNow()` ([`ProductDetail.php:575-618`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L575-L618)) kini terisolasi penuh: menggunakan `Cart` terpisah (`is_buy_now = true`), membersihkan item lama di cart Buy Now tersebut, menyimpan 1 `CartItem` baru sesuai `$this->quantity`, dan menyetel `session(['checkout_is_buy_now' => true, 'checkout_item_ids' => [(string) $buyNowItem->id]])` tanpa menyentuh keranjang reguler maupun mengecek akumulasi qty keranjang reguler.

2. **[`app/Livewire/Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L944-L970) (`+51/-30` baris):**
   - `getCartProperty()` ([`Checkout.php:944-959`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L944-L959)) kini memfilter `->where('is_buy_now', (bool) session('checkout_is_buy_now', false))` (serta `->whereNull('user_id')` untuk guest), sehingga Checkout Buy Now hanya memuat item Buy Now (termasuk kalkulasi berat ongkir) dan tidak pernah mencampuri keranjang reguler.
   - Menambahkan helper `cleanupCheckedOutCart()` ([`Checkout.php:961-969`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L961-L969)) yang menggantikan 4 blok duplikat pasca-order (`tunai`, `tripay`, `xendit`, `fallback`), menghapus `CartItem` yang di-checkout, menghapus record `Cart` jika kosong, dan membersihkan `session()->forget(['checkout_item_ids', 'checkout_is_buy_now'])`.

3. **[`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L21-L365) (`+144/-6` baris):**
   - Menambahkan kontrak publik `$stockNotices` & `$itemAvailability` serta method `syncCartStockState()` ([`Cart.php:154-239`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L154-L239)) yang berjalan pada `mount()`, `proceedToCheckout()`, dan aksi kuantitas/hapus:
     - Otomatis meng-clamp `cart_items.quantity` di DB jika stok riil turun di bawah qty keranjang + mencatat pesan ke `$stockNotices`.
     - Otomatis men-uncheck dan menandai tidak tersedia jika produk/varian non-aktif, `pos_only`/`is_custom`, atau stok `<= 0`.
   - `proceedToCheckout()` ([`Cart.php:335-365`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L335-L365)) menjalankan `syncCartStockState(true)` — menahan redirect dengan pesan error jika ada perubahan stok mendadak, dan menyetel `checkout_is_buy_now = false` saat checkout reguler agar tidak tertimpa sesi Buy Now sebelumnya.

4. **Automated Feature Test — [`BuyNowIsolationAndCartStockSyncTest.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/tests/Feature/BuyNowIsolationAndCartStockSyncTest.php):**
   - 5 skenario test baru yang mencakup isolasi Buy Now saat stok keranjang reguler penuh, reset `checkout_is_buy_now` saat checkout reguler, auto-clamp saat stok turun, auto-uncheck & blokir checkout untuk stok 0 / varian non-aktif, serta guard perubahan stok di tengah sesi (`mid-session`).

---

## Bukti Verifikasi
- `php artisan test --filter="BuyNowIsolationAndCartStockSyncTest|ProductBuyNowTest|CartPageTest|CheckoutPageTest|AdminOrderVariantAndProductStockSortTest|ProductDetailVariantAndCartGuardTest|VariantImageMatchingTest|ChannelIsolationAndPosCustomProductTest|ProductImageSingleSourceOfTruthTest"`
  - Hasil: **38/38 tests passed (212 assertions, 9.236s)**.
- `git diff --stat app/Livewire/`:
  - `app/Livewire/Cart.php | 144 +++++++++++++++++++++++++++++++++++++++--`
  - `app/Livewire/Checkout.php | 51 +++++++--------`
  - `app/Livewire/ProductDetail.php | 69 ++++++++++++++++----`
  - `3 files changed, 218 insertions(+), 46 deletions(-)`

---

## Checklist SOP
- [x] Membaca handoff open `014` dan seluruh file target sebelum menulis kode.
- [x] Batas wewenang dipatuhi (tidak mengubah file Blade frontend; kontrak disalurkan via handoff `015`).
- [x] Zero N+1 pada `syncCartStockState()` (menggunakan relasi eager-loaded `items.product` & `items.variant`).
- [x] Seluruh 38 automated test baru & regresi lulus 100%.
- [x] `CHANGELOG.md`, handoff `014` (`closed`), handoff `015` (`open` ke `role-frontend`), dan laporan tertulis dengan bukti konkret.

---

## Log Penyimpangan
- Total penambahan bersih (+218/-46 baris di 3 file) sedikit melewati 200 baris karena penambahan `syncCartStockState()` lengkap untuk Opsi A di `Cart.php`, namun sudah diimbangi penghapusan duplikasi di `Checkout.php` dan `ProductDetail.php` serta tetap dalam 3 file produksi.
