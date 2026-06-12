<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Form Filter Tanggal -->
        <form wire:submit.prevent="applyFilters">
            {{ $this->form }}
        </form>

        <!-- Cards / KPI Overview -->
        @livewire(\App\Filament\Widgets\LaporanBisnisOverview::class, ['filters' => $filters])

        <!-- Charts Section -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @livewire(\App\Filament\Widgets\LaporanBisnisChart::class, ['filters' => $filters])
            </div>
            <div class="xl:col-span-1">
                @livewire(\App\Filament\Widgets\LaporanBisnisDoughnutChart::class, ['filters' => $filters])
            </div>
        </div>

        <!-- Tabel Detail -->
        <div>
            <h3 class="text-lg font-bold mb-4 px-1 text-gray-900 dark:text-white">Rincian Transaksi Rentang Waktu Terpilih</h3>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
