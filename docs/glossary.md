# Glosarium Istilah Proyek

_Terakhir diverifikasi: 2026-11-08, commit `32bece31`_  
Satu konsep = satu nama. Jangan membuat padanan baru; tambahkan di sini jika memang baru.

| Konsep | Nama Resmi | Dipakai di (UI / API / DB / Kode) | Nama yang DILARANG (Alias Lama) |
|---|---|---|---|
| Saluran Penjualan Produk | `channel_visibility` | DB: `products.channel_visibility`, UI & Kode | `product_channel`, `target_market` |
| Produk Khusus Toko Fisik | `pos_only` | DB, Kode, Filament Admin | `offline_only`, `kasir_only` |
| Produk Khusus Toko Online | `online_only` | DB, Kode, Filament Admin | `web_only`, `ecommerce_only` |
| Produk Kedua Saluran | `both` | DB, Kode | `omnichannel`, `all_channel` |
| Terminal Kasir | `POS Manager` | UI, Livewire: `PosManager`, Route: `pos.index` | `Kasir Terminal`, `PointOfSale` |
| Sesi Shift Kasir | `PosSession` | DB: `pos_sessions`, Model: `PosSession` | `ShiftKasir`, `WorkShift` |
| Pengembalian Barang POS | `PosReturn` | DB: `pos_returns`, Model: `PosReturn` | `RefundPos`, `ReturKasir` |
| Stempel Loyalitas Pelanggan | `PosStampLog` / Cap Digital | DB: `pos_stamp_logs`, Model: `PosCustomer` | `LoyaltyPoint`, `KuponCap` |
| Mutasi Keluar/Masuk Stok | `StockLog` | DB: `stock_logs`, Model: `StockLog` | `InventoryHistory`, `StokMutasi` |
| Arus Kas Keuangan | `Cashflow` | DB: `cashflows`, Model: `Cashflow` | `BukuKas`, `Keuangan` |
| Cetak Struk Kasir | `EscPosService` | Service: `app/Services/EscPosService.php` | `ThermalPrinterService`, `RawBT` |
| Pengelola Sistem | `Super Admin` | Filament Shield, Model `User` | `Administrator`, `Root` |
| Kasir Toko Fisik | `Kasir` | Spatie Role: `kasir`, Model `User` | `Cashier`, `Operator Kasir` |
| Pelanggan POS | `PosCustomer` | DB: `pos_customers`, Model: `PosCustomer` | `CustomerPos`, `MemberToko` |
