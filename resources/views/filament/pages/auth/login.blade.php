<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-stone-950 text-stone-100 font-sans antialiased overflow-hidden selection:bg-emerald-500 selection:text-white">
    <!-- LEFT PANEL: 55% HERO VISUAL (7 Cols on desktop) -->
    <div class="lg:col-span-7 relative hidden lg:flex flex-col justify-between p-12 overflow-hidden bg-stone-950">
        <!-- Background Image with Soft Vignette & Gradient Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/admin_login_hero.jpg') }}" alt="Raabiha Luxury Boutique" class="w-full h-full object-cover object-center scale-105 filter contrast-[1.05] brightness-90 transition-transform duration-1000 ease-out hover:scale-100">
            <!-- Luxury Dark Vignette Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/40 to-stone-950/20"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-stone-950/70 via-stone-950/20 to-stone-950"></div>
        </div>

        <!-- Top Header Logo Branding -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-600/90 backdrop-blur-md flex items-center justify-center text-white font-extrabold text-2xl shadow-xl shadow-emerald-950/60 border border-emerald-400/40">
                    R
                </div>
                <div>
                    <span class="text-2xl font-bold tracking-widest uppercase text-white font-serif">RAABIHA</span>
                    <span class="block text-[10px] text-amber-300 tracking-widest uppercase font-semibold">Haute Couture & Modest Fashion</span>
                </div>
            </div>
            
            <div class="px-4 py-1.5 rounded-full bg-black/50 backdrop-blur-md border border-white/15 text-[11px] font-medium text-stone-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>System Online v2.5</span>
            </div>
        </div>

        <!-- Bottom Quote & Tagline Box (Glassmorphism) -->
        <div class="relative z-10 max-w-xl space-y-4">
            <div class="p-6 rounded-3xl bg-stone-900/70 backdrop-blur-xl border border-white/20 shadow-2xl space-y-3">
                <div class="flex items-center gap-1 text-amber-400 text-sm">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    <span class="text-stone-300 text-xs font-semibold ml-2">Exclusive Management Control</span>
                </div>
                <blockquote class="text-lg font-serif italic text-stone-100 leading-relaxed">
                    "Elegance in Every Detail. Empowering Raabiha's modest luxury fashion storefront, inventory, and omnichannel point of sale."
                </blockquote>
                <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs text-stone-400">
                    <span>Raabiha Executive Control Panel</span>
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        SSL 256-bit Encrypted
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: FORM (5 Cols on desktop) -->
    <div class="lg:col-span-5 flex flex-col justify-between p-6 sm:p-12 bg-stone-900 relative z-10 border-l border-white/5">
        <!-- Top Navigation Links -->
        <div class="flex items-center justify-between text-xs">
            <a href="/" class="inline-flex items-center gap-1.5 text-stone-400 hover:text-emerald-400 transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Ke Toko Utama</span>
            </a>
            
            <a href="/pos/login" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-stone-800 hover:bg-emerald-950 text-emerald-400 hover:text-emerald-300 border border-emerald-900/50 font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Login POS Kasir</span>
            </a>
        </div>

        <!-- Center Form Content -->
        <div class="w-full max-w-md mx-auto my-auto py-8 space-y-8">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-700/50 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Admin & Management Portal
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight text-white font-serif">Selamat Datang Kembali</h2>
                <p class="text-sm text-stone-400">Masukkan akun administrator atau manajerial Anda untuk melanjutkan.</p>
            </div>

            <!-- Filament Form Renderer -->
            <form wire:submit="authenticate" class="space-y-6">
                {{ $this->form }}

                <x-filament::button type="submit" form="authenticate" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-bold rounded-2xl shadow-xl shadow-emerald-950/50 border border-emerald-500/30 transition-all active:scale-[0.99] text-base cursor-pointer">
                    Masuk Portal Admin
                </x-filament::button>
            </form>
        </div>

        <!-- Bottom Copyright Footer -->
        <div class="text-center text-xs text-stone-500 pt-6 border-t border-white/5">
            &copy; {{ date('Y') }} Raabiha Modest Fashion. Hak Cipta Dilindungi Undang-Undang.
        </div>
    </div>
</div>
