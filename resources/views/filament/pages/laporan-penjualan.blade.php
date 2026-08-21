<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Form Filter Tanggal & Channel -->
        <form wire:submit.prevent="applyFilters">
            {{ $this->form }}
        </form>

        <!-- Native Filament Widgets for Top vs Slow Selling Products -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @livewire(\App\Filament\Widgets\TopSellingProductsWidget::class, ['filters' => $filters])
            @livewire(\App\Filament\Widgets\SlowSellingProductsWidget::class, ['filters' => $filters])
        </div>

        <!-- Tabel Detail Transaksi Penjualan -->
        <div>
            <h3 class="text-lg font-bold mb-4 px-1 text-gray-900 dark:text-white">Rincian Transaksi Penjualan Terfilter</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
