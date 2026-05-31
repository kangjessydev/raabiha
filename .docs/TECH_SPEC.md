# Architecture & Technical Specification

## 1. Stack Teknologi
- **Core CMS:** WordPress (Digunakan murni untuk ketahanan *database*, pengelolaan *backend*, dan fitur dasar *e-commerce* via WooCommerce).
- **Frontend & UI:** 
  - **Vue.js & Tailwind CSS:** Digunakan untuk merancang antarmuka publik dan Dasbor khusus. Tujuan utamanya adalah memberikan pengalaman navigasi yang ringan, cepat, SEO *friendly*, dan "menghilangkan unsur tampilan WordPress klasik".
  - **Pendekatan:** Vue disisipkan ke dalam arsitektur tema WordPress, menggunakan *REST API* atau *Localize Script* untuk bertukar data tanpa menambah kompleksitas *stack* terpisah (seperti Nuxt/Next.js).

## 2. Integrasi Layanan Eksternal (Third-Party)
- **Payment Gateway:** Xendit (Untuk pemrosesan pembayaran otomatis).
- **Pengiriman (Logistik):** RajaOngkir (Untuk perhitungan ongkos kirim otomatis).
- **Analytics & Marketing:**
  - TikTok Pixel
  - Google Analytics
  - Google Business
- **Penyimpanan Media:** Penyimpanan internal *server* dengan konversi otomatis ke format **WebP**. Layanan eksternal (CDN/Cloud) akan dipertimbangkan di masa depan hanya jika batas kapasitas penyimpanan server terpenuhi.

## 3. Filosofi Pengembangan
- **Keep it Simple & Fast:** Jangan menambah *library* atau *state management* yang berat jika tidak diperlukan. Keranjang (Cart) dapat dikelola dengan komposisi Vue standar dan API bawaan WooCommerce.
- **Setir Terpisah:** Dasbor (`/dashboard`) dibangun khusus sebagai pusat komando operasional toko.
