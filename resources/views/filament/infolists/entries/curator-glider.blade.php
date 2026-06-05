<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="mb-4 rounded-xl overflow-hidden shadow-sm max-w-3xl mx-auto w-full">
        @if($getState())
            <x-curator-glider class="w-full h-auto object-cover" :media="$getState()" fallback="https://via.placeholder.com/800x400?text=No+Cover" />
        @else
            <div class="w-full h-48 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                <span>Tidak ada gambar sampul</span>
            </div>
        @endif
    </div>
</x-dynamic-component>
