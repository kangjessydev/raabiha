# Project Task Tracker (Kanban)

File ini digunakan oleh AI dan Developer untuk melacak progres pengerjaan agar tidak ada iterasi yang berulang dan memastikan sinkronisasi antara Admin Panel dan Frontend Customer.

## 📌 Status Saat Ini: Sprint 2 (Integrasi Fungsional)

### 🏗️ Admin Panel (Filament)
**✅ Selesai (Done):**
- Setup RBAC & Spatie Permission.
- Kustomisasi Form (Select Category, RichEditor, Tipe Data).
- Kustomisasi Tema Dashboard (Aplikasi `raabiha-card`, Font Poppins, Top Navigation).
- Manajemen Bahasa (APP_LOCALE Indonesia).
- **Penataan Cluster E-Commerce:** Pengurutan menu (Transaksi, Katalog, Promosi, Reseller, Pengaturan Toko) menggunakan *native* `$navigationSort` dan `BackedEnum`.
- **Integrasi Media Library:** Penggunaan `awcodes/filament-curator` dengan *override* kustom untuk bernaung di bawah *Cluster* Media Files.

**🚧 Sedang Dikerjakan / Belum (In Progress / To Do):**
- [ ] **Logika Export/Import Media:** Menambahkan fungsi *logic* pada halaman statis `ExportMedia` dan `ImportMedia`.
- [ ] **Badge Guest Checkout:** Menambahkan filter dan label visual pada `OrderResource` untuk membedakan pesanan Guest vs Registered User (sesuai `DATABASE_SCHEMA.md`).
- [ ] **Pengaturan Shield & Setting Cluster:** Memastikan konfigurasi Filament Shield bernaung di bawah `SettingsCluster` (sesuai `TECH_SPEC.md`).

### 🛒 Frontend Customer (Blade + Livewire)
**✅ Selesai (Done):**
- Slicing Figma (Home, About, Contact, Dasbor Customer, Product Detail).
- Konversi `shop.blade.php` ke Livewire (`App\Livewire\Shop`) dengan pencarian & filter *real-time*.
- Halaman Produk Dinamis (`product-detail.blade.php`), termasuk varian ukuran/warna dan rendering HTML untuk deskripsi.
- Animasi Galeri: Transisi geser (*slide*) gambar galeri utama dan efek transparan Emerald pada *thumbnail* aktif.
- Refaktor UI: Menyatukan tombol QTY, CTA Cart, dan *Wishlist*, serta membersihkan *MVC Directory*.

**🚧 Sedang Dikerjakan / Belum (In Progress / To Do):**
- [ ] **Sistem Keranjang Dinamis:** Mengkonversi operasi Cart ke Livewire (menambah produk, update QTY, hitung total harga sementara) menggunakan Database Storage (Opsi A pada schema).
- [ ] **Logika "Beli Sekarang":** Checkout independen untuk 1 barang tanpa mengganggu isi *cart* utama.
- [ ] **Sistem Checkout (Frontend):** Form pengisian alamat dan pemilihan metode pengiriman.

---

## 🔌 Integrasi API (Fase Berikutnya)
- [ ] **Logistik (RajaOngkir):** Cek ongkir otomatis dan gratis ongkir berdasarkan alamat.
- [ ] **Pembayaran (Xendit):** Virtual Account, QRIS otomatis dengan Webhook integrasi ke status pesanan.

## 🧊 Backlog / Opsional (Sprint 3 / Fase 2)
- [ ] *Virtual Fitting Room* dengan avatar kustom.
- [ ] *Script* Backup Database otomatis di VPS CloudPanel.
