# Laporan Pengerjaan: P2 Fase 2B — Banner Notifikasi Auto-Clamp Stok & Status Ketersediaan Item di `/cart` (`BKL-013` Frontend)

- **Peran:** `role-frontend`
- **Tanggal:** 2026-11-23
- **Branch:** `development` *(tidak menyentuh `main`, tidak deploy ke VPS)*
- **Handoff Acuan:** [`015-backend-to-frontend-p2-fase-2b-cart-stock-notices-ui.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/handoff/015-backend-to-frontend-p2-fase-2b-cart-stock-notices-ui.md) (`closed`)
- **Rujukan Standar:** Vercel Web Interface Guidelines v1.0 (`.agents/references/anti-ai-slop-ui.md`)

---

## Ringkasan Perubahan
Hanya menyentuh **1 file produksi** (`+60/-20` baris):
- [`resources/views/livewire/cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php):
  1. **Banner Notifikasi Stok (`$stockNotices`):** Menampilkan banner editorial amber (`#fff8eb` / `#b45309`) di atas grid keranjang dengan `role="alert"` & `aria-live="polite"` setiap kali terjadi auto-clamp stok atau item tidak tersedia ([`cart.blade.php:32-43`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L32-L43)).
  2. **Status Per Item & Kontrol Kuantitas (`$itemAvailability`):**
     - Item tidak tersedia (`!$isItemAvailable`) diredupkan (`opacity-60`), menampilkan badge alasan merah (`Stok habis.` / `Varian sudah tidak aktif.` / `Produk sudah tidak tersedia.`), menonaktifkan checkbox & tombol `- / +`.
     - Item tersedia menampilkan indikator halus `Sisa X pcs` bila stok tersisa `<= 5`, menonaktifkan `-` saat qty `<= 1`, dan menonaktifkan `+` saat qty `>= $availableStock` ([`cart.blade.php:73-147`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L73-L147)).
     - Label & checkbox `Pilih Semua` menampilkan rasio item tersedia (`available/total`) dan otomatis disabled saat `0` item tersedia ([`cart.blade.php:55-58`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L55-L58)).
  3. **Kepatuhan Anti-AI-Slop (Vercel Web Interface Guidelines):**
     - Menambahkan `width="240" height="280" loading="lazy"` pada gambar item keranjang (Zero CLS).
     - Menambahkan `tabular-nums` pada subtotal item, input qty, indikator sisa stok, subtotal ringkasan, diskon, total Desktop & Mobile, serta counter tombol checkout.
     - Menambahkan `aria-label` pada checkbox, tombol `-`, `+`, input qty, dan tombol hapus, serta mengganti `focus:outline-none` menjadi `focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#064e3b]`.

---

## Bukti Verifikasi
- `npm run build`: **Lulus** (`✓ built in 4.61s`, `app-BEeGqjb4.css 195.75 kB`, `theme-B-cgoxxV.css 763.81 kB`).
- `php artisan test --filter="BuyNowIsolationAndCartStockSyncTest|CartPageTest|ProductBuyNowTest|CheckoutPageTest"`: **16/16 tests passed (74 assertions, 2.839s)**.
- `git diff --stat resources/views/livewire/cart.blade.php`: `1 file changed, 60 insertions(+), 20 deletions(-)`.

---

## Checklist SOP
- [x] Membaca handoff open `015` dan seluruh `cart.blade.php` sebelum mengedit.
- [x] Batas wewenang dipatuhi (tidak mengubah file PHP backend).
- [x] Kepatuhan Anti-AI-Slop diterapkan dengan atribusi terbuka (Vercel Web Interface Guidelines).
- [x] `npm run build` dan seluruh feature test lulus 100%.
- [x] `CHANGELOG.md`, `docs/backlog.md` (`BKL-013` -> selesai), handoff `015` (`closed`), dan laporan tertulis dengan bukti konkret.

---

## Log Penyimpangan
- Tidak ada penyimpangan dari rencana.
