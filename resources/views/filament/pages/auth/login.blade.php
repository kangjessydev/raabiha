<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- LEFT PANEL: Clean Hero Image Only (No Text, No AI Overlays) -->
    <div class="lg:col-span-6 xl:col-span-7 relative hidden lg:block overflow-hidden bg-gray-100">
        <img src="{{ asset('images/admin_login_hero.jpg') }}" 
             alt="Raabiha Storefront" 
             class="w-full h-full object-cover object-center">
    </div>

    <!-- RIGHT PANEL: Filament Light Theme Form -->
    <div class="lg:col-span-6 xl:col-span-5 flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-white min-h-screen border-l border-gray-200/80 shadow-sm">
        <!-- Top Navigation Bar -->
        <div class="flex items-center justify-between text-xs text-gray-500 font-medium pb-4">
            <a href="/" class="inline-flex items-center gap-1.5 hover:text-emerald-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Lihat Toko</span>
            </a>

            <a href="/pos/login" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 border border-gray-200 transition-all font-semibold">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Login POS Kasir</span>
            </a>
        </div>

        <!-- Mobile Header Hero Banner (Visible only on mobile) -->
        <div class="lg:hidden w-full h-44 rounded-2xl overflow-hidden mb-6 relative">
            <img src="{{ asset('images/admin_login_hero.jpg') }}" alt="Raabiha" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-4">
                <span class="text-white font-bold text-lg">Raabiha Admin</span>
            </div>
        </div>

        <!-- Main Form Container -->
        <div class="w-full max-w-sm mx-auto my-auto py-6 space-y-6">
            <!-- Brand Title -->
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold text-xl flex items-center justify-center shadow-sm">
                        R
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Raabiha Admin</h1>
                        <p class="text-xs text-gray-500">Panel Manajemen Toko & POS</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form wire:submit="authenticate" class="space-y-5">
                {{ $this->form }}

                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-xl shadow-sm hover:shadow transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer flex items-center justify-center gap-2">
                    <span>Masuk</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-gray-400 pt-6 border-t border-gray-100">
            &copy; {{ date('Y') }} Raabiha Modest Fashion
        </div>
    </div>
</div>
