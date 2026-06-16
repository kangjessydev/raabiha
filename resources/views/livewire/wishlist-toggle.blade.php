<div x-data="{ active: @js($isInWishlist) }">
    @if($isDetail)
    <button wire:click.prevent.stop="toggleWishlist" x-on:click="active = !active"
            class="w-14 shrink-0 h-full border border-[#e5e2de] transition-colors flex justify-center items-center focus:outline-none relative"
            :class="active ? 'text-red-500 bg-red-50' : 'text-[#1c1c1a] hover:bg-[#f2efe8]'"
            title="{{ $isInWishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}">
        
        <!-- Filled Heart (Active) -->
        <svg x-show="active" x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-50"
             x-transition:enter-end="opacity-100 scale-100"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
        </svg>

        <!-- Outline Heart (Inactive) -->
        <svg x-show="!active" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
    </button>
    @else
    <button wire:click.prevent.stop="toggleWishlist" x-on:click="active = !active"
            class="flex items-center justify-center w-8 h-8 rounded-full shadow-md transition-colors duration-200 relative overflow-hidden"
            :class="active ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-white/95 backdrop-blur-sm text-[#615e57] hover:text-[#b91c1c] hover:bg-red-50 hover:scale-110 active:scale-95'"
            title="{{ $isInWishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}">
        
        <!-- Filled Heart (Active) -->
        <svg x-show="active" x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-50"
             x-transition:enter-end="opacity-100 scale-100"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
        </svg>

        <!-- Outline Heart (Inactive) -->
        <svg x-show="!active" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
    </button>
    @endif
</div>
