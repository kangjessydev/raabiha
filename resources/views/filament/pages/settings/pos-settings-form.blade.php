<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-filament::button type="submit" size="lg" icon="heroicon-m-check">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
