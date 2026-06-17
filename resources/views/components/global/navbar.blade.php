@php
    $siteName = \App\Models\SiteSetting::where('key', 'site_name')->value('value') ?? 'RAABIHA';
    
    $logoLightId = \App\Models\SiteSetting::where('key', 'site_logo_light')->value('value');
    $logoLightMedia = $logoLightId ? \Awcodes\Curator\Models\Media::find($logoLightId) : null;
    
    $logoLightUrl = null;
    if ($logoLightMedia) {
        $logoLightUrl = Storage::url($logoLightMedia->path);
    } elseif (file_exists(public_path('assets/images/logo-desktop.png'))) {
        $logoLightUrl = asset('assets/images/logo-desktop.png');
    }

    $logoMobileUrl = null;
    if ($logoLightMedia) {
        $logoMobileUrl = Storage::url($logoLightMedia->path);
    } elseif (file_exists(public_path('assets/images/logo-mobile.png'))) {
        $logoMobileUrl = asset('assets/images/logo-mobile.png');
    }
@endphp
<div class="w-full z-[100] sticky top-0">
    <header class="w-full bg-[#fcf9f5] border-b border-[#e5e2de] transition-transform duration-300 transform translate-y-0" id="smart-navbar">
        
        <!-- ==================== DESKTOP (Hidden on Mobile) ==================== -->
        <div class="hidden md:block">
            <!-- DESKTOP LAYOUT -->
            <div class="flex items-center justify-between px-12 py-5 relative z-50 bg-[#fcf9f5]">
                <!-- Desktop Logo -->
                <div class="w-[180px] shrink-0">
                    <a href="{{ url('/') }}" wire:navigate.hover class="block hover:opacity-80 transition-opacity">
                        @if($logoLightUrl)
                            <img src="{{ $logoLightUrl }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain">
                        @else
                            <span class="text-2xl font-bold tracking-widest text-[#064e3b] font-serif uppercase whitespace-nowrap">{{ strtoupper($siteName) }}</span>
                        @endif
                    </a>
                </div>
                
                <!-- Nav Menu -->
                @php
                    $rawNavbarLinks = \App\Models\SiteSetting::where('key', 'navbar_links')->value('value');
                    $navbarLinks = $rawNavbarLinks ? json_decode($rawNavbarLinks, true) : [];
                    if (!is_array($navbarLinks)) $navbarLinks = [];
                @endphp
                <nav class="flex gap-8"><ul id="menu-main-menu" class="flex gap-8 m-0 p-0 list-none">
                @foreach($navbarLinks as $link)
                    @php
                        $path = ltrim(parse_url($link['url'], PHP_URL_PATH) ?? '', '/');
                        $isActive = $path === '' ? request()->is('/') : (request()->is($path) || request()->is($path . '/*') || ($path === 'shop' && request()->is('product*')));
                    @endphp
                    <li class="group"><a href="{{ url($link['url']) }}" wire:navigate.hover class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ $isActive ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">{{ $link['label'] }}</a></li>
                @endforeach
                </ul></nav>            
                <div class="flex items-center gap-5 w-[180px] justify-end shrink-0">
                    <!-- Search -->
                    <button class="search-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <!-- Cart -->
                    <button type="button" onclick="Livewire.dispatch('open-mini-cart')" id="desktop-cart-icon" class="cart-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors relative focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <livewire:cart-badge />
                    </button>
                    <!-- Account/Dashboard -->
                    <div class="relative">
                        <button id="desktop-profile-toggle" class="text-[#1c1c1a] hover:text-[#064e3b] transition-colors flex items-center justify-center w-6 h-6 rounded-full overflow-hidden focus:outline-none">
                            @auth
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=064e3b&color=fff" alt="Profile" class="w-full h-full object-cover">
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @endauth
                        </button>
                        <!-- Dropdown -->
                        <div id="desktop-profile-dropdown" class="absolute right-0 mt-3 w-48 bg-[#fcf9f5] border border-[#e5e2de] shadow-lg rounded-sm py-2 opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-200 z-50">
                            @auth
                                <div class="px-4 py-2 border-b border-[#e5e2de] mb-1">
                                    <span class="block text-xs font-mono font-bold uppercase tracking-widest text-[#1c1c1a]">{{ auth()->user()->name }}</span>
                                </div>
                                <a href="/account" wire:navigate.hover class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Dasbor Pelanggan</a>
                                @if(auth()->user()->hasRole('super_admin'))
                                    <a href="/admin" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Admin Panel</a>
                                @elseif(auth()->user()->hasRole('reseller'))
                                    <a href="/reseller-dashboard" wire:navigate.hover class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Portal Reseller</a>
                                @endif
                                <a href="/cart" wire:navigate.hover class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Keranjang Saya</a>
                                <form method="POST" action="{{ route('logout') }}" class="block m-0">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm font-sans text-red-600 hover:bg-[#f0ede9] transition-colors">Keluar (Logout)</button>
                                </form>
                            @else
                                <a href="/login" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Log In</a>
                                <a href="/register" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Daftar Akun</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MOBILE (Hidden on Desktop) ==================== -->
        <div class="block md:hidden">
            <!-- MOBILE LAYOUT -->
            <div class="flex items-center justify-between px-6 py-4 relative z-50 bg-[#fcf9f5]">
                <!-- Hamburger Menu (Left) -->
                <button id="mobile-menu-toggle" class="text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <!-- Mobile Logo (Center) -->
                <a href="{{ url('/') }}" wire:navigate.hover class="block hover:opacity-80 transition-opacity absolute left-1/2 -translate-x-1/2">
                    @if($logoMobileUrl)
                        <img src="{{ $logoMobileUrl }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                    @else
                        <span class="text-xl font-bold tracking-widest text-[#064e3b] font-serif uppercase whitespace-nowrap">{{ strtoupper($siteName) }}</span>
                    @endif
                </a>

                <!-- Search & Cart (Right) -->
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <button class="search-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Global Search Overlay (Slide down) -->
        <div id="search-overlay" class="absolute top-full left-0 right-0 bg-[#fcf9f5] border-b border-[#e5e2de] shadow-sm transform -translate-y-full opacity-0 pointer-events-none transition-all duration-300 z-40">
            <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4">
                <form role="search" method="get" action="/search" class="flex items-center gap-4">
                    <svg class="w-5 h-5 text-[#a3a3a3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="q" placeholder="Cari gamis, outerwear, hijab..." class="w-full bg-transparent border-none text-[13px] font-sans text-[#1c1c1a] placeholder-[#a3a3a3] outline-none focus:ring-0">
                    <button type="button" id="search-close" class="text-[#1c1c1a] hover:text-red-500 transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>
</div>