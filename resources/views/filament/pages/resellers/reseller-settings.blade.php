<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4 flex gap-4">
            <x-filament::button type="submit" wire:target="save">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
