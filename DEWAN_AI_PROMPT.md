# BRIEFING & PROMPT DEWAN AI (AI COUNCIL)
**Proyek:** Website E-Commerce raabiha.com (Laravel 11, Livewire 3, Filament 3)  
**Topik Utama:** Resolusi Masalah Kebobolan Ongkir, Auto-Fallback Logistik, Integritas Stok & Arsitektur Transaksi  
**Tanggal:** 6 Oktober 2026

---

## 📌 PANDUAN PENGGUNAAN UNTUK PENGGUNA
1. Dokumen ini dirancang untuk mendiskusikan rencana perbaikan sistem bersama **Dewan AI**.
2. **Cara Pakai:**
   - Buka model AI yang bersangkutan (Claude, ChatGPT, DeepSeek, Gemini, Kimi).
   - Salin **[BAGIAN UMUM: KONTEKS SISTEM & TEMUAN MASALAH]** sekali di awal atau sertakan bersama prompt khusus.
   - Salin **PROMPT KHUSUS** untuk model tersebut yang sudah disesuaikan dengan peran uniknya.
   - Salin jawaban mereka kembali ke obrolan dengan **Antigravity**.
   - Antigravity akan membaca, mendebat masukan yang kurang realistis atau memiliki celah baru, dan merangkum keputusan final sebelum melakukan implementasi kode.

---

## 🏛️ STRUKTUR & PERAN DEWAN AI

| Model AI | Peran / Jabatan | Fokus Evaluasi |
| :--- | :--- | :--- |
| **Antigravity (IDE)** | **Principal Software Engineer & Implementer** | Eksekusi kode Laravel/Livewire/Filament, menjaga realitas implementasi teknis, dan menguji langsung di sistem. |
| **Claude** | **Chief Software Architect & Code Auditor** | Audit arsitektur, edge cases, integritas data transaksi, race conditions, dan konsistensi status pesanan. |
| **ChatGPT** | **Senior Solutions Architect & E-Commerce Strategist** | Alur bisnis e-commerce, praktik terbaik logistik di Indonesia, skalabilitas toko, dan relasi transaksi-akuntansi. |
| **DeepSeek** | **Security & Defensive Engineering Specialist** | Eksploitasi celah (bug hunter), manipulasi state di client-side, celah bypass validasi, dan proteksi anti-bocor. |
| **Gemini (Browser)** | **System Integration & Third-Party API Specialist** | Dinamika API pihak ketiga (BinderByte, RajaOngkir, Tripay, Xendit), penanganan timeout, failover, dan webhooks. |
| **Kimi** | **UX & Conversion Guardian** | Pengalaman pengguna (UX checkout), meminimalkan cart abandonment, auto-fallback tanpa hambatan input, dan kejelasan copy error. |

---

# [BAGIAN UMUM: KONTEKS SISTEM & TEMUAN MASALAH]
*(Salin bagian ini sebagai konteks dasar saat mengirim prompt ke masing-masing AI)*

```text
KAMI MEMILIKI SISTEM E-COMMERCE BERBASIS LARAVEL 11 + LIVEWIRE 3 + FILAMENT 3.
KEMARIN TERJADI INSIDEN: KLIEN KOMPLAIN KARENA "KEBOBOLAN ONGKIR".
Pembeli di wilayah tertentu berhasil menekan tombol "Bayar Sekarang", transaksi masuk ke database dan payment gateway, tetapi nilai ongkos kirim Rp 0 (gratis/hilang), sehingga toko nombok biaya kirim.

HASIL AUDIT INTERNAL MENEMUKAN 5 AKAR MASALAH:

1. CELAH VALIDASI BACKEND (Livewire Checkout::processCheckout):
- Validasi hanya memeriksa nama, alamat, kelurahan, dan agree_terms.
- TIDAK ADA validasi 'shipping_method' => 'required' atau 'shippingRates tidak boleh kosong'.
- Ketika kurir gagal dimuat, shipping_method = "" dan shipping_cost = 0, tetapi formulir lolos validasi dan order terbuat dengan grand_total tanpa ongkir.

2. CELAH FRONTEND (checkout.blade.php):
- Tombol "Bayar Sekarang" hanya terikat pada checkbox persetujuan (:disabled="!agree").
- Tombol tidak memeriksa apakah tarif pengiriman valid atau masih menghitung (wire:loading).

3. KETERBATASAN API BINDERBYTE & OUT-OF-COVERAGE:
- Beberapa ID kecamatan di BinderByte gagal dihitung (error status 400 Invalid origin/destination prefix IDs).
- Beberapa daerah pelosok/pemekaran tidak didukung kurir online sehingga BinderByte mengembalikan 0 kurir. Sistem menganggap ini array kosong [] tanpa memicu error pemblokir.

4. CELAH TARIF MANUAL SAAT INI:
- Sistem memiliki mode manual (pembeli switch manual), tetapi tarifnya FLAT per transaksi (misal Rp 20.000) dan TIDAK DIKALIKAN DENGAN BERAT PAKET (kg). Jika pembeli belanja 8 kg, ongkir tetap Rp 20.000 dan toko tombok besar.
- Jika admin lupa memasukkan tarif untuk pulau/provinsi tertentu dan tidak ada tarif default nasional, mode manual juga menghasilkan 0 kurir.

5. TEMUAN KRITIS LAIN DI TRANSKASI & INVENTORI:
- Pengembalian Stok Gaib: Saat checkout, stok produk langsung dipotong. Tetapi saat pesanan batal/expired (misal via webhook Tripay/Xendit), OrderObserver HANYA membatalkan cashflow dan mengirim email, STOK TIDAK PERNAH DIKEMBALIKAN (increment) ke sistem, sehingga inventori toko bocor terus.
- Tripay Webhook Controller: Membaca private key langsung dari env('TRIPAY_PRIVATE_KEY'), bukan dari SiteSetting (database), sehingga rawan gagal verifikasi signature jika dijalankan php artisan config:cache.

RENCANA SOLUSI YANG DIUSULKAN (3 FASE):
- Fase 1: Lockdown validasi checkout backend & frontend. Jika API BinderByte gagal, sistem otomatis (Auto-Fallback) mengambil nama Provinsi/Kota yang sudah dipilih pembeli dan mencocokkannya ke tabel aturan manual yang dikalikan berat (kg) + wajib ada aturan Default Nasional. Perbaiki Tripay webhook membaca database.
- Fase 2: Restorasi stok otomatis dan kuota voucher saat pesanan batal/expired di OrderObserver. Perbaiki tombol "Beli Sekarang" agar tidak mencampur keranjang lama.
- Fase 3: Hapus file model ganda (SalesPage vs Salespage), bersihkan tabel usang (coupons), dan sentralisasi logika voucher ke VoucherService.
```

---

# PROMPT 1: UNTUK CLAUDE (Chief Software Architect & Code Auditor)

```text
[Konteks Sistem: Tempelkan BAGIAN UMUM di atas terlebih dahulu]

Hai Claude, peran Anda di Dewan AI ini adalah sebagai CHIEF SOFTWARE ARCHITECT & CODE AUDITOR.
Tugas utama Anda adalah meneliti arsitektur, konsistensi data transaksi, race conditions, dan edge cases dari rencana di atas.

Jangan menjadi "yes-man". Tolong berikan kritik tajam dan audit mendalam terhadap aspek-aspek berikut:

1. ARSITEKTUR AUTO-FALLBACK DARI API KE MANUAL:
- Ketika API BinderByte mengembalikan 0 tarif atau error, sistem Livewire akan otomatis mencocokkan nama provinsi yang tersimpan di memori ke tabel manual_shipping_rules.
- Apakah ada potensi inkonsistensi data transaksi jika pembeli mengganti-ganti alamat secara cepat? Bagaimana penanganan race condition antar Lifecycle Hooks di Livewire 3?

2. ATOMICITY & TRANSACTION INTEGRITY PADA CHECKOUT:
- Saat ini sistem menggunakan DB::beginTransaction() dan Cache::lock('checkout_lock_' . $userId, 30).
- Apakah lock 30 detik ini aman jika ada panggilan ke payment gateway eksternal di dalam blok transaksi? Bukankah melakukan panggilan HTTP eksternal di dalam DB Transaction adalah anti-pattern yang berbahaya bagi koneksi database pool? Apa arsitektur rekomendasi Anda?

3. RESTORASI STOK DI OBSERVER:
- Jika order dibatalkan (misal via webhook berulang atau admin mengklik cancel dua kali), bagaimana mencegah double-restoration (stok dikembalikan dua kali)?
- Apakah lebih baik menggunakan field status/flag khusus (misal: is_stock_restored) atau mekanisme idempotency key?

Berikan rekomendasi arsitektur terbaik dan tunjukkan blind spot yang mungkin belum dipikirkan oleh tim developer!
```

---

# PROMPT 2: UNTUK CHATGPT (Senior Solutions Architect & E-Commerce Strategist)

```text
[Konteks Sistem: Tempelkan BAGIAN UMUM di atas terlebih dahulu]

Hai ChatGPT, peran Anda di Dewan AI ini adalah sebagai SENIOR SOLUTIONS ARCHITECT & E-COMMERCE STRATEGIST.
Tugas Anda adalah menilai alur bisnis, pengalaman operasional admin gudang, integrasi ekspedisi logistik Indonesia, dan keandalan pembukuan kas.

Jangan menjadi "yes-man". Mohon evaluasi secara kritis:

1. STRATEGI TARIF MANUAL (FALLBACK EKSPEDISI DI INDONESIA):
- Dalam bisnis e-commerce Indonesia, ketika API logistik mati/gagal mencakup wilayah pelosok, apakah formula:
  Ongkir = Tarif Flat Wilayah x Ceil(Berat / 1000) + Biaya Tambahan
  sudah mencerminkan praktik terbaik?
- Bagaimana menangani perbedaan kurir di invoice? Pembeli memilih "Reguler (Tarif Toko)", tetapi admin di gudang harus memilih ekspedisi nyata (JNE, J&T, SiCepat, dll.). Bagaimana format pelabelan dan komunikasi ke pembeli agar tidak menimbulkan sengketa?

2. RULES HIERARCHY UNTUK TARIF MANUAL:
- Kami memiliki hierarki: Spesifik Provinsi > Satu Pulau > Default Nasional.
- Apa rekomendasi besaran tarif default nasional yang aman untuk toko retail fashion/gamis agar tidak boncos jika ada kiriman ke pelosok pulau terluar, namun tidak terlalu mahal hingga membatalkan niat beli?

3. DAMPAK PADA CASHFLOW & PEMBUKUAN TOKO:
- Ketika ongkir masuk ke rekening toko via Tripay (Payment Gateway), dana tersebut tercatat di Cashflow masuk sebagai grand_total.
- Namun saat admin membayar kurir di gerai, uang keluar belum tercatat otomatis jika tidak ada modul pengeluaran ongkir. Bagaimana rekomendasi alur data antara ongkir yang dibayar konsumen vs biaya riil yang dibayar toko ke ekspedisi?

Berikan saran strategis terbaik Anda dari kacamata operasional e-commerce skala menengah-besar!
```

---

# PROMPT 3: UNTUK DEEPSEEK (Security & Defensive Engineering Specialist)

```text
[Konteks Sistem: Tempelkan BAGIAN UMUM di atas terlebih dahulu]

Hai DeepSeek, peran Anda di Dewan AI ini adalah sebagai SECURITY & DEFENSIVE ENGINEERING SPECIALIST.
Tugas Anda adalah berpikir seperti Bug Hunter / Attacker yang mencari celah untuk membobol ongkir, memanipulasi diskon, atau mengacaukan inventori toko.

Jangan menjadi "yes-man". Bongkar celah keamanan dari rancangan ini:

1. EXPLOIT SCENARIOS PADA LIVEWIRE CHECKOUT:
- Livewire mengekspos public properties ke client. Bagaimana cara seorang penyerang memanipulasi properti $shipping_cost, $shipping_method, atau $addressMode melalui manipulasi request payload Livewire (DevTools/Burp Suite)?
- Bagaimana rancangan validasi defensif di backend agar manipulasi nilai ongkir dari sisi client 100% mustahil tembus ke database?

2. VOUCHER & DISCOUNT MANIPULATION:
- Saat ini ada voucher potongan ongkir dan voucher belanja. Bagaimana mencegah penyerang menggabungkan (stacking) voucher ongkir pada metode pengiriman manual atau memicu minus total (grand_total < 0)?

3. WEBHOOK VULNERABILITIES (TRIPAY & XENDIT):
- Selain masalah config:cache pada private key, apa saja potensi celah pada Tripay/Xendit Webhook Controller kami? (Misal: replay attack, timing attack pada hash_hmac, atau manipulasi callback IP).

Berikan rekomendasi pengamanan defensif (defensive coding) paling ketat untuk mengunci sistem ini dari segala sisi!
```

---

# PROMPT 4: UNTUK GEMINI (System Integration & Third-Party API Specialist)

```text
[Konteks Sistem: Tempelkan BAGIAN UMUM di atas terlebih dahulu]

Hai Gemini, peran Anda di Dewan AI ini adalah sebagai SYSTEM INTEGRATION & THIRD-PARTY API SPECIALIST.
Tugas Anda adalah meneliti perilaku API pihak ketiga (BinderByte Logistics, Komerce/RajaOngkir, Tripay, Xendit), penanganan kuota, rate limits, dan strategi failover yang tangguh.

Jangan menjadi "yes-man". Analisis secara mendalam hal-hal berikut:

1. BINDERBYTE LOGISTICS API ECCENTRICITIES:
- Mengapa BinderByte mengembalikan error 400 'Invalid origin or destination prefix IDs' pada kombinasi kecamatan tertentu (misal: origin dist_32.04.14 ke dist_32.04.42)?
- Apakah BinderByte membutuhkan format ID tanpa titik (320414) atau ada perbedaan antara kode BPS dan Kemendagri pada endpoint /wilayah vs /v1/cost?
- Bagaimana format payload yang paling stabil untuk BinderByte cost API?

2. STRATEGI FAILOVER & CIRCUIT BREAKER:
- Jika API BinderByte lambat (> 5 detik) atau server mereka down, checkout pelanggan tidak boleh hang.
- Bagaimana merancang timeout, caching rate yang efisien, dan circuit breaker otomatis yang langsung mengalihkan request ke tarif internal tanpa membuat pelanggan menunggu lama?

3. INKONSISTENSI WEBHOOK PAYMENT GATEWAY:
- Analisis perbedaan payload dan penanganan status antara Tripay dan Xendit. Apa mitigasi terbaik jika webhook dari payment gateway terlambat masuk atau terpanggil dua kali (idempotency webhook)?

Berikan wawasan teknis mendalam mengenai integrasi API logistik dan payment gateway di Indonesia!
```

---

# PROMPT 5: UNTUK KIMI (UX & Conversion Guardian)

```text
[Konteks Sistem: Tempelkan BAGIAN UMUM di atas terlebih dahulu]

Hai Kimi, peran Anda di Dewan AI ini adalah sebagai UX & CONVERSION GUARDIAN.
Tugas Anda adalah memastikan semua perbaikan teknis keamanan di atas TIDAK MERUSAK kenyamanan belanja pelanggan dan TIDAK MENYEBABKAN cart abandonment (pembeli kabur karena form rumit atau error membingungkan).

Jangan menjadi "yes-man". Berikan pandangan kritis Anda terhadap:

1. PENGALAMAN PENGGUNA SAAT AUTO-FALLBACK AKTIF:
- Jika sistem tiba-tiba mendeteksi bahwa kurir online tidak dapat menjangkau kecamatan pembeli, bagaimana cara menampilkan opsi tarif manual kepada pembeli agar mereka tidak panik atau bingung?
- Kata-kata (microcopy) apa yang paling ramah dan meyakinkan untuk menjelaskan bahwa pesanan mereka dialihkan ke tarif toko?

2. FEEDBACK TOMBOL BAYAR SAAT KALKULASI BERLANGSUNG:
- Tombol bayar akan dinonaktifkan saat kalkulasi ongkir berlangsung. Bagaimana visual feedback (loading spinner, teks tombol, placeholder ongkir) yang paling nyaman agar pengguna tahu sistem sedang bekerja dan tidak mengira website macet?

3. GUEST CHECKOUT VS REGISTERED ADDRESS:
- Pelanggan lama yang memiliki alamat tersimpan sering mengalami error jika format ID wilayahnya tidak cocok dengan sistem baru. Bagaimana alur transisi terbaik bagi user lama agar mereka tidak dipaksa mengisi ulang seluruh formulir secara menjengkelkan?

Berikan saran terbaik Anda untuk menjaga tingkat konversi checkout tetap tinggi sembari sistem diamankan!
```

---

## 🚀 LANGKAH BERIKUTNYA
1. Copas prompt di atas ke model AI masing-masing.
2. Bawa jawaban dari masing-masing AI ke percakapan ini.
3. Antigravity akan menyaring dan memvalidasi setiap usulan, mendebat poin-poin yang bertentangan atau tidak realistis di Laravel/Livewire, dan menyusun sintesis keputusan untuk langsung diimplementasikan ke codebase raabiha-ecommerce.
