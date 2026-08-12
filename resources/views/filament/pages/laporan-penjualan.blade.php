<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Form Filter Tanggal & Channel -->
        <form wire:submit.prevent="applyFilters">
            {{ $this->form }}
        </form>

        <!-- Product Performance Widget (Paling Laku vs Paling Lambat Laku) -->
        @livewire(\App\Filament\Widgets\ProductPerformanceWidget::class, ['filters' => $filters])

        <!-- Tabel Detail Transaksi Penjualan -->
        <div>
            <h3 class="text-lg font-bold mb-4 px-1 text-gray-900 dark:text-white">Rincian Transaksi Penjualan Terfilter</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
