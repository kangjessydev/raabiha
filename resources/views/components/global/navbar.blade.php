<header class="sticky top-0 z-50 bg-[#fcf9f5] border-b border-[#e5e2de] transition-all duration-300">
        
        <!-- ==================== DESKTOP LAYOUT ==================== -->
        <div class="hidden md:flex items-center justify-between px-12 py-5 relative z-50 bg-[#fcf9f5]">
            <!-- Desktop Logo -->
            <a href="{{ url('/') }}" wire:navigate class="block hover:opacity-80 transition-opacity">
                <!-- Gunakan gambar statis logo-desktop.png -->
                <img src="{{ asset('assets/images/logo-desktop.png') }}" alt="Raabiha" class="h-10 w-auto object-contain" onerror="this.outerHTML='<span class=\'text-2xl font-bold tracking-widest text-[#064e3b] font-serif uppercase\'>RAABIHA</span>'">
            </a>
            
            <!-- Nav Menu -->
            <nav class="flex gap-8"><ul id="menu-main-menu" class="flex gap-8 m-0 p-0 list-none">
<li class="group"><a href="{{ url('/') }}" wire:navigate class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ request()->is('/') ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">Beranda</a></li>
<li class="group"><a href="{{ url('/about') }}" wire:navigate class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ request()->is('about') ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">Tentang Kami</a></li>
<li class="group"><a href="{{ url('/contact') }}" wire:navigate class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ request()->is('contact') ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">Lokasi &#038; Kontak</a></li>
<li class="group"><a href="{{ url('/gallery') }}" wire:navigate class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ request()->is('gallery') ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">Galeri</a></li>
<li class="group"><a href="{{ url('/blog') }}" wire:navigate class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ request()->is('blog*') ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">Blog</a></li>
<li class="group"><a href="{{ url('/shop') }}" wire:navigate class="text-xs font-medium tracking-widest uppercase transition-colors whitespace-nowrap {{ request()->is('shop*') || request()->is('product*') ? 'text-[#064e3b] border-b border-[#064e3b] pb-0.5' : 'text-neutral-500 hover:text-neutral-900' }}">Katalog</a></li>
</ul></nav>            
            <div class="flex items-center gap-5">
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
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </button>
                    <!-- Dropdown -->
                    <div id="desktop-profile-dropdown" class="absolute right-0 mt-3 w-48 bg-[#fcf9f5] border border-[#e5e2de] shadow-lg rounded-sm py-2 opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-200 z-50">
                                                    <a href="/account" wire:navigate class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Akun Saya</a>
                                                    <a href="/login" wire:navigate class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Log In</a>
                                                    <a href="/register" wire:navigate class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Daftar Akun</a>
                                            </div>
                </div>
            </div>
        </div>

        <!-- ==================== MOBILE LAYOUT ==================== -->
                    <div class="md:hidden flex items-center justify-between px-6 py-4 relative z-50 bg-[#fcf9f5]">
                <!-- Hamburger Menu (Left) -->
                <button id="mobile-menu-toggle" class="text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <!-- Mobile Logo (Center) -->
                <a href="{{ url('/') }}" wire:navigate class="block hover:opacity-80 transition-opacity absolute left-1/2 -translate-x-1/2">
                    <!-- Gunakan gambar statis logo-mobile.png -->
                    <img src="{{ asset('assets/images/logo-mobile.png') }}" alt="Raabiha" class="h-8 w-auto object-contain" onerror="this.outerHTML='<span class=\'text-xl font-bold tracking-widest text-[#064e3b] font-serif uppercase\'>R</span>'">
                </a>

                <!-- Search & Cart (Right) -->
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <button class="search-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <!-- Cart Moved to Bottom Nav -->
                </div>
            </div>
        
        <!-- Global Search Overlay (Slide down) -->
        <div id="search-overlay" class="absolute top-full left-0 right-0 bg-[#fcf9f5] border-b border-[#e5e2de] shadow-sm transform -translate-y-full opacity-0 pointer-events-none transition-all duration-300 z-40">
            <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4">
                <form role="search" method="get" action="index.html" class="flex items-center gap-4">
                    <input type="hidden" name="post_type" value="product" />
                    <svg class="w-5 h-5 text-[#a3a3a3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="s" placeholder="Cari gamis, outerwear, hijab..." class="w-full bg-transparent border-none text-[13px] font-sans text-[#1c1c1a] placeholder-[#a3a3a3] outline-none focus:ring-0">
                    <button type="button" id="search-close" class="text-[#1c1c1a] hover:text-red-500 transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>