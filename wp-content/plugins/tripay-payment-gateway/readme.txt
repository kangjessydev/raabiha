=== TriPay Payment Gateway ===
Contributors: trijayadigital, zerosdev, rofiqdev, ridhoibey
Tags: ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, downloadable, downloads, payment, bca, mandiri, bni, bri, otomatis, virtual account, credit card, payment gateway
Requires at least: 4.7
Tested up to: 6.7
Stable tag: 3.3.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

TriPay Payment adalah payment gateway indonesia yang menyediakan beragam metode pembayaran seperti virtual account, convenience store, e-wallet, dll

== Description ==

TriPay Payment adalah payment gateway indonesia yang menyediakan beragam metode pembayaran seperti virtual account, convenience store, e-wallet, dll.

Beberapa channel yang tersedia diantaranya :
* BRI VA
* BNI VA
* BCA VA
* Mandiri VA
* Maybank VA
* Permata VA
* Sahabat Sampoerna VA
* Muamalat VA
* Sinarmas VA
* Other Bank VA
* Indomaret
* Alfamart
* QRIS
* dan terus bertambah.

Silahkan melakukan pendaftaran terlebih dahulu di (https://tripay.co.id) untuk bisa menggunakan plugin ini.

== Installation ==

= Langkah ke-1 =
1. Pastikan Anda telah menginstall plugin WooCommerce karena ini merupakan addon untuk WooCommerce. Versi WooCommerce minimum untuk plugin ini adalah 3.1.0
2. Unggah plugin ini ke folder `/wp-content/plugins/woocommerce-gateway-tripay`, atau install langsung melalui WordPress plugin secara instan.
3. Aktifkan di menu 'Plugins' WordPress Anda.
4. Masuk ke menu WooCommerce -> Settings -> TriPay Global Configuration lalu isi kolom yang tersedia
5. Salin "Callback URL" berupa link contoh: `http://webtokoonline.com/wc-api/wc_gateway_tripay` lalu masukkan ke menu Merchant di akun TriPay Payment Anda
6. Masuk ke menu WooCommerce -> Settings -> Payments lalu pada channel pembayaran TriPay yang tersedia klik Manage

Dan silahkan mulai berjualan.

= Tutorial =
Selengkapnya silahkan kunjungi tutorial integrasi TriPay Payment dengan WooCommerce di sini:
[https://tripay.co.id/docs/1/cara-install-setting-plugin-untuk-woocommerce-wordpress]

== Frequently Asked Questions ==

= Bagaimana cara install dan integrasi dengan toko online saya? =

Selengkapnya silahkan kunjungi tutorial integrasi TriPay Payment dengan WooCommerce di sini:
[https://tripay.co.id/docs/1/cara-install-setting-plugin-untuk-woocommerce-wordpress]

= Apakah ada biaya layanan? =
Ya, untuk menggunakan layanan kami akan dikenakan biaya yang bervariasi tergantung jenis channel yang digunakan

== Changelog ==

= 1.0.0 =
[NEW] Inisialisasi rilis

= 1.1.0 =
[ADD] Kirim email invoice ke pelanggan setelah checkout
[ADD] Aktifkan/nonaktifkan ikon metode pembayaran
[ADD] Setting redirect page setelah checkout
[FIX] Bug callback ketika status order bukan Pending/Menunggu Pembayaran

= 1.2.0 =
[ADD] Channel pembayaran QRIS
[ADD] Setting status awal pesanan
[ADD] Verifikasi data callback
[ADD] Informasi biaya transaksi ketika checkout
[FIX] Bug dan performa

= 1.3.0 =
[ADD] Channel pembayaran Alfamidi
[ADD] Setting DIRECT / REDIRECT checkout
[FIX] Bug dan performa

= 1.3.1 =
[FIX] Update kompatibilitas dengan versi baru Wordpress & Woocommerce
[FIX] Update kompatibilitas dengan versi baru PHP
[FIX] Tingkatkan logging error

= 1.3.2 =
[FIX] PHP static method warning
[FIX] Update kompatibilitas Woocommerce 4.5.2

= 1.3.3 =
[FIX] Bug dan performa

= 1.3.4 =
[FIX] Bug deprecated WooCommerce function

= 1.3.5 =
[ADD] Channel pembayaran BCA VA
[FIX] Bug dan performa

= 1.3.6 =
[ADD] Channel pembayaran Muamalat VA, Sinarmas VA, Kartu Kredit
[FIX] Deteksi callback URL

= 1.3.7 =
[ADD] Channel pembayaran CIMB Niaga VA
[FIX] Perbaikan sistem callback

= 2.0.0 =
[ADD] Atur url ikon pembayaran
[ADD] Konversi mata uang asing ke IDR
[ADD] Aktifkan/Nonaktifkan email invoice ke pelanggan
[FIX] Perbaikan sistem checkout
[FIX] Penyesuaian sistem fee baru
[FIX] Perbaikan bug kode bayar kosong di email

= 2.0.1 =
[FIX] Bug status pesanan setelah pembayaran sukses

= 2.1.0 =
[FIX] Bug nominal transaksi ketika menggunakan fitur pajak WooCommerce
[FIX] Bug Kode Bayar/Nomor VA tidak muncul di Email Invoice
[ADD] Support mode DIRECT untuk QRIS
[ADD] Channel QRIS Customizable

= 2.1.1 =
[FIX] Bug Kode Bayar/Nomor VA tidak muncul di mode DIRECT

= 3.0.0 =
[FIX] Bug dan performa

= 3.0.1 =
[FIX] Bug zona waktu

= 3.0.2 =
[ADD] Channel pembayaran Sahabat Sampoerna VA

= 3.0.3 =
[FIX] Ikon channel Indomaret

= 3.1.0 =
[ADD] Instruksi pembayaran
[ADD] Channel OVO

= 3.2.0 =
[ADD] Channel QRIS by DANA

= 3.2.1 =
[FIX] Custom ikon

= 3.2.2 =
[ADD] Channel BSI Virtual Account, Danamon Virtual Account, OCBC NISP Virtual Account

= 3.2.3 =
[FIX] Channel pembayaran baru tidak muncul

= 3.2.4 =
[FIX] Proses callback ketika status order Failed

= 3.2.5 =
[ADD] Channel ShopeePay & DANA

= 3.2.6 =
[ADD] New QRIS
[REMOVE] QRIS by DANA

= 3.2.7 =
Fix: Undefined index tripay_exchange_rate_from, tripay_exchange_rate_value

= 3.2.8 =
Fix: Prevent XSS

= 3.2.9 =
[ADD] Channel Other Bank Virtual Account

= 3.3.0 =
[REMOVE] Channel Credit Card
[FIX] Support Woocommerce Blocks

= 3.3.1 =
[FIX] Fees Not Showing On Checkout Pages

= 3.3.2 =
[FIX] PHP >= 8.2.0 dynamic properties deprecation

= 3.3.3 =
[FIX] Some of customer data not showing
