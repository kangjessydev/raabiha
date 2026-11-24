# Brief: Sinkronisasi `is_custom = false` Saat Produk POS Dipindah ke Kanal E-Commerce

- **Untuk:** role-backend   **Dari:** role-asisten   **Tanggal:** 2026-11-24
- **Status:** `closed`
- **Disetujui user:** ya

## Tujuan
Memastikan produk yang awalnya dibuat via POS Kasir (`is_custom = true`) otomatis menjadi `is_custom = false` ketika Admin memindahkan `channel_visibility` menjadi `online_only`, sehingga langsung tampil di storefront E-Commerce (`/shop`, `/product/{slug}`, `/sitemap.xml`).

## Yang diminta
- Tambahkan hook `saving(Product $product)` di `app/Observers/ProductObserver.php` untuk mereset `is_custom = false` saat `channel_visibility === 'online_only'`.
- Tambahkan automated test di `tests/Feature/ChannelIsolationAndPosCustomProductTest.php`.
- Rilis patch `v1.1.1` ke `main` & deploy ke `<VPS_CLOUDPANEL>`, serta sinkronkan produk `online_only` yang terlanjur masih `is_custom = 1`.

## Di luar cakupan
- Mengubah produk `pos_only` atau menonaktifkan isolasi 2 kanal (`ADR-001`).

## Kriteria selesai
- Tes lulus 100%, rilis `v1.1.1` ter-deploy di `<VPS_CLOUDPANEL>`, dan `https://www.raabiha.com/product/soraya-abaya-textured` mengembalikan HTTP 200 serta tampil di `/shop`.
