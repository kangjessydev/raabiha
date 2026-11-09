# Handoff 004: inspektur → backend

- **Dari:** role-inspektur   **Untuk:** role-backend
- **Tanggal:** 2026-11-08
- **Status:** done
- **Prioritas:** tinggi

## Masalah / Kebutuhan
Berdasarkan hasil audit menyeluruh keamanan dan integritas data pada laporan [`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md), ditemukan 7 celah keamanan dan potensi cacat integritas data yang memerlukan penanganan (hardening) oleh `role-backend`.

## Bukti
1. `routes/web.php:63-72, 74-80`: Akses `/invoice/{orderNumber}` dan `/order-success?order=...` tanpa validasi kepemilikan pesanan (IDOR & kebocoran PII pelanggan).
2. `app/Livewire/Account.php:251-255`: `openRefundStatus($refundId)` mengambil model `RefundRequest` tanpa filter `where('user_id', Auth::id())` (IDOR finansial).
3. `app/Services/PosTransactionService.php:168, 176-188`: Nilai diskon voucher POS diterima mentah dari client tanpa rekalkulasi dan validasi status aktif di server.
4. `app/Services/PosTransactionService.php:167` vs `resources/views/livewire/pos-manager.blade.php:7798`: Threshold diskon manual kasir hanya dicek di browser, backend menerima sembarang angka tanpa guard supervisor PIN.
5. `app/Livewire/PosManager.php:1257, 1278, 1292`: Pemanggilan `$supervisor->name` tanpa null-check saat void order memicu error 500 jika PIN void dinonaktifkan.
6. `app/Livewire/PosManager.php:349, 1208`: Tidak ada rate limiting pada pengecekan PIN supervisor POS.
7. `app/Livewire/Cart.php:141, 151, 161`: `CartItem::find($itemId)` tidak diverifikasi terhadap relasi cart milik user yang sedang aktif.

## Tindakan yang Disarankan
1. **Perbaiki IDOR Invoice & Order Success:**
   - Tambahkan verifikasi otorisasi pada route closure `/invoice/{orderNumber}` (cek user ID pemilik pesanan atau signed URL / session ID untuk guest).
2. **Perbaiki IDOR Refund di Account:**
   - Ubah query di `app/Livewire/Account.php:253` menjadi `\App\Models\RefundRequest::with(['order', 'user'])->where('user_id', Auth::id())->find($refundId)`.
3. **Hardening Voucher POS:**
   - Validasi voucher dan hitung ulang nilai diskon voucher di `PosTransactionService.php` menggunakan service kalkulasi voucher atau validasi rule voucher server-side.
4. **Hardening Diskon Manual POS:**
   - Tambahkan validasi threshold diskon manual kasir di backend `PosTransactionService.php` dan validasi PIN supervisor jika melebihi batas.
5. **Cegah Crash 500 Void Order:**
   - Gunakan null-safe operator di `PosManager.php`: `($supervisor?->name ?? Auth::user()->name)`.
6. **Pasang Rate Limiting PIN Supervisor:**
   - Terapkan `RateLimiter::attempt()` untuk melindungi percobaan tebak PIN supervisor kasir.
7. **Perbaiki IDOR Cart Items:**
   - Scope pencarian item di `Cart.php`: `$this->cart ? $this->cart->items()->find($itemId) : null`.

## Di Luar Cakupan
- Tidak mengubah skema tabel database produksi.
- Tidak mengubah struktur payload gateway Tripay/Xendit yang sudah berjalan stabil.

## Kriteria Selesai
1. Akses ke invoice pesanan milik orang lain ditolak (403/redirect).
2. Akses ke refund request orang lain melalui Livewire ditolak / return null.
3. Nilai voucher dan diskon manual di POS terverifikasi di backend.
4. Void order tanpa PIN tidak crash.
5. Manipulasi item cart antar user tidak dapat dilakukan.
6. Seluruh pengujian fitur terkait lulus (`php artisan test`).

## Disetujui User
menunggu
