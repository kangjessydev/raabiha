<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Panduan --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5 text-gray-500" />
                    <span>Ekspor Data ke Excel / CSV</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Gunakan tombol-tombol ekspor di kanan atas halaman ini untuk mengunduh data dalam format Excel atau CSV. Setiap ekspor diproses secara asinkron di latar belakang.
            </x-slot>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <x-heroicon-o-shopping-bag class="w-6 h-6 text-primary-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Produk</p>
                        <p>Nama, harga, stok, kategori, SEO, dan semua atribut produk.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Pesanan</p>
                        <p>Semua data transaksi, status pembayaran, dan informasi pengiriman.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <x-heroicon-o-users class="w-6 h-6 text-info-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Pengguna</p>
                        <p>Data akun pelanggan dan informasi status reseller.</p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Notes --}}
        <x-filament::section color="info">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5" />
                    <span>Cara Mengunduh</span>
                </div>
            </x-slot>
            <ul class="mt-2 list-disc list-inside space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                <li>Klik tombol <strong>"Ekspor ..."</strong> di pojok kanan atas halaman ini.</li>
                <li>Pilih kolom yang ingin disertakan, lalu klik <strong>Ekspor</strong>.</li>
                <li>Proses berjalan di latar belakang. Saat selesai, notifikasi dan tautan unduh akan muncul di ikon 🔔 lonceng.</li>
                <li>File dapat dibuka langsung di Microsoft Excel, Google Sheets, atau LibreOffice Calc.</li>
                <li>Gunakan file hasil ekspor ini sebagai <strong>template acuan utama</strong> saat Anda ingin melakukan proses <strong>Impor Data (Update / Tambah)</strong>.</li>
            </ul>
        </x-filament::section>

    </div>
</x-filament-panels::page>
