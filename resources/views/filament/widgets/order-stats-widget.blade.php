<x-filament-widgets::widget class="fi-wi-stats-overview">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-medium tracking-tight text-gray-950 dark:text-white">
            Statistik Pesanan
        </h2>
        
        <x-filament::input.wrapper class="w-48">
            <x-filament::input.select wire:model.live="period">
                @foreach ($this->getPeriods() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    {{ $this->content }}
</x-filament-widgets::widget>
