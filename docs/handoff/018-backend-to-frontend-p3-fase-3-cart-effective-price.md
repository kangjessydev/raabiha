# Brief: P3 Fase 3 (Ringan) — Penyelarasan Harga Per Baris `/cart` ke `effective_price` (`S-01`)

- **Untuk:** `role-frontend`   **Dari:** `role-backend`   **Tanggal:** 2026-11-23
- **Status:** `closed` (selesai dikerjakan oleh `role-frontend` pada 2026-11-23; laporan di `docs/reports/2026-11-23-frontend-p3-fase-3-cart-effective-price.md`)
- **Disetujui user:** ya (user memilih menyisipkan `S-01` pada P3)
- **Branch Aktif:** `development` *(DILARANG deploy ke VPS dan DILARANG menyentuh branch `main`)*

## Tujuan
Menyelaraskan tampilan harga per baris di [`resources/views/livewire/cart.blade.php:73`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L73) agar memakai `effective_price` seperti kalkulasi subtotal di [`app/Livewire/Cart.php:87,376`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L376).

## Yang diminta
1. Di [`resources/views/livewire/cart.blade.php:73`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L73), ubah:
   `$price = $item->variant && $item->variant->price ? $item->variant->price : $item->product->price;`
   menjadi:
   `$price = $item->variant ? $item->variant->effective_price : $item->product->effective_price;`
2. Jalankan `npm run build` & `php artisan test --filter="BuyNowIsolationAndCartStockSyncTest|CartPageTest"`, perbarui `CHANGELOG.md`, tutup handoff `018`, dan buat laporan ringkas.

## Di luar cakupan
- Tidak mengubah file PHP backend maupun file Blade lain.
- Tidak deploy ke VPS dan tidak menyentuh branch `main`.

## Kriteria selesai
1. Harga per baris `/cart` 100% sinkron dengan `effective_price` di `Cart.php`.
2. `npm run build` dan test lulus 100%.
