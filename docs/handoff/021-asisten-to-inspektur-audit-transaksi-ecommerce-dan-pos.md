# Brief: Audit Forensik Alur Transaksi E-Commerce & POS (Pra-Rilis v1.1.0)

- **Untuk:** role-inspektur   **Dari:** role-asisten   **Tanggal:** 2026-11-23
- **Status:** `closed` (Laporan: [2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md](../reports/2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md))
- **Disetujui user:** ya

## Tujuan
Melakukan audit forensik (*Audit-Only / Read-Only*) pada seluruh alur transaksi kritis E-Commerce dan POS untuk memastikan operasional jualan aman dari *race condition*, selisih stok, manipulasi harga/voucher/diskon, duplikasi pembayaran/webhook, dan celah integritas kas sebelum rilis `v1.1.0` naik ke production.

## Konteks
- **Profil Risiko:** **Ketat** ([project-brief.md](../project-brief.md#L13-L18)) — transaksi uang riil, pembayaran online (Tripay/Xendit), kasir POS (tunai/non-tunai), dan pembukuan kas (*cashflows*).
- **Baseline:** Semua pekerjaan P0–P4 & UX fixes sudah selesai dan di-commit di branch `development` (`08977841`).
- **ADR Berlaku ([decisions.md](../decisions.md)):**
  - `ADR-001`: Isolasi 2 kanal (`online_only` & `pos_only`).
  - `ADR-002`: Fitur Reseller ditangguhkan.
  - `ADR-003`: Tidak menerapkan rate limit ketat pada PIN supervisor POS atas kebijakan klien.
- **Mode:** **Audit-Only Mode** — dilarang mengubah satu baris kode pun. Temuan diklasifikasikan berdasarkan tingkat risiko (*Kritis / Blocker Rilis*, *Sedang*, *Ringan*) dengan bukti `file:baris`.

## Yang diminta
Audit mendalam dan terstruktur pada 6 area transaksi inti berikut:
1. **Race Condition & Penguncian Stok (`lockForUpdate` / DB Transaction):**
   - Alur Checkout E-Commerce (`app/Livewire/Checkout.php` & service terkait): apakah pengurangan stok produk/varian memakai transaksi DB + `lockForUpdate()`? Bagaimana jika 2 pembeli checkout 1 stok terakhir di milidetik yang sama?
   - Alur POS Checkout (`app/Services/PosTransactionService.php` & `app/Livewire/PosManager.php`): apakah pengurangan stok kasir + validasi stok cukup sudah atomic dan tahan klik ganda (*double submit*)?
   - Pemulihan stok saat order dibatalkan / kedaluwarsa / *void* POS: apakah ada risiko stok dikembalikan dua kali (*double restock*)?
2. **Idempotency & Integritas Pembayaran Online (Tripay / Xendit Webhook):**
   - Verifikasi signature webhook (`app/Http/Controllers/` terkait Tripay/Xendit) dan validasi nominal bayar terhadap `orders.total_amount`.
   - Penanganan webhook duplikat/replay (jika gateway mengirim callback `PAID` dua kali, apakah order, stok, poin loyalitas, atau `cashflows` ikut diproses dua kali?).
   - Perlindungan *double click* saat `placeOrder()` di Checkout.
3. **Manipulasi Harga, Voucher/Kupon, & Ongkir di Sisi Klien:**
   - Pastikan seluruh kalkulasi subtotal, harga promo (`effective_price`), potongan voucher/poin, ongkir, dan grand total dihitung ulang 100% di sisi server (tidak mempercayai angka dari state Livewire/request klien).
   - Race condition kuota voucher (`usage_limit` / `used_count` / penggunaan per user): apakah aman dari pemakaian bersamaan?
4. **Integritas Transaksi POS, Shift Kasir, Void, & Cashflow:**
   - Konsistensi pencatatan `orders`, `order_items`, `stock_logs`, `pos_sessions`, dan `cashflows` saat checkout tunai/non-tunai maupun saat `voidOrder()`.
   - Verifikasi ulang otorisasi diskon manual & status shift aktif (apakah transaksi bisa lolos tanpa shift terbuka atau memakai shift yang sudah ditutup?).
5. **Alur Dasar E-Commerce End-to-End (Guest & Login):**
   - Add to Cart → Update Qty → Buy Now terisolasi → Checkout → Order Created → Status Tracking & Pengurangan Stok.
   - Cek edge case: produk/varian dinonaktifkan atau harga berubah di tengah proses checkout.
6. **Rekomendasi Go / No-Go Rilis `v1.1.0`:**
   - Pisahkan secara tegas mana temuan **Blocker (Wajib diperbaiki sebelum deploy)** vs **Non-Blocker (Aman dideploy dulu, bisa dicatat sebagai catatan pasca-rilis)**.

## Di luar cakupan
- Mengubah kode aplikasi, membuat migrasi, atau menjalankan perintah di VPS.
- Modul Blog, SEO, atau fitur Reseller (`ADR-002`).
- Mengungkit ulang temuan minor yang sudah di-freeze user (`S-01`, `R-01`, `R-02` di laporan [2026-11-23-inspektur-audit-menyeluruh-ecommerce-dan-pos.md](../reports/2026-11-23-inspektur-audit-menyeluruh-ecommerce-dan-pos.md)) kecuali berdampak langsung pada keamanan/integritas transaksi.

## Kriteria selesai
- Seluruh 6 area di atas diaudit berbasis bukti konkret (`file:baris` + alur eksekusi).
- Laporan lengkap tersimpan di `docs/reports/2026-11-23-inspektur-audit-transaksi-ecommerce-dan-pos.md`.
- Keputusan jelas apakah ada **Blocker Rilis** untuk `v1.1.0`; jika ada, buatkan rekomendasi handoff perbaikan untuk disetujui user sebelum eksekusi.

## Pertanyaan terbuka
- Tidak ada (seluruh ruang lingkup sudah disetujui user).
