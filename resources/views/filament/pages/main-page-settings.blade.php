<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @if (auth()->user()->can('Update:MainPageSettings'))
        <div class="flex flex-wrap items-center gap-4 mt-6">
            <x-filament::button type="submit" size="lg">
                Simpan Perubahan Halaman
            </x-filament::button>
        </div>
        @endif
    </form>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
