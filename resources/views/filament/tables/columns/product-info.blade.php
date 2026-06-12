<div class="flex items-center gap-3 px-3 py-2 max-w-xs w-full">
    @php
        $mediaIds = $getRecord()->images ?? [];
        $firstMediaId = is_array($mediaIds) ? ($mediaIds[0] ?? null) : $mediaIds;
        $media = $firstMediaId ? \Awcodes\Curator\Models\Media::find($firstMediaId) : null;
    @endphp
    
    @if($media)
        <img src="{{ $media->url }}" alt="{{ $getRecord()->name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-800" />
    @else
        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400 ring-2 ring-gray-100 dark:ring-gray-800">
            <x-heroicon-o-shopping-bag class="w-5 h-5" />
        </div>
    @endif

    <div class="flex flex-col min-w-0">
        <span class="font-medium text-sm text-gray-900 dark:text-white whitespace-normal line-clamp-2">{{ $getRecord()->name }}</span>
        <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $getRecord()->category?->name ?? 'Tanpa Kategori' }}</span>
    </div>
</div>
