# Log Uji Coba Fitur Raabiha E-Commerce

Dokumen ini mencatat fitur-fitur yang sudah selesai dilakukan pengujian secara menyeluruh (End-to-End Test) dan berstatus **OKE / LULUS UJI**.

## 🚀 Fitur yang Sudah Selesai Diuji (LULUS)

- [x] **Ekspor Data (Semua Modul)**
  - Format CSV sudah bersih dari tag HTML.
  - Label kolom (header) sudah tertata rapi dan berbahasa Indonesia.
- [x] **Impor Data (Semua Modul: Produk, Pesanan, Kategori, Artikel, Voucher, Pengguna)**
  - Terdapat fitur pembaruan pintar (*Auto-Update* berdasarkan Slug/ID/Kode) sehingga tidak terjadi duplikasi.
  - Validasi ketat (seperti kolom boolean wajib) sudah dilonggarkan agar anti-gagal.
  - Nama-nama kolom Impor tersinkronisasi 100% dengan hasil Ekspor (bisa langsung pakai file Export sebagai template).
- [x] **Pengumuman Topbar**
  - Bug validasi karakter (yang dipicu ekstensi browser seperti Grammarly) sudah dihilangkan. 
  - UI sudah responsif dan secara otomatis menggunakan efek *marquee* (berjalan) jika teks melebihi 100 karakter.
- [x] **Banner Promosi**
  - UI tabel lebih bersih dan praktis (penghapusan kolom urutan yang tidak perlu).
  - Terdapat tombol *Toggle* langsung untuk mematikan/menyalakan banner dari luar.
  - Semua label sudah berbahasa Indonesia (*Full Localization*).
- [x] **Voucher Diskon**
  - Impor dan Ekspor tersinkronisasi secara sempurna.
  - Sinkronisasi UI dan validasi kolom bebas *error* saat upload massal.
- [x] **Manajemen Pesanan (Transaksi)**
  - Pemrosesan pesanan *online* (via web) dan *offline* (manual/POS) berjalan normal dan lancar.
- [x] **Refund Pelanggan**
  - Alur pengajuan dan pemrosesan dana kembalian (refund) berfungsi dengan baik.
- [x] **Buku Kas (Cashflow)**
  - Pencatatan arus kas dan statistik laporan transaksi bekerja secara normal.
- [x] **Halaman Statis (CMS)**
  - Fitur tipografi Tailwind CSS berhasil diterapkan sehingga list, bold, heading, dll dari *Rich Editor* di admin panel ter-render dengan sempurna di *frontend*.
  - Toolbar *Rich Editor* di-set menjadi *sticky* di bagian atas dengan latar warna solid agar tidak transparan/menimpa tulisan saat mengedit konten panjang.
- [x] **Sales Page Builder**
  - Berhasil mengubah struktur *Sales Page Builder* dari model kaku (hardcoded sections) menjadi sistem *Nested Builder* (Section > Widgets) yang lebih modular dan dinamis seperti Elementor.
  - Implementasi warna latar belakang (*bg-color*) dan warna teks secara dinamis di *frontend* berjalan lancar.
  - Opsi kustomisasi tipografi (jenis font, ukuran, warna, *alignment*) pada setiap *widget* tereksekusi dengan baik di *frontend*.
  - Layout halaman khusus Sales Page otomatis dalam mode "Blank" (tanpa Navigasi, Footer, dan Bottom bar).
- [x] **CMS Halaman Utama**
  - Modul pengaturan tampilan halaman utama dari panel admin sudah bekerja dan terhubung secara *real-time* ke *frontend*.
- [x] **Manajemen Hak Akses & Peran (Role & Permission)**
  - Pembatasan hak akses untuk peran Owner (Pemilik) telah diuji coba sepenuhnya dan berjalan 100% aman.
  - Menu sensitif (Media Files, Topbar Announcement, Banner Promosi, dan Voucher) berhasil disembunyikan total dari navigasi Owner.
  - Halaman-halaman lainnya (Produk, Pesanan, Kategori, Atribut, Manajemen Stok, Pelanggan, Reseller, dan seluruh modul Konten/CMS) dibatasi hanya bisa melihat (Read-Only) dengan menonaktifkan form, tombol simpan, tombol tambah/edit/hapus, serta toggle status.
  - Izin melihat Analitik Pengunjung (Google Analytics) berhasil dibuka untuk peran Owner dan Marketing.
  - Pembatasan hak akses untuk peran Kasir (Tim Kasir) telah sukses diuji: Kasir dapat membuat pesanan baru (POS manual/offline), namun saat mengedit pesanan lama secara mandiri, semua kolom penting dikunci. Untuk melakukan perubahan, Kasir menggunakan tombol **Ajukan Perubahan** yang mengarahkannya ke halaman edit pesanan dengan kolom terbuka, lalu tombol simpan berubah menjadi **Ajukan Perubahan** untuk menyimpan rancangan proposal. Setelah disetujui Owner via menu **Permintaan Kasir**, perubahan tersebut otomatis diterapkan langsung ke pesanan. Tombol persetujuan Reseller (Setujui/Tolak) serta tombol pembuatan reseller baru (Buat Reseller) berhasil disembunyikan secara khusus dari pandangan Kasir. Kasir tidak bisa mengubah status menjadi "Dibatalkan" langsung lewat dropdown edit biasa (pilihan disembunyikan), melainkan wajib melalui alur **Ajukan Pembatalan**.
  - **Uji Coba Peran Finance:** Berhasil memverifikasi bahwa menu **Refund Pelanggan** kini muncul untuk Tim Finance. Saat status Refund diubah menjadi **Selesai Ditransfer (completed)**, sistem secara otomatis memperbarui status pembayaran pesanan terkait menjadi **Dikembalikan (refunded)**.
  - **Uji Coba Peran Marketing:** Berhasil menyembunyikan menu **Manajemen Stok** secara penuh dari Tim Marketing. Tim Marketing hanya memiliki akses untuk mengelola katalog visual/promosi produk tanpa hak akses stok fisik maupun hak hapus produk/kategori.
  - **Uji Coba Peran Gudang/Logistik:** Berhasil menyembunyikan menu utama **Produk** secara total. Tim Gudang langsung dialihkan ke menu **Manajemen Stok** untuk melakukan pemutakhiran stok dan melihat log mutasi stok tanpa bisa mengubah konten katalog produk lainnya.
  - **Otomatisasi Alur Refund & Pembatalan:** Pengujian alur dari Kasir mengajukan pembatalan -> disetujui Owner -> sistem otomatis membuat draf pengajuan refund senilai grand total -> diproses Finance hingga status pembayaran pesanan menjadi `refunded` telah berjalan 100% lancar dan sinkron.

---
*Dokumen ini akan terus diperbarui seiring dengan berjalannya proses pengujian modul-modul lainnya.*
