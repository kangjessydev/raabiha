# Integrasi API Pihak Ketiga (API Integration)

File ini akan digunakan sebagai pusat dokumentasi untuk mengelola interaksi sistem dengan layanan eksternal.

## 1. RajaOngkir
*(Kerangka Dokumentasi)*
- **Tujuan:** Menghitung ongkos kirim otomatis berdasarkan alamat *Customer*.
- **Endpoint/Webhook:** (Belum diintegrasikan)
- **Logika Sistem:**
  - Cache hasil pencarian biaya kirim untuk wilayah yang sering dicari.

## 2. Xendit Payment Gateway
*(Kerangka Dokumentasi)*
- **Tujuan:** Membuat Virtual Account, QRIS, atau metode pembayaran otomatis.
- **Endpoint/Webhook:** (Belum diintegrasikan)
- **Logika Sistem:**
  - Saat pesanan dibuat di sistem (Status: PENDING), API dipanggil.
  - Webhook Xendit akan memperbarui status secara otomatis jika pembayaran berhasil (Status: PAID).
