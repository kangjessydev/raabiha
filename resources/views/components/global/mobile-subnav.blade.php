@props(['title', 'backUrl' => url()->previous(), 'transparent' => false, 'cart' => false])

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
    @if($cart)
        <a href="{{ url('/cart') }}" wire:navigate.hover class="flex items-center justify-center w-10 h-10 -mr-2 rounded-full {{ $transparent ? 'bg-white/80 backdrop-blur-sm shadow-sm hover:bg-white active:bg-white/90' : 'hover:bg-black/5 active:bg-black/10' }} transition-colors text-black">
            <div class="relative inline-flex">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <livewire:cart-badge />
            </div>
        </a>
    @else
        <div class="w-10 h-10"></div>
    @endif
</div>
