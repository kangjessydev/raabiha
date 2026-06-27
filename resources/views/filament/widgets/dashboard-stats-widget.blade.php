<x-filament-widgets::widget class="fi-wi-stats-overview">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium tracking-tight text-gray-950 dark:text-white">
            Ringkasan Transaksi
        </h2>
        
        <x-filament::input.wrapper class="w-48">
            <x-filament::input.select wire:model.live="period">
                <option value="today">Hari Ini</option>
                <option value="week">7 Hari Terakhir</option>
                <option value="month">Bulan Ini</option>
                <option value="year">Tahun Ini</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    {{ $this->content }}
</x-filament-widgets::widget>
