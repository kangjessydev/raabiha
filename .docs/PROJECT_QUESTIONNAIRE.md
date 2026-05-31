# 📝 Kuisioner Proyek: Persiapan Dokumen Production

Harap jawab pertanyaan-pertanyaan di bawah ini. Anda bisa menjawabnya langsung di dalam percakapan kita, atau dengan mengisi file ini. Jika ada jawaban yang kurang lengkap atau belum Anda pikirkan, biarkan saja kosong. Nanti saya akan memandu dan menanyakannya kembali kepada Anda.

---

## 1. Product Requirements (Untuk PRD.md)

_Fokus pada tujuan bisnis dan fitur._

- **Tujuan Utama:** Masalah utama apa yang ingin dipecahkan oleh _e-commerce_ Raabiha ini? Apa bedanya dengan toko _online_ biasa? Jawab: Raabiha ini memiliki masalah biaya admin membengkak di marketplace gara2 kebijakan baru di marketplace seperti tiktokshop dan shopee. Maka website ini adalah solusi untuk mengurangi biaya tersebut sekaligus meningkatkan penjualan. Bedanya dengan toko online biasa, Raabiha memiliki sisten reseller dan promo2 yg tidak ada di marketplace
- **Target Pengguna:** Siapa target pasar utama Anda? (Misalnya: wanita usia 20-35 tahun, reseller, dll). Jawab: wanita usia 18-30 tahun, kalangan apapun, muslim
- **Fitur Wajib (MVP):** Apa saja fitur utama yang **harus ada** sebelum web ini dirilis ke publik? (Misal: Checkout, Hitung Ongkir otomatis, Payment Gateway, Dashboard Pelanggan). Jawab: patokan fitur ecommerce adalah 100% sama dengan woocommerce tapi dengan frontend dashboard yg disesuaikan. Ada fitru cek ongkir gratis dan juga payment gateway yg lengkap, serta dashboard customer yg lengkap.
- **Fitur Masa Depan:** Fitur apa yang ingin Anda tambahkan di masa depan (fase ke-2)? Jawab: fitur simulasi ganti baju. Konsepnya mirip seperti mendandani karakter pada game. User bisa selfie dirinya, wajahnya akan masuk ke karakter. User bisa mengisi tinggi badang, berat badan, lingkar dada, lingkar perut, lingkar pinggan, dll. User bisa memilih baju yg digunakna pad aavatar yg sudah dikustomisasi sesuai preferensi user, dengan pakaina yg merupakan produk Raabiha. User juga bisa meminta rekomendai otomatis sesuai perefensi dirinya.

## 2. Architecture & Tech Spec (Untuk TECH_SPEC.md)

_Fokus pada pondasi teknis agar AI tidak membuat kode yang berantakan._

- **Struktur Frontend:** Saat ini kita menggunakan Vue.js dan Tailwind CSS yang disisipkan ke dalam tema WordPress. Apakah _state management_ (seperti Pinia/Vuex) diperlukan untuk keranjang belanja (cart)? Jawab: Untuk stack tidak perlu ribet, yg penting ringan, cepat, dan mudah diatur SEOnya. Wordpress digunakan karena fitur yg siap pakai, tapi Vue dan Tialwind digunakan utnuk menghilangkan "unsur wordpress" nya, punya dashboard khusus sebagai "setir", bukan jadi core baru sendiri.
- **Integrasi Pihak Ketiga:** Layanan eksternal apa saja yang akan diintegrasikan?
  - _Payment Gateway:_ (Misal: Midtrans, Tripay, Xendit, Stripe?) Jawab: Xendit
  - _Pengiriman:_ (Misal: RajaOngkir, Shipper, integrasi kurir spesifik?) jawab: RajaOngkir
  - _Analitik/Marketing:_ (Misal: Meta Pixel, TikTok Pixel, Google Analytics?) Jawab: Tiktok Pixel, Google Analytics, Google Business
- **Pengelolaan Media:** Apakah sistem WebP yang baru saja kita perbaiki sudah mencukupi, atau Anda butuh integrasi ke penyimpanan eksternal (seperti AWS S3 atau Cloudflare R2)? Jawab: untuk sekarang sudah mencukupi, kecuali ada masalah kedepannya bisa menggunakan layanan pihak ketiga yg gratis

## 3. Keamanan & Akses (Untuk SECURITY_RULES.md)

_Fokus pada perlindungan data pelanggan dan web._

- **Hak Akses (Roles):** Selain Administrator dan Customer standar, apakah ada tipe pengguna khusus? (Misalnya peran "Reseller" yang mendapat potongan harga khusus). Jawab: ada Co-Administrator, ada Store Manager, ada Finance, ada Admin Kasir, dan ada Stock Manager
- **Pendaftaran:** Apakah pengguna bisa bebas mendaftar (Open Registration) atau harus lewat persetujuan? Jawab: bebas mendaftar, bisa menggunakan email atau bisa menggunakan login google secara langsung

## 4. Deployment & Build (Untuk DEPLOYMENT.md)

_Fokus pada proses rilis ke server._

- **Lingkungan Server:** Di mana web ini akan di-_hosting_ saat rilis? (Misal: VPS DigitalOcean, Niagahoster cPanel, dll). Jawab: di VPS Lite dengan core 2, ram 2 gb, dan ssd 20gb di domainesia.
- **Proses Update:** Bagaimana cara Anda biasanya melakukan pembaruan web? (Upload ZIP via cPanel, Git Pull via Terminal, atau CI/CD otomatis seperti GitHub Actions?). Jawab: belum tau, tapi saya sudah install cloudpanel pada ubuntu di VPS
- **Strategi Backup:** Apakah sudah ada sistem _backup database_ dan file? Jawba: belum ada, tidak mengerti. Tpai maunya otomatis dan bisa disesuaikan waktunya

## 5. Pelacakan Tugas (Untuk TASKS.md)

_Fokus pada prioritas kerja saat ini._

- **Prioritas Utama:** Dari semua hal yang harus dikerjakan, fitur apa yang menjadi fokus nomor 1 kita minggu ini? (Misal: Menyelesaikan halaman beranda, atau menyelesaikan sistem Checkout). Jawba: Menyelesaikan desain seluruh halaman publik (slicing dari figma) dan dashboard admin /dashboard
