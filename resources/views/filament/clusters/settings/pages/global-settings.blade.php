<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        @if (auth()->user()->can('Update:GlobalSettings'))
        <div class="mt-4">
            <x-filament::button type="submit">
                Simpan Pengaturan
            </x-filament::button>
        </div>
        @endif
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>