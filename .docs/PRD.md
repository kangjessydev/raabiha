# Product Requirements Document (PRD)

## 1. Latar Belakang & Tujuan
Raabiha e-commerce dibangun sebagai solusi strategis untuk mengurangi ketergantungan pada *marketplace* pihak ketiga (seperti TikTok Shop dan Shopee) yang memiliki potongan biaya admin yang semakin membengkak. 
Tujuan utamanya adalah meningkatkan margin keuntungan penjualan langsung (Direct-to-Consumer) sekaligus memberikan penawaran eksklusif (seperti sistem *Reseller* dan promo khusus) yang tidak tersedia di *marketplace*.

## 2. Target Audiens
- **Demografi:** Wanita muslim.
- **Usia:** 18 - 30 tahun.
- **Segmen:** Semua kalangan (inklusif).

## 3. Fitur Utama (Fase 1 - MVP)
Fase MVP mengacu pada pembuatan kapabilitas *e-commerce* secara custom dari nol menggunakan Laravel, disesuaikan persis dengan kebutuhan Raabiha agar lebih ringan, cepat, dan mudah di-maintain.
- **Katalog & Belanja:** Etalase produk lengkap dengan struktur database custom.
- **Sistem Pengiriman:** Cek ongkir otomatis & fitur gratis ongkir (Integrasi RajaOngkir).
- **Pembayaran:** *Payment Gateway* yang terotomatisasi (Integrasi Tripay / Xendit).
- **Manajemen Pengguna:** Dasbor pelanggan publik yang SEO-friendly dan ringan.
- **Dasbor Admin Eksekutif:** Menggunakan **Filament Admin Panel** sebagai pusat komando operasional toko (manajemen pesanan, produk, user, dan laporan) yang terpisah dari area publik.

## 4. Fitur Masa Depan (Fase 2)
- **Virtual Fitting Room (Simulasi Ganti Baju):** 
  - Konsep *gamification* mirip kustomisasi karakter.
  - Pengguna dapat mengunggah *selfie* agar wajahnya diterapkan pada avatar.
  - Kustomisasi ukuran tubuh yang presisi (tinggi badan, berat badan, lingkar dada, perut, pinggang, dll).
  - Pengguna dapat mencoba pakaian dari katalog Raabiha pada avatar mereka.
  - Sistem rekomendasi ukuran dan gaya otomatis berdasarkan preferensi bentuk tubuh pengguna.
