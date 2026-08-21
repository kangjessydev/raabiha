<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Pusat Ekspor Data</x-slot>
        <x-slot name="description">
            Klik tombol <strong>Ekspor Data</strong> di pojok kanan atas untuk memilih jenis data yang ingin diekspor ke file Excel/CSV.
        </x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-filament::card>
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-shopping-bag" class="h-6 w-6 text-primary-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Produk & Varian</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ekspor daftar produk beserta harga, stok, channel, dan atribut varian.</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-6 w-6 text-success-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Pesanan</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ekspor riwayat transaksi dengan filter status pembayaran dan tanggal.</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-users" class="h-6 w-6 text-info-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Pengguna</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ekspor data akun pelanggan dan reseller dengan filter status.</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-folder" class="h-6 w-6 text-warning-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Kategori</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ekspor seluruh kategori produk yang tersedia di toko.</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6 text-gray-400 mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Artikel Blog</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ekspor seluruh artikel blog yang telah dipublikasikan.</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-ticket" class="h-6 w-6 text-danger-500 mt-0.5 shrink-0" />
                    <div>
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Voucher</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ekspor data kode voucher diskon yang aktif maupun nonaktif.</p>
                    </div>
                </div>
            </x-filament::card>
        </div>
    </x-filament::section>
</x-filament-panels::page>

