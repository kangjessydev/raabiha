# Integrasi API Pihak Ketiga (API Integration)

File ini akan digunakan sebagai pusat dokumentasi untuk mengelola interaksi sistem dengan layanan eksternal.

## 1. RajaOngkir
*(Kerangka Dokumentasi)*
- **Tujuan:** Menghitung ongkos kirim otomatis berdasarkan alamat *Customer*.
- **Endpoint/Webhook:** (Belum diintegrasikan)
- **Logika Sistem:**
  - Cache hasil pencarian biaya kirim untuk wilayah yang sering dicari.

## 2. Tripay / Xendit Payment Gateway
*(Sudah Diintegrasikan dengan Tripay)*
- **Tujuan:** Membuat Virtual Account, QRIS, atau metode pembayaran otomatis.
- **Endpoint/Webhook:** `POST /webhook/tripay`
- **Logika Sistem:**
  - Saat pembeli mengklik "Bayar Sekarang", sistem membuat *order* (Status: PENDING) dan memanggil API Tripay (`/transaction/create`).
  - Pembeli diarahkan ke *Payment Page* Tripay (`checkout_url`).
  - Webhook Tripay akan dipanggil oleh server Tripay jika status pembayaran berubah. Skrip webhook akan memvalidasi *signature* HMAC SHA256 menggunakan `TRIPAY_PRIVATE_KEY` dan otomatis memperbarui `payment_status` dan `status` pesanan menjadi PAID atau CANCELLED.
