# Laporan Audit Forensik: P2 Fase 2 (`BKL-013` — Isolasi Buy Now & Auto-Clamp Stok `/cart`)

- **Peran:** `role-inspektur` *(Mode Audit Saja / Read-Only — 0 baris kode diubah)*
- **Tanggal:** 2026-11-23
- **Branch:** `development`
- **Rujukan Standar:** Vercel Web Interface Guidelines v1.0 (`.agents/references/anti-ai-slop-ui.md`)
- **Cakupan Audit:**
  - [`app/Livewire/ProductDetail.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php)
  - [`app/Livewire/Checkout.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php)
  - [`app/Livewire/Cart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php)
  - [`resources/views/livewire/cart.blade.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php)
  - Verifikasi silang: [`app/Livewire/MiniCart.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/MiniCart.php), [`app/Livewire/CartBadge.php`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/CartBadge.php)

---

## 1. Ringkasan Kesehatan Kode
- **Status:** **Lulus Audit / Seluruh P2 Siap Commit & Push ke Branch `development`**
- **Ringkasan Temuan pada Perubahan P2 Fase 2:**
  - Kritis: **0**
  - Tinggi: **0**
  - Sedang (Pra-eksisting di luar regresi P2): **1** *(dicatat untuk backlog/polishing opsional, tidak memblokir commit P2)*
  - Rendah: **0**
- **Verifikasi Test & Build:**
  - `npm run build`: **Lulus** (`✓ built in 4.61s`).
  - Feature tests: **38/38 passed (212 assertions)** untuk P2 Fase 2A + regresi penuh, dan **16/16 passed (74 assertions)** pasca-edit Fase 2B.

---

## 2. Matriks Sinkronisasi Alur Data (UI ➔ Backend ➔ DB)

| Elemen UI / Aksi | Logika Backend | Tabel & Kolom DB / Session | Status |
| :--- | :--- | :--- | :--- |
| Tombol "Beli Sekarang" di Detail Produk | [`ProductDetail::buyNow()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/ProductDetail.php#L575-L618) via `resolveSelectionForCart()` | `carts (is_buy_now = true)`, `cart_items`, `session(checkout_is_buy_now = true, checkout_item_ids)` | **Sinkron Penuh** |
| Halaman `/checkout` (daftar item, berat ongkir, & pembersihan pasca-order) | [`Checkout::getCartProperty()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Checkout.php#L944-L959) & `cleanupCheckedOutCart()` | Filter `carts.is_buy_now` sesuai `session('checkout_is_buy_now')`; hapus `cart_items` & `carts` kosong tanpa menyentuh cart reguler | **Sinkron Penuh** |
| MiniCart & CartBadge Header | [`MiniCart.php:24,30`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/MiniCart.php#L24-L30) & [`CartBadge.php:23,29`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/CartBadge.php#L23-L29) | Filter `carts.is_buy_now = false` (tidak terpengaruh Buy Now) | **Sinkron Penuh** |
| Banner `$stockNotices`, Badge Status, & Tombol `- / +` di `/cart` | [`Cart::syncCartStockState()`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L154-L239) & [`cart.blade.php:32-150`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L32-L150) | Auto-clamp `cart_items.quantity` di DB, uncheck & blokir checkout item stok `0`/non-aktif, reset `checkout_is_buy_now = false` | **Sinkron Penuh** |

---

## 3. Audit Anti-AI-Slop (Vercel Web Interface Guidelines v1.0)
- `role="alert"` & `aria-live="polite"` pada banner `$stockNotices` serta `role="alert"` pada error banner mobile: **Lulus**.
- Dimensi eksplisit `width="240" height="280" loading="lazy"` pada gambar item keranjang (Zero CLS): **Lulus**.
- `tabular-nums` pada subtotal item, qty, sisa stok, ringkasan subtotal/diskon/total Desktop & Mobile, serta counter tombol checkout: **Lulus**.
- `aria-label` dan `focus-visible:ring-2 focus-visible:ring-[#064e3b]` pada kontrol interaktif yang disentuh: **Lulus**.
- Tidak ada `transition-all` baru yang ditambahkan: **Lulus**.

---

## 4. Temuan Catatan Pra-Eksisting (Non-Blokir P2)

### `S-01` (Sedang — Pra-Eksisting di Luar Regresi P2)
- **Lokasi:** [`resources/views/livewire/cart.blade.php:73`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/resources/views/livewire/cart.blade.php#L73) vs [`app/Livewire/Cart.php:87,376`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/Cart.php#L376)
- **Temuan:** Baris tampilan harga per item di `cart.blade.php:73` memakai `$item->variant->price` / `$item->product->price`, sedangkan kalkulasi `$subtotal` di `Cart.php:376` memakai `effective_price`.
- **Dampak:** Bila suatu produk/varian memiliki harga diskon/promo aktif (`effective_price != price`), angka subtotal di baris item bisa berbeda dengan Subtotal di kolom Ringkasan Pesanan.
- **Rekomendasi:** Dapat diselaraskan menjadi `$item->variant ? $item->variant->effective_price : $item->product->effective_price` pada sesi polishing berikutnya (tidak memblokir commit P2).

---

## 5. Kesimpulan
- Seluruh perubahan **P2 Fase 1 (`BKL-009`, `BKL-004`)** dan **P2 Fase 2 (`BKL-013`)** lulus audit forensik tanpa temuan regresi.
- **Aman untuk commit & push ke branch `development`**, lalu melanjutkan ke **P3 (`BKL-001/002/003` POS + `BKL-005` Widget Stok Habis)**.
