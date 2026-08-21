<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Native Filament Stats Overview Widget -->
        @livewire(\App\Filament\Widgets\LaporanStokOverviewWidget::class)

        <!-- Tabel Valuasi & Inventaris -->
        <div>
            <h3 class="text-lg font-bold mb-4 px-1 text-gray-900 dark:text-white">Rincian Valuasi & Sisa Stok Produk</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
