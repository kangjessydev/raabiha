# Laporan Pengerjaan: P3 Fase 3 — Penyelarasan Harga Per Baris `/cart` ke `effective_price` (`S-01`)

- **Peran:** `role-frontend`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`018-backend-to-frontend-p3-fase-3-cart-effective-price.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/018-backend-to-frontend-p3-fase-3-cart-effective-price.md) (`closed`)

## Ringkasan Perubahan
- [`resources/views/livewire/cart.blade.php:73`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L73) (`+1/-1` baris): mengubah kalkulasi `$price` per baris menjadi `$item->variant ? $item->variant->effective_price : $item->product->effective_price` agar 100% sinkron dengan `app/Livewire/Cart.php:87,376`.

## Bukti Verifikasi
- `npm run build`: **Lulus** (`✓ built in 4.56s`).
- `php artisan test --filter="BuyNowIsolationAndCartStockSyncTest|CartPageTest"`: **7/7 passed (38 assertions, 1.78s)**.
