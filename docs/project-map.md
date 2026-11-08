# Project Map

_Terakhir diverifikasi: 2026-11-08, commit `32bece31`_  
Jaga singkat. Detail panjang taruh di file lain dan rujuk di sini.

## Struktur Folder
| Path | Fungsi |
|---|---|
| `app/Filament/` | Panel Backoffice Admin (Filament v5): Resources, Pages, Widgets, Export/Import |
| `app/Http/Controllers/` | Controller HTTP, Webhook handler (Tripay, Xendit), Sitemap |
| `app/Http/Middleware/` | Middleware autentikasi khusus POS (`EnsurePosAuthenticated`), tracking pageviews |
| `app/Livewire/` | Komponen interaktif frontend Storefront (Cart, Checkout, Shop) & Terminal POS (`PosManager`) |
| `app/Models/` | Model Eloquent ORM (Order, Product, PosSession, PosReturn, Cashflow, dll.) |
| `app/Observers/` | Observers lifecycle Eloquent (Order, Product, StockLog) |
| `app/Providers/` | Service providers (Filament Panel, Curator storage URL, App bootstrapping) |
| `app/Services/` | Logika bisnis & integrasi eksternal (PosTransaction, OrderStock, EscPos, Voucher, BinderByte) |
| `database/migrations/` | Skema database dan riwayat migrasi |
| `resources/views/` | Template Blade Storefront & Livewire UI components |
| `resources/css/` & `js/` | Aset frontend Tailwind CSS v4 & JavaScript bundler |
| `routes/web.php` | Definisi rute: Domain utama, subdomain POS (`pos.`), Webhook, dan rute publik |
| `tests/Feature/` & `tests/Unit/` | Test suite PHPUnit (109 tes pengujian fungsional) |

## Titik Masuk & Alur Utama
- **Storefront Publik (`raabiha.com`):**
  - Rute: `/`, `/shop`, `/product/{slug}`, `/cart`, `/checkout`
  - Alur: Pengunjung memilih produk → tambah ke keranjang (`Cart.php`) → checkout (`Checkout.php`) → kalkulasi ongkir BinderByte/Emsifa & diskon voucher (`VoucherService`) → pembayaran Tripay/Xendit → callback webhook memicu mutasi stok (`OrderStockService`).
- **Terminal Kasir POS (`pos.raabiha.com`):**
  - Rute: `/login` (`PosLogin.php`) → Dashboard POS (`PosManager.php`)
  - Alur: Kasir buka shift kasir (`PosSession`) → scan barcode / pilih produk `pos_only` & `both` → bayar (tunai/transfer/QRIS) → potong stok & cetak struk ESC/POS Bluetooth/Serial (`EscPosService`) → tutup shift dengan rekonsiliasi kas.
- **Admin Panel Backoffice (`admin.raabiha.com`):**
  - Filament panel root (`path('')` dengan domain `admin.raabiha.com`).
  - Pengelolaan katalog, verifikasi manual pesanan, laporan akuntansi/cashflow, monitoring sesi POS kasir.

## Peta Fitur Utama
| Fitur | UI (File) | API / Logic (File) | DB (Tabel) | Status |
|---|---|---|---|---|
| Katalog & Belanja Online | `resources/views/livewire/shop.blade.php`, `product-detail.blade.php` | `app/Livewire/Shop.php`, `ProductDetail.php` | `products`, `product_variants`, `categories` | Nyata |
| Keranjang & Checkout | `resources/views/livewire/cart.blade.php`, `checkout.blade.php` | `app/Livewire/Cart.php`, `Checkout.php` | `carts`, `cart_items`, `orders`, `order_items` | Nyata |
| Integrasi Voucher & Promo | `resources/views/livewire/checkout.blade.php` | `app/Services/VoucherService.php` | `vouchers`, `applied_voucher_ids` | Nyata |
| Terminal Kasir POS | `resources/views/livewire/pos-manager.blade.php` | `app/Livewire/PosManager.php`, `PosTransactionService.php` | `orders`, `pos_sessions`, `pos_customers` | Nyata |
| Cetak Struk ESC/POS Thermal | Dialog Web Bluetooth/Serial di POS UI | `app/Services/EscPosService.php` | `site_settings` (receipt header/footer) | Nyata |
| Pengembalian/Retur POS | Komponen Retur di POS UI | `app/Livewire/PosManager.php` | `pos_returns`, `pos_return_items` | Nyata |
| Sesi & Shift Kasir | Modal shift di POS UI | `app/Livewire/PosManager.php` | `pos_sessions` | Nyata |
| Pengelolaan Stok & Log | Filament `StockManagementResource.php` | `app/Services/OrderStockService.php`, `StockLogObserver.php` | `products`, `product_variants`, `stock_logs` | Nyata |
| Webhook Pembayaran | - | `TripayWebhookController.php`, `XenditWebhookController.php` | `orders`, `cashflows` | Nyata |
| Halaman Statis & Sales Page | `resources/views/static-page.blade.php`, `sales-page.blade.php` | `routes/web.php:148` (Catch-all) | `static_pages`, `sales_pages` | Nyata |
| Template Dummy Static | `resources/views/dummy-static.blade.php` | - | - | Dummy/Arsip |

## Konvensi (Dengan Contoh Kode Nyata)
- **Penamaan:**
  - Model: PascalCase tunggal (`App\Models\Order`, `App\Models\PosSession`).
  - Controller/Livewire: PascalCase (`App\Livewire\PosManager`, `App\Livewire\Checkout`).
  - Service: `App\Services\<Domain>Service.php` (`PosTransactionService`, `OrderStockService`).
  - Tabel: snake_case jamak (`orders`, `pos_sessions`, `pos_returns`, `cashflows`).
- **Gaya Penulisan:**
  - Service Layer untuk transaksi database kompleks: Menggunakan `DB::transaction()` dengan pencegahan race condition (`lockForUpdate()`).
  - Subdomain Grouping: Pembagian panel dan aplikasi via `Route::domain()` di `routes/web.php` dan `AdminPanelProvider.php`.
- **Pola yang Dipakai:**
  - **TALL Stack Hybrid:** Tailwind CSS v4, AlpineJS (via Livewire), Laravel 13, Livewire 3 + Filament v5.
  - **Channel Visibility Pattern:** Filter produk berdasarkan kolom `channel_visibility` (`online_only`, `pos_only`, `both`).
  - **Single Table Inheritance / Polymorphic relations:** Pada sistem pencatatan `cashflows` dan `stock_logs`.

## Pola yang Ada tapi Tidak Konsisten (Temuan Onboard)
- **Subdomain Handling pada Test Suite:** Pada `AdminLoginTest.php` dan `KasirAccessTest.php`, pemanggilan test menggunakan URI path biasa (`$this->get('/admin/login')`) alih-alih menyertakan host domain pengujian, mengakibatkan respon 404 karena route terikat pada subdomain `admin.` / `pos.`.
- **Eksistensi File Dummy:** Terdapat berkas `resources/views/dummy-static.blade.php` yang tidak lagi terhubung ke rute aktif manapun.
- **Toleransi Logika Tes POS Loyalty & Receipt:** Uji fungsional `PosLoyaltyTest` dan `PosReceiptSettingsTest` mengalami perbedaan ekspektasi nilai (misal jumlah stempel maksimal 9 vs 12, dan kalkulasi poin) akibat perubahan konfigurasi bisnis terkini yang belum disinkronkan ke assertions pengujian.
