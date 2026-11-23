# Brief: P2 Fase 2B — Banner Notifikasi Auto-Clamp Stok & Status Ketersediaan Item di `/cart` (`BKL-013` Frontend)

- **Untuk:** `role-frontend`   **Dari:** `role-backend`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-frontend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-frontend-p2-fase-2b-cart-stock-notices-ui.md`)
- **Disetujui user:** ya (bagian dari rencana P2 Fase 2 — Opsi A yang telah dikonfirmasi user)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menghubungkan kontrak backend P2 Fase 2A (`$stockNotices`, `$itemAvailability`, dan `session('error')` dari [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php)) ke tampilan [`resources/views/livewire/cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php) agar pelanggan melihat peringatan jelas saat stok berkurang (auto-clamp) atau varian/produk habis/non-aktif.

## Konteks & Kontrak Backend Siap Pakai
Di [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php):
- `$stockNotices` (`array<int, string>`): Daftar pesan manusiawi saat stok otomatis di-clamp atau item tidak tersedia (contoh: `'Stok "Khimar Ceruty" berkurang. Jumlah di keranjang otomatis disesuaikan dari 5 menjadi 2 pcs.'` atau `'Item "Gamis (Maroon - M)" tidak dapat di-checkout: Varian sudah tidak aktif.'`).
- `$itemAvailability` (`array<string, array{is_available: bool, available_stock: int, reason: ?string}>`): Status ketersediaan & stok riil per `(string) $item->id`.
- `session('error')`: Pesan error ketika `incrementQuantity()` mencapai batas stok atau `proceedToCheckout()` ditolak.
- **Berkas target (hanya 1 file produksi):**
  - [`resources/views/livewire/cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php)

## Yang diminta
1. **Banner Notifikasi Stok & Error di Atas Daftar Keranjang:**
   - Tampilkan banner alert yang rapi (selaras palet editorial Raabiha: `#fcf9f5`, `#1c1c1a`, `#064e3b`, `#ba1a1a`, amber/warning untuk auto-clamp) apabila `!empty($stockNotices)` atau `session()->has('error')`.
   - Gunakan `role="alert"` / `aria-live="polite"` untuk aksesibilitas.
2. **Status Per Item Berdasarkan `$itemAvailability[(string) $item->id]`:**
   - Jika `!$isAvailable`:
     - Tampilkan badge status jelas (`$availability['reason']`, misal `Stok habis.` / `Varian sudah tidak aktif.`).
     - Beri visual redup (`opacity-60`) pada kartu item dan **disable checkbox** pemilihan item.
     - **Disable tombol `-` dan `+`** dengan `disabled` & `cursor-not-allowed`.
   - Jika `$isAvailable`:
     - Disable tombol `-` saat `$item->quantity <= 1` dan disable tombol `+` saat `$item->quantity >= $availableStock`.
     - Tampilkan info stok tersisa secara halus jika `$availableStock <= 5` (misal `Sisa {{ $availableStock }} pcs`).
3. **Kepatuhan Anti-AI-Slop (Vercel Web Interface Guidelines):**
   - Tambahkan `width` & `height` eksplisit pada `<img>` item keranjang (Zero CLS).
   - Tambahkan `tabular-nums` pada angka harga subtotal, qty, dan ringkasan belanja.
   - Tambahkan `aria-label` pada tombol `-` / `+` / hapus, serta ganti `focus:outline-none` menjadi `focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#064e3b]`.
4. **Verifikasi:**
   - Jalankan `npm run build` dan `php artisan test --filter="BuyNowIsolationAndCartStockSyncTest|CartPageTest|ProductBuyNowTest|CheckoutPageTest"`.
   - Perbarui `CHANGELOG.md`, tandai `BKL-013` selesai di `docs/backlog.md`, tutup handoff `015`, dan tulis laporan di `docs/reports/2026-11-23-frontend-p2-fase-2b-cart-stock-notices-ui.md`.

## Di luar cakupan
- **TIDAK mengubah** file PHP backend (`Cart.php`, `Checkout.php`, `ProductDetail.php`).
- **TIDAK mengubah** halaman lain di luar `cart.blade.php`.
- **TIDAK deploy** ke VPS dan **TIDAK menyentuh branch `main`**.

## Kriteria selesai
1. Banner `$stockNotices` & `session('error')` tampil jelas dan responsif (Desktop & Mobile).
2. Item habis/non-aktif menampilkan badge alasan, checkbox & tombol qty ter-disable, serta tombol `+` ter-disable pada batas stok riil.
3. `npm run build` dan seluruh feature test terkait lulus 100%.
4. `CHANGELOG.md`, `docs/backlog.md` (`BKL-013` -> selesai), handoff `015` (`closed`), dan laporan tertulis dengan bukti konkret.
