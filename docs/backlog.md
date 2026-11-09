# Backlog Perbaikan & Rencana Fitur

Dokumen ini mencatat daftar temuan, rencana peningkatan, dan perbaikan tertunda yang menunggu permintaan atau konfirmasi lebih lanjut dari pihak klien/bisnis sebelum diimplementasikan.

---

## 1. Modul Point of Sale (POS) & Terminal Kasir

### BKL-001: Hardening Otorisasi Diskon Manual Kasir di Sisi Server
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #4.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** *Pending Client Request* (Menunggu instruksi klien).
- **Deskripsi:**
  Saat ini, batasan diskon manual kasir (`pos_manual_discount_max_percent_without_pin` sebesar 20% dan `pos_manual_discount_max_rp_without_pin` sebesar Rp 50.000) baru divalidasi pada antarmuka pengguna (`resources/views/livewire/pos-manager.blade.php:7798-7800`). Di backend ([`app/Services/PosTransactionService.php:167`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Services/PosTransactionService.php#L167)), nilai diskon manual diterima langsung dari input klien tanpa validasi threshold toko atau kewajiban verifikasi PIN supervisor.
- **Rencana Tindakan:**
  Tambahkan middleware/service guard di `PosTransactionService::createOrder()` untuk memeriksa persentase dan nominal diskon manual terhadap pengaturan toko, serta mewajibkan `supervisor_id` dan `supervisor_pin` yang valid jika melewati ambang batas tanpa PIN.

---

### BKL-002: Null-Safe Guard pada Nama Supervisor saat Pembatalan Nota (`voidOrder`)
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #5.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** *Pending Client Request* (Menunggu instruksi klien).
- **Deskripsi:**
  Pada [`app/Livewire/PosManager.php:1257, 1278, 1292`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/app/Livewire/PosManager.php#L1257), string deskripsi dan log stok memanggil `$supervisor->name`. Jika pengaturan toko `pos_require_pin_for_void` disetel menjadi `false` (kasir boleh void tanpa supervisor), variabel `$supervisor` bernilai `null` dan memicu error `Attempt to read property "name" on null` yang menggagalkan proses void nota.
- **Rencana Tindakan:**
  Ubah akses properti menjadi null-safe: `($supervisor?->name ?? Auth::user()->name)`.

---

### BKL-003: Rate Limiting Percobaan Tebak PIN Supervisor POS
- **Sumber Temuan:** Laporan Audit Inspektur ([`docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md`](file:///home/kangjessy/Documents/projects/raabiha-ecommerce/docs/reports/2026-11-08-inspektur-audit-security-and-data-integrity.md)) - Temuan #6.
- **Tingkat Prioritas:** Sedang (Medium).
- **Status:** *Pending Client Request* (Menunggu instruksi klien).
- **Deskripsi:**
  Pengecekan PIN supervisor (4-6 digit) pada method pengambilalihan sesi paksa (`forceTakeoverWithSupervisor`), kas kecil (`addPettyCash`), buka laci (`openManualDrawer`), dan void nota belum dilengkapi throttling/rate limiter. Kasir dapat melakukan brute-force PIN tanpa ada batas percobaan.
- **Rencana Tindakan:**
  Pasang `Illuminate\Support\Facades\RateLimiter::attempt()` pada fungsi-fungsi verifikasi PIN supervisor dengan batas maksimal 5 kali percobaan salah sebelum dilakukan penguncian sementara (lockout) 10-15 menit.
