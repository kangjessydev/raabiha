# DAFTAR FITUR LENGKAP: RAABIHA E-COMMERCE

Dokumen ini berisi daftar fitur dan kapabilitas sistem E-Commerce Raabiha yang telah dikembangkan dan dioptimasi untuk siap pakai (Production-Ready).

---

## 🛍️ 1. FRONTEND (Halaman Toko Publik)
Sistem antar-muka pelanggan dirancang dengan fokus pada kecepatan, konversi (UI/UX), dan kemudahan akses di semua perangkat (khususnya *Mobile*).

*   **Desain "Mobile-First App-Like"**: Tampilan toko yang mulus seperti aplikasi *smartphone* asli (termasuk *sticky bottom bar* dan transisi halus).
*   **Katalog Produk Cerdas**: Mendukung produk tunggal maupun produk dengan banyak varian (seperti Pilihan Ukuran dan Warna yang masing-masing bisa memiliki stok/harga berbeda).
*   **Keranjang Belanja Persisten (Guest Checkout)**: Pelanggan dapat memasukkan barang ke keranjang tanpa harus mendaftar/login terlebih dahulu. Sistem akan mengingat isi keranjang berdasarkan identitas perangkat.
*   **Sistem Voucher & Diskon**: Mendukung penerapan kupon diskon (Potongan Harga Flat, Persentase, atau Gratis Ongkir) yang bisa ditumpuk sesuai syarat dan ketentuan promo.
*   **Kalkulasi Ongkos Kirim Otomatis**: Terintegrasi langsung dengan **API RajaOngkir**, pelanggan dapat melihat tarif ongkos kirim *real-time* dari berbagai kurir (JNE, J&T, SiCepat, dll) lengkap dengan estimasi waktu sampai saat *Checkout*.
*   **Sistem Pembayaran Otomatis (Payment Gateway)**: Terintegrasi penuh dengan **Tripay** (dan *support* Xendit). Pelanggan dapat membayar dengan Virtual Account Bank, E-Wallet (OVO, Dana, ShopeePay), atau QRIS.
*   **Konfirmasi Lunas Otomatis**: Status pesanan akan otomatis berubah dari "Menunggu Pembayaran" menjadi "Lunas" dalam hitungan detik setelah pelanggan mentransfer uang, tanpa campur tangan admin (*Webhook Enabled*).

---

## ⚙️ 2. BACKEND & DASHBOARD ADMIN
Panel manajemen toko dibangun menggunakan teknologi mutakhir (Filament V5) yang sangat *powerful*, cepat, dan mudah digunakan oleh operator toko.

### A. Dashboard & Metrik Real-time
*   **Ringkasan Finansial**: Menampilkan metrik *real-time* untuk Penjualan Hari Ini, Pendapatan Bulanan, Pengeluaran, dan Estimasi Laba Bersih.
*   **Grafik Tren (Trend Chart)**: Visualisasi grafik perbandingan antara Pemasukan dan Pengeluaran toko selama 30 hari terakhir.
*   **Statistik Operasional**: Menampilkan jumlah pesanan baru yang menunggu untuk diproses.

### B. Manajemen Pemesanan (Order Management)
*   **Pelacakan Status Cerdas**: Alur status pesanan yang rapi (Tertunda $\rightarrow$ Dibayar $\rightarrow$ Diproses $\rightarrow$ Dikirim $\rightarrow$ Selesai).
*   **Cetak Resi & Invoice**: Fasilitas untuk mencetak label pengiriman atau struk belanja.
*   **Ekspor/Impor Data**: Admin dapat mengekspor data pesanan ke dalam format Excel/CSV untuk keperluan akuntansi atau laporan bulanan.

### C. Manajemen Inventaris & Stok Barang
*   **Katalog Produk Lanjutan**: Mengatur nama, deskripsi (Rich Text Editor), harga coret (diskon), berat barang (untuk hitung ongkir), dan SEO Meta Tag per produk.
*   **Pembaruan Stok Cepat (Quick Edit)**: Mengubah jumlah stok ribuan varian produk langsung dari dalam satu halaman tabel tanpa perlu membuka produk satu per satu.
*   **Log Pergerakan Barang (Stock Log)**: Sistem pencatatan otomatis. Setiap pengurangan stok (karena pesanan) atau penambahan stok (karena *restock*) akan tercatat riwayatnya (Siapa, Kapan, dan Mengapa).

### D. Buku Kas & Finansial (Cashflow)
*   **Pencatatan Otomatis Pemasukan**: Setiap pesanan pelanggan yang lunas akan otomatis masuk ke tabel Pemasukan dan tidak bisa dimanipulasi (*Immutable*).
*   **Buku Pengeluaran Kas**: Admin dapat mencatat pengeluaran operasional toko (Contoh: Gaji Karyawan, Biaya Iklan Meta, Listrik) yang akan langsung memotong kalkulasi Laba Bersih di Dashboard.
*   **Filter Kategori Keuangan**: Laporan keuangan bisa difilter berdasarkan label spesifik (Pembelian Bahan Baku, Pajak, Ongkos Kirim, dll).

### E. Manajemen Hak Akses Karyawan (RBAC)
*   **Sistem Role & Permission**: Mencegah kebocoran data dengan mengatur izin akses setiap karyawan. (Contoh: Karyawan "Gudang" hanya bisa melihat Stok, "Keuangan" hanya melihat Buku Kas, sedangkan "Super Admin" memegang kendali penuh).

### F. Pengaturan Toko Terpusat (Global Settings)
Admin/Pemilik bisnis dapat mengatur seluruh konfigurasi toko tanpa bantuan *programmer* secara mandiri:
*   **Identitas Toko**: Mengganti Logo, Nama Toko, Teks *Footer*, dan SEO *Website*.
*   **Mode Libur (Holiday Mode)**: Sekali klik untuk memunculkan *banner* toko sedang tutup sementara tanpa mematikan *website*.
*   **Koneksi API & Kurir**: Memasukkan/mengganti API Key (RajaOngkir & Tripay) yang dikunci dengan sistem otorisasi *password*.
*   **Tracking Marketing**: Mengisi kode Meta Pixel, TikTok Pixel, dan Google Analytics langsung dari kolom yang disediakan.

---

*Dokumen ini dibuat otomatis pada: Juni 2026. Seluruh fitur di atas telah diuji (Tested) dan siap beroperasi di Production Server.*
