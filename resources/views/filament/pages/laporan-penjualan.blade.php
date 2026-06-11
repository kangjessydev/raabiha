<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Kita biarkan Filament Panels merender header widgets secara native --}}
        @if ($this->hasHeaderWidgets())
            <x-filament-widgets::widgets
                :columns="$this->getHeaderWidgetsColumns()"
                :data="$this->getWidgetData()"
                :widgets="$this->getVisibleHeaderWidgets()"
            />
        @endif
    </div>
</x-filament-panels::page>
