<x-filament-widgets::widget class="fi-wi-stats-overview">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-lg font-medium tracking-tight text-gray-950 dark:text-white">
            Ringkasan Transaksi
        </h2>
        
        <div class="flex items-center gap-2">
            <!-- Filter Channel Penjualan (Celah 20) -->
            <x-filament::input.wrapper class="w-44">
                <x-filament::input.select wire:model.live="channel">
                    <option value="all">Semua Channel</option>
                    <option value="online">Online Web</option>
                    <option value="pos">POS Kasir</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <!-- Filter Periode -->
            <x-filament::input.wrapper class="w-44">
                <x-filament::input.select wire:model.live="period">
                    <option value="today">Hari Ini</option>
                    <option value="week">7 Hari Terakhir</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    {{ $this->content }}
</x-filament-widgets::widget>
