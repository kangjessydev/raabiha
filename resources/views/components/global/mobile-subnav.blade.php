@props(['title', 'backUrl' => url()->previous(), 'transparent' => false, 'share' => false])

<div class="md:hidden {{ $transparent ? 'absolute top-0 left-0 right-0 z-50 bg-transparent border-none' : 'sticky top-0 z-50 bg-[#fcf9f5] border-b border-[#e5e5e5]' }} px-4 py-3 flex items-center justify-between">
    <!-- Back Button -->
    <a href="{{ $backUrl }}" class="flex items-center justify-center w-10 h-10 -ml-2 rounded-full {{ $transparent ? 'bg-white/80 backdrop-blur-sm shadow-sm hover:bg-white active:bg-white/90' : 'hover:bg-black/5 active:bg-black/10' }} transition-colors">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
    </a>

    <!-- Title -->
    <h1 class="{{ $transparent ? 'bg-white/80 backdrop-blur-sm px-4 py-1.5 rounded-full shadow-sm text-[10px] font-mono font-bold tracking-[0.2em] uppercase' : 'text-sm font-semibold tracking-widest uppercase text-black' }} absolute left-1/2 -translate-x-1/2">
        {{ $title }}
    </h1>

    <!-- Right Button/Placeholder -->
    @if($share)
        <button type="button" onclick="document.getElementById('share-modal').classList.remove('hidden')" class="flex items-center justify-center w-10 h-10 -mr-2 rounded-full {{ $transparent ? 'bg-white/80 backdrop-blur-sm shadow-sm hover:bg-white active:bg-white/90' : 'hover:bg-black/5 active:bg-black/10' }} transition-colors text-black">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
        </button>
    @else
        <div class="w-10 h-10"></div>
    @endif
</div>
