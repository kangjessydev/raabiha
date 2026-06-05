<div class="px-4 py-2">
    @if($record->image)
        <div class="mb-6 w-full max-w-2xl mx-auto rounded-xl overflow-hidden shadow-sm">
            <x-curator-glider class="w-full h-auto object-cover" :media="$record->image" fallback="https://via.placeholder.com/800x400?text=No+Cover" />
        </div>
    @endif
    
    <div class="mb-4">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white leading-tight">
            {{ $record->title }}
        </h1>
        <div class="flex items-center gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
            @if($record->category)
                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/20 dark:text-green-400">
                    {{ $record->category->name }}
                </span>
            @endif
            <span>
                Dipublikasikan: {{ $record->published_at ? $record->published_at->translatedFormat('d M Y') : 'Draft' }}
            </span>
            @if($record->author)
                <span>
                    Oleh: {{ $record->author->name }}
                </span>
            @endif
        </div>
    </div>
    
    <div class="prose dark:prose-invert max-w-none border-t border-gray-100 dark:border-gray-800 pt-6">
        {!! $record->content !!}
    </div>
    
    @if($record->tags && $record->tags->count() > 0)
        <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">Tags:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($record->tags as $tag)
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
