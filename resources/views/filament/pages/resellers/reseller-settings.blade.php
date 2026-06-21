<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        @if (auth()->user()->can('Update:User'))
        <div class="mt-4 flex gap-4">
            <x-filament::button type="submit" wire:target="save">
                Simpan Pengaturan
            </x-filament::button>
        </div>
        @endif
    </form>
</x-filament-panels::page>
