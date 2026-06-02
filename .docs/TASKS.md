# Project Task Tracker (Kanban)

File ini digunakan oleh AI dan Developer untuk melacak progres pengerjaan agar tidak ada iterasi yang berulang.

## 📌 Prioritas Saat Ini (Sprint 1: Migrasi ke Laravel & Filament)
- [x] **Setup Proyek Baru:** Inisialisasi proyek Laravel, konfigurasi database, dan instalasi Filament.
- [x] **Desain Skema Database:** Membuat *migration* dan *model* untuk tabel inti e-commerce (Products, Variants, Customers, Orders, Order Items).
- [x] **Slicing Figma (Public Pages):** Menerjemahkan desain Figma ke *views* Laravel Blade + Tailwind CSS. *(Progres: Home, About, Contact, Product Detail selesai).*
- [x] **Setup Filament Admin:** Mengatur Filament *resources* untuk manajemen entitas (ProductResource, OrderResource) dan implementasi peran (RBAC) via Spatie.

## 📌 Sprint 2: Backend Logic & Frontend Integration

#### Phase 1: Filament Admin Enhancements (Done)
- [x] Kustomisasi form (Select Category, RichEditor, Tipe Data).
- [x] Custom Theme (Aplikasi `raabiha-card` dan tombol Emerald/Charcoal).
- [x] Setting *APP_LOCALE* dan *Label Resource* menjadi Bahasa Indonesia.
- [x] Dummy Data Seeding (via `DummyDataSeeder`).

#### Phase 2: Frontend Data Integration (In Progress)
- [x] Konversi `shop.blade.php` ke Livewire (`App\Livewire\Shop`).
- [x] Binding Search, Filter Kategori, & Sort secara real-time.
- [ ] Integrasi Halaman Single Product (`product.blade.php`).
- [ ] Implementasi varian (Ukuran/Warna) ke UI.
- [ ] Sistem Keranjang & Checkout: Mengubah form statis menjadi dinamis (Livewire), menghitung total harga, dan menyimpan order ke database.

## ✅ Selesai (Done)
- [x] Slicing halaman Dashboard Customer & Promo / Voucher.
- [x] Menyepakati perombakan arsitektur dari WP/Vue ke Laravel/Filament/Blade.
- [x] Menyiapkan struktur dokumen proyek (.docs) untuk standar *Production*.
- [x] Refactor UX QTY & CTA Detail Produk: Menyatukan tombol QTY, CTA, dan Wishlist menjadi satu baris horizontal pudar dan memindahkan tombol Share ke breadcrumbs.
- [x] Refaktor & Aktivasi Accordion Detail Produk: Menyederhanakan accordion menjadi deskripsi terpadu (termasuk Shipping & Care) dan tab Ulasan/Review interaktif.
- [x] Clean Up MVC Directory: Hapus semua file statis sisa *slicing* HTML yang berserakan di *root folder*.

## 🧊 Backlog (Akan Dikerjakan Nanti)
- [x] Implementasi Galeri Modest Urban Coat (6 Gambar, arrow navigasi, dan transisi halus).
- [x] Implementasi Related Products Slider (4 kolom desktop, 2 kolom horizontal slider mobile).
- [ ] Logika "Beli Sekarang" Independen: Membuat sesi checkout terisolasi khusus untuk 1 item saat klik "Beli Sekarang", tanpa menghapus item yang sudah ada di keranjang utama (Marketplace-style).
- [ ] Melakukan integrasi sistem ongkir RajaOngkir.
- [ ] Menyiapkan gerbang pembayaran Xendit.
- [ ] Menyiapkan *script backup database* otomatis di VPS CloudPanel.
- [ ] Fase 2: Sistem *Virtual Fitting Room* dengan avatar kustom.
