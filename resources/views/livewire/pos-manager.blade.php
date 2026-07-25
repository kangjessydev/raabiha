<div x-data="posSystem()" class="h-screen w-full flex flex-col md:flex-row overflow-hidden bg-gray-50/50 relative">

    <!-- Notifications Toast -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 class="glass px-6 py-4 rounded-xl flex items-center gap-3 shadow-lg"
                 :class="toast.type === 'error' ? 'border-red-400 bg-red-50/80' : 'border-green-400 bg-green-50/80'">
                <div x-text="toast.message" class="font-medium" :class="toast.type === 'error' ? 'text-red-800' : 'text-green-800'"></div>
            </div>
        </template>
    </div>

    <!-- Cek Session -->
    @if(!$activeSession)
        <!-- Overlay Buka Shift -->
        <div class="absolute inset-0 z-40 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm">
            <div class="glass w-full max-w-md rounded-2xl p-8 relative shadow-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Buka Shift Kasir</h2>
                    <p class="text-gray-500 text-sm mt-1">Masukkan modal awal (uang kas di laci) untuk memulai transaksi.</p>
                </div>
                
                <form wire:submit.prevent="openSession" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modal Awal (Rp)</label>
                        <input type="number" wire:model="openingCash" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-lg py-3 px-4" placeholder="0">
                        @error('openingCash') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-brand-500/30 transition-all active:scale-95">
                        Mulai Sesi
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- MAIN POS AREA -->

        <!-- Sidebar -->
        <div :class="isSidebarOpen ? 'w-64' : 'w-20'" class="bg-white border-r border-gray-200 flex flex-col justify-between h-full transition-all duration-300 z-30 shadow-[4px_0_24px_rgba(0,0,0,0.02)] flex-shrink-0 relative">
            <div class="overflow-y-auto overflow-x-hidden no-scrollbar">
                <!-- Logo -->
                <div class="h-16 flex items-center border-b border-gray-100 px-4 transition-all duration-300" :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center text-white font-bold shadow-sm shadow-brand-500/30 flex-shrink-0">R</div>
                    <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-bold text-gray-800 text-lg tracking-tight whitespace-nowrap">Raabiha POS</span>
                </div>
                
                <!-- Menus -->
                <nav class="p-3 space-y-2 mt-2">
                    <button @click="activePage = 'kasir'" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl font-medium transition-colors" :class="[activePage==='kasir' ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Kasir">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Kasir</span>
                    </button>
                    <button @click="activePage = 'history'" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl font-medium transition-colors" :class="[activePage==='history' ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Riwayat Transaksi">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Riwayat Transaksi</span>
                    </button>
                    <button @click="activePage = 'customers'" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl font-medium transition-colors" :class="[activePage==='customers' ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Pelanggan">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Pelanggan</span>
                    </button>
                    <button @click="activePage = 'cashsummary'" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl font-medium transition-colors" :class="[activePage==='cashsummary' ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', isSidebarOpen ? 'justify-start' : 'justify-center']" title="Rekap Kas">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Rekap Kas</span>
                    </button>
                    <button @click="lockScreen()" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl font-medium text-amber-700 hover:bg-amber-50 transition-colors" :class="[isSidebarOpen ? 'justify-start' : 'justify-center']" title="Kunci Layar">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span x-show="isSidebarOpen" x-transition.opacity.duration.300ms class="whitespace-nowrap">Kunci Layar</span>
                    </button>
                    
                    <div class="pt-4 mt-4 border-t border-gray-100">
                        <!-- Printer -->
                        <div class="px-2" :class="isSidebarOpen ? 'lg:px-2' : 'px-0'">
                            <label x-show="isSidebarOpen" class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 pl-2 transition-opacity duration-300">Perangkat</label>
                            <div class="flex flex-col gap-2 p-2 rounded-xl border transition-all" :class="isSidebarOpen ? 'bg-gray-50 border-gray-100' : 'bg-transparent border-transparent'">
                                <select x-show="isSidebarOpen" x-model="printerType" class="w-full text-sm border-none bg-transparent focus:ring-0 cursor-pointer text-gray-700 font-semibold py-1 transition-opacity duration-300">
                                    <option value="bluetooth">Bluetooth</option>
                                    <option value="serial">USB/Serial</option>
                                </select>
                                <button @click="connectPrinter()" :class="[printerConnected ? 'bg-green-100 text-green-700 border-green-200' : 'bg-white hover:border-red-300 hover:text-red-600 text-red-500 border-red-200', isSidebarOpen ? 'justify-center px-3 py-2 border shadow-sm' : 'justify-center p-2.5']" class="w-full rounded-lg text-sm font-bold transition-all flex items-center gap-2 relative" title="Koneksi Printer">
                                    <span class="relative flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        <!-- Dot indicator tampil saat sidebar collapsed -->
                                        <span x-show="!isSidebarOpen" class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full border-2 border-white" :class="printerConnected ? 'bg-green-500' : 'bg-red-500 animate-ping'" style="display:none;"></span>
                                    </span>
                                    <span x-show="isSidebarOpen" x-text="printerConnected ? '✓ Tersambung' : '✗ Belum Terhubung'" class="whitespace-nowrap"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            
            <!-- Profile / User Menu -->
            <div class="p-3 border-t border-gray-200 bg-gray-50/50" x-data="{ showUserMenu: false }" @click.away="showUserMenu = false">
                <!-- Profile Button -->
                <button @click="showUserMenu = !showUserMenu"
                    class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-100 transition-all group"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'"
                    title="{{ auth()->user()->name }}">

                    {{-- Avatar Inisial --}}
                    <div class="w-9 h-9 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm shadow-brand-500/30">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    {{-- Nama & Email (muncul saat sidebar terbuka) --}}
                    <div x-show="isSidebarOpen" x-transition.opacity.duration.200ms class="flex-1 text-left overflow-hidden">
                        <div class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->name }}</div>
                        @if(auth()->user()->email)
                            <div class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</div>
                        @endif
                    </div>

                    {{-- Chevron --}}
                    <svg x-show="isSidebarOpen" x-transition.opacity.duration.200ms class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="showUserMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Popup (muncul ke atas) -->
                <div x-show="showUserMenu"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute bottom-[72px] left-3 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50"
                     :class="isSidebarOpen ? 'w-[calc(100%-24px)]' : 'w-64'"
                     style="display:none;">

                    {{-- Header Info --}}
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Masuk sebagai</div>
                        <div class="font-bold text-gray-800 mt-0.5">{{ auth()->user()->name }}</div>
                        @if(auth()->user()->email)
                            <div class="text-xs text-gray-400">{{ auth()->user()->email }}</div>
                        @endif
                    </div>

                    <div class="p-2 space-y-1">
                        {{-- Tutup Shift --}}
                        <button @click="showUserMenu = false; showCloseSession = true;"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left rounded-xl text-red-600 hover:bg-red-50 transition-colors font-medium text-sm">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Tutup Shift Kasir
                        </button>

                        {{-- Kunci Layar --}}
                        <button @click="showUserMenu = false; lockScreen();"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left rounded-xl text-amber-700 hover:bg-amber-50 transition-colors font-medium text-sm">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Kunci Layar POS
                        </button>

                        {{-- Ganti PIN POS --}}
                        <button @click="showUserMenu = false; showChangePinModal = true;"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium text-sm">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Ganti PIN POS (6-Digit)
                        </button>

                        {{-- Ke Admin (hanya untuk yang boleh) --}}
                        @if(auth()->user()->hasAnyRole(['super_admin', 'owner', 'panel_user', 'marketing', 'finance', 'logistics', 'cs']))
                        <a href="{{ url('/admin') }}"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-left rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium text-sm">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Admin Panel
                        </a>
                        @endif

                        <div class="border-t border-gray-100 my-1"></div>

                        {{-- Keluar --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-3 py-2.5 text-left rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors font-medium text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Center: Products Area (Kasir) -->
        <div x-show="activePage === 'kasir'" class="flex-1 flex flex-col h-full bg-transparent min-w-0 overflow-hidden">
            <!-- Header/Search (Cleaned) -->
            <div class="glass border-b border-gray-200/50 p-4 sticky top-0 z-10 flex items-center gap-4">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2.5 bg-white border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-brand-600 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20" title="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex-1 relative max-w-2xl">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-11 pr-4 py-3 border-none bg-gray-100/70 hover:bg-gray-100 rounded-2xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all text-sm font-medium" placeholder="Cari Produk atau Barcode / SKU...">
                </div>
                <!-- Printer Status Indicator -->
                <button @click="connectPrinter()"
                    :title="printerConnected ? 'Printer: Tersambung (klik untuk ganti)' : 'Printer: Belum Terhubung — Klik untuk Sambungkan'"
                    class="flex items-center gap-2 pl-3 pr-4 py-2.5 rounded-xl border text-xs font-bold transition-all"
                    :class="printerConnected
                        ? 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100'
                        : 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100 animate-pulse'">
                    <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                        <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                              :class="printerConnected ? 'bg-green-400 animate-ping' : 'bg-red-400'"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                              :class="printerConnected ? 'bg-green-500' : 'bg-red-500'"></span>
                    </span>
                    <span x-text="printerConnected ? 'Printer OK' : 'Printer Offline'" class="hidden sm:inline whitespace-nowrap"></span>
                </button>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6">
                <div class="grid gap-4 grid-cols-[repeat(auto-fill,minmax(170px,1fr))]">
                    @forelse($products as $product)
                        @php
                            // Cek jika produk punya varian
                            $hasVariants = $product->variants->count() > 0;
                            $computedStock = $product->computed_stock ?? ($hasVariants ? $product->variants->sum('stock') : $product->stock);
                            $isOutOfStock = $computedStock <= 0;
                            
                            // Harga display
                            $hasPromo = false;
                            $promoPrice = null;
                            $originalPrice = null;
                            $priceDisplay = '';
                            
                            if ($product->pos_discount_price) {
                                $hasPromo = true;
                                $promoPrice = $product->pos_discount_price;
                                $originalPrice = $product->pos_price ?: $product->price;
                                $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                            } elseif ($product->pos_price) {
                                $originalPrice = $product->pos_price;
                                $priceDisplay = 'Rp ' . number_format($originalPrice, 0, ',', '.');
                            } elseif ($hasVariants) {
                                $minPrice = $product->variants->min('price');
                                $maxPrice = $product->variants->max('price');
                                if ($minPrice != $maxPrice) {
                                    $priceDisplay = 'Rp ' . number_format($minPrice, 0, ',', '.') . ' - Rp ' . number_format($maxPrice, 0, ',', '.');
                                } else {
                                    $priceDisplay = 'Rp ' . number_format($minPrice, 0, ',', '.');
                                }
                                $originalPrice = $minPrice;
                            } else {
                                if ($product->discount_price) {
                                    $hasPromo = true;
                                    $promoPrice = $product->discount_price;
                                    $originalPrice = $product->price;
                                    $priceDisplay = 'Rp ' . number_format($promoPrice, 0, ',', '.');
                                } else {
                                    $originalPrice = $product->price;
                                    $priceDisplay = 'Rp ' . number_format($originalPrice, 0, ',', '.');
                                }
                            }
                            $priceForJs = $hasPromo ? $promoPrice : $originalPrice;
                            
                            $imageUrl = asset('assets/images/placeholder.webp');
                            if (!empty($product->images)) {
                                if (is_numeric($product->images[0])) {
                                    $media = \Awcodes\Curator\Models\Media::find($product->images[0]);
                                    if ($media) $imageUrl = Storage::url($media->path);
                                } else {
                                    if (Storage::disk('public')->exists($product->images[0])) {
                                        $imageUrl = asset('storage/' . $product->images[0]);
                                    } else {
                                        $imageUrl = asset($product->images[0]);
                                    }
                                }
                            }
                            $image = $imageUrl;
                        @endphp
                        
                        <div class="glass bg-white {{ $isOutOfStock ? 'opacity-50 cursor-not-allowed grayscale-[30%]' : 'hover:shadow-2xl hover:-translate-y-1 cursor-pointer group' }} transition-all duration-300 rounded-2xl overflow-hidden border border-gray-100 relative"
                             @if(!$isOutOfStock)
                             x-data="{ variantsData: {{ $hasVariants ? \Illuminate\Support\Js::from($product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $product->pos_discount_price ?: ($product->pos_price ?: ($v->price ?: $product->price)), 'stock' => $v->stock])) : 'null' }} }"
                             @click="addProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $priceForJs }}, {{ $hasVariants ? 'true' : 'false' }}, variantsData)"
                             @endif
                             >
                             
                             @if($isOutOfStock)
                                <div class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg transform -rotate-12">HABIS</span>
                                </div>
                             @endif

                            <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                <img src="{{ $image }}" alt="{{ $product->name }}" class="object-cover w-full h-full {{ !$isOutOfStock ? 'group-hover:scale-105' : '' }} transition-transform duration-500">
                                @if(isset($product->is_best_seller) && $product->is_best_seller)
                                    <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-[10px] px-2 py-1 rounded-md font-bold shadow-sm flex items-center gap-1 z-10">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        Terlaris
                                    </span>
                                @endif
                                @if($hasVariants)
                                    <span class="absolute top-2 right-2 bg-gray-900/80 backdrop-blur text-white text-[10px] px-2 py-1 rounded-md font-medium z-10">{{ $product->variants->count() }} Varian</span>
                                @endif
                                <span class="absolute bottom-2 left-2 {{ $isOutOfStock ? 'bg-red-500' : 'bg-brand-500' }} text-white text-[10px] px-2 py-1 rounded-md font-medium shadow-sm z-10">Stok: {{ $computedStock }}</span>
                            </div>
                            <div class="p-3">
                                <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 leading-tight {{ !$isOutOfStock ? 'group-hover:text-brand-600' : '' }} transition-colors">{{ $product->name }}</h3>
                                <div class="mt-2 flex flex-col">
                                    @if($hasPromo)
                                        <span class="text-[10px] text-gray-400 line-through">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="text-brand-600 font-bold leading-tight">{{ $priceDisplay }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <p class="text-lg">Tidak ada produk ditemukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Cart Sidebar -->
        <div x-show="activePage === 'kasir'" class="w-full md:w-[400px] lg:w-[450px] bg-white border-l border-gray-200/50 shadow-2xl flex flex-col relative z-20">
            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 transition-transform duration-300" :class="cartBouncing ? 'scale-110 text-brand-600' : ''">
                    <svg class="w-5 h-5 text-brand-500 transition-transform" :class="cartBouncing ? 'animate-bounce' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Keranjang
                </h2>
                <div class="flex items-center gap-1">
                    <button @click="showHoldModal = true" class="relative text-brand-600 hover:bg-brand-50 px-3 py-2 rounded-lg transition-colors flex items-center gap-1" title="Lihat Antrean">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-bold hidden md:inline">Antrean</span>
                        <span x-show="heldCarts.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full shadow-sm" x-text="heldCarts.length"></span>
                    </button>
                    <button @click="clearCart()" x-show="cart.length > 0" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors" title="Kosongkan Keranjang">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-2 space-y-2 bg-gray-50/30">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <p class="font-medium text-gray-500">Keranjang masih kosong</p>
                        <p class="text-sm mt-1">Pilih produk di samping untuk memulai transaksi.</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-2 relative group hover:border-brand-200 transition-all"
                         x-transition:enter="transition ease-out duration-300 transform"
                         x-transition:enter-start="opacity-0 -translate-x-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-x-0 scale-100">
                        <div class="flex justify-between items-start pr-6">
                            <h4 class="font-semibold text-sm text-gray-800 leading-tight" x-text="item.name"></h4>
                            <div class="font-bold text-brand-600 text-sm" x-text="'Rp ' + formatMoney(item.price * item.quantity)"></div>
                        </div>
                        
                        <div class="flex justify-between items-center mt-1">
                            <div class="text-xs text-gray-500" x-text="'Rp ' + formatMoney(item.price) + ' / item'"></div>
                            <!-- Qty Controls -->
                            <div class="flex items-center bg-gray-100 rounded-lg p-1">
                                <button @click="updateQty(index, -1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-brand-600 active:scale-95 transition-all">-</button>
                                <input type="number" x-model.number="item.quantity" class="w-10 text-center bg-transparent border-none focus:ring-0 text-sm font-semibold p-0 mx-1" min="1">
                                <button @click="updateQty(index, 1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 hover:text-brand-600 active:scale-95 transition-all">+</button>
                            </div>
                        </div>

                        <!-- Hapus Item -->
                        <button @click="removeItem(index)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 bg-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Cart Footer (Totals & Checkout) -->
            <div class="bg-white border-t border-gray-100 p-4 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-semibold text-gray-700" x-text="'Rp ' + formatMoney(subtotal)"></span>
                </div>
                <!-- Voucher Selector Button (Compact) -->
                <div class="flex justify-between items-center mb-2.5 cursor-pointer group" @click="showVoucherModal = true">
                    <span class="flex items-center gap-2 text-brand-600 border-b border-dashed border-brand-300 group-hover:border-brand-500 transition-colors pb-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        <span class="font-semibold text-sm" x-text="activeVoucher ? activeVoucher.name : 'Gunakan Kupon Promo'"></span>
                    </span>
                    <div class="flex items-center gap-2">
                        <span x-show="activeVoucher" class="font-bold text-red-500 text-sm" x-text="'- Rp ' + formatMoney(voucherDiscountAmount)"></span>
                        <span x-show="!activeVoucher" class="text-xs font-semibold text-brand-600 group-hover:text-brand-700">Pilih ></span>
                        <!-- Tombol lepas voucher -->
                        <button x-show="activeVoucher" @click.stop="removeVoucher()" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50" title="Lepas Promo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Diskon Manual Row -->
                <div class="flex justify-between items-center mb-4">
                    <span class="flex items-center gap-2 text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10m-8 5h8"/></svg>
                        <span class="font-semibold text-sm">Diskon Manual</span>
                    </span>
                    <div class="flex items-center gap-2">
                        <template x-if="manualDiscountValue > 0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-amber-600 text-sm" x-text="'- Rp ' + formatMoney(manualDiscountAmount)"></span>
                                <button @click="openManualDiscountModal()" class="text-gray-400 hover:text-amber-600 transition-colors p-1 rounded-full hover:bg-amber-50" title="Edit Diskon Manual">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button @click="removeManualDiscount()" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50" title="Hapus Diskon Manual">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="!manualDiscountValue || manualDiscountValue <= 0">
                            <button @click="openManualDiscountModal()" class="text-xs font-bold text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1">
                                <span>+ Potongan</span>
                            </button>
                        </template>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-6 pt-4 border-t border-gray-100">
                    <span class="text-lg font-bold text-gray-800">Total</span>
                    <span class="text-2xl font-black text-brand-600 transition-all duration-300 inline-block" :class="cartBouncing ? 'scale-110 text-brand-500' : ''" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                </div>
                
                <div class="flex gap-2">
                    <button 
                        @click="holdCart()"
                        :disabled="cart.length === 0"
                        class="px-4 py-4 rounded-xl font-bold text-gray-700 shadow-sm transition-all active:scale-95 flex items-center justify-center border border-gray-200"
                        :class="cart.length > 0 ? 'bg-white hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed border-transparent'"
                        title="Masukkan ke Antrean">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </button>
                    
                    <button 
                        @click="openCheckoutModal()"
                        :disabled="cart.length === 0"
                        class="flex-1 py-4 rounded-xl font-bold text-lg shadow-xl transition-all active:scale-95 flex justify-center items-center gap-2"
                        :class="cart.length > 0 ? 'bg-brand-600 hover:bg-brand-700 text-white shadow-brand-500/30' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        PROSES PEMBAYARAN
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Pilih Varian Produk -->
        <div x-show="showVariantModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showVariantModal = false"
                 x-show="showVariantModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-lg rounded-2xl p-6 relative shadow-2xl">
                 <h3 class="text-xl font-bold mb-1" x-text="currentProductForVariant ? currentProductForVariant.name : 'Pilih Varian'"></h3>
                 <p class="text-gray-500 mb-4 text-sm">Pilih salah satu varian di bawah ini:</p>
                 
                 <div class="space-y-2 mb-6 max-h-[50vh] overflow-y-auto">
                     <template x-for="variant in currentVariants" :key="variant.id">
                         <button @click="addVariantToCart(variant.id, variant.name, variant.price)" 
                                 :disabled="variant.stock <= 0"
                                 class="w-full flex justify-between items-center p-4 border rounded-xl hover:bg-brand-50 hover:border-brand-300 transition-colors text-left"
                                 :class="variant.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'bg-white'">
                             <div>
                                 <div class="font-bold text-gray-800" x-text="variant.name"></div>
                                 <div class="text-sm" :class="variant.stock > 0 ? 'text-brand-600' : 'text-red-500'" x-text="variant.stock > 0 ? 'Stok: ' + variant.stock : 'Habis'"></div>
                             </div>
                             <div class="font-black text-gray-800" x-text="'Rp ' + formatMoney(variant.price)"></div>
                         </button>
                     </template>
                 </div>

                 <button @click="showVariantModal = false" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-xl transition-all">Batal</button>
            </div>
        </div>

        <!-- MODAL: Checkout / Payment -->
        <div x-show="showCheckoutModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-3 md:p-4" style="display: none;">
            
            <div @click.away="if(!isProcessing) showCheckoutModal = false" 
                 x-show="showCheckoutModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="bg-slate-100 w-full max-w-3xl rounded-2xl p-6 relative max-h-[90vh] flex flex-col shadow-2xl border border-gray-200/50">
                
                <!-- Header Modal -->
                <div class="flex justify-between items-center mb-5 flex-shrink-0">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Pembayaran Transaksi
                    </h3>
                    <button @click="showCheckoutModal = false" :disabled="isProcessing" class="bg-white hover:bg-gray-200 text-gray-400 hover:text-gray-700 p-2 rounded-full shadow-sm transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body 2 Kolom: Scrollable -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 overflow-y-auto flex-1 pr-1">
                    
                    <!-- Kolom Kiri: Rincian Barang (Col 5) -->
                    <div class="md:col-span-5 space-y-4 flex flex-col justify-between">
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm mb-3">Rincian Barang</h4>
                            <div class="max-h-56 overflow-y-auto space-y-2.5 pr-1">
                                <template x-for="(item, idx) in cart" :key="idx">
                                    <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-200/80 space-y-1">
                                        <div class="flex justify-between items-start text-sm font-bold text-gray-800">
                                            <span x-text="item.name" class="pr-2 leading-snug"></span>
                                            <span class="whitespace-nowrap" x-text="'Rp ' + formatMoney(item.price * item.quantity)"></span>
                                        </div>
                                        <div class="text-xs text-gray-400 font-medium" x-text="'Rp ' + formatMoney(item.price) + ' × ' + item.quantity"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Card Total Tagihan -->
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 space-y-2 mt-auto">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Subtotal</span>
                                <span class="font-bold text-gray-800" x-text="'Rp ' + formatMoney(subtotal)"></span>
                            </div>
                            <div x-show="activeVoucher" class="flex justify-between text-sm text-brand-600">
                                <span>Diskon Promo</span>
                                <span class="font-bold" x-text="'- Rp ' + formatMoney(voucherDiscountAmount)"></span>
                            </div>
                            <div x-show="manualDiscountValue > 0" class="flex justify-between text-sm text-amber-600">
                                <span>Diskon Manual</span>
                                <span class="font-bold" x-text="'- Rp ' + formatMoney(manualDiscountAmount)"></span>
                            </div>
                            <div class="border-t border-gray-100 pt-2 flex justify-between items-center">
                                <span class="font-bold text-gray-800 text-sm">Total Tagihan</span>
                                <span class="text-2xl font-black text-brand-600" x-text="'Rp ' + formatMoney(grandTotal)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Metode Bayar & Nominal (Col 7) -->
                    <div class="md:col-span-7 space-y-4">
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm mb-3">Metode Pembayaran</h4>
                            
                            <!-- Dynamic Payment Methods Grid from DB (2 Kolom Lapang) -->
                            <div class="grid grid-cols-2 gap-3 max-h-44 overflow-y-auto pr-1">
                                <template x-for="method in paymentMethods" :key="method.code">
                                    <button type="button"
                                            @click="selectPaymentMethod(method)"
                                            class="h-12 px-4 rounded-xl text-xs font-bold flex items-center justify-between transition-all shadow-sm cursor-pointer"
                                            :class="paymentMethod === method.code ? 'border-2 border-brand-500 bg-brand-50/70 text-brand-800 shadow-sm' : 'border border-gray-200 bg-white text-gray-800 hover:border-gray-300 hover:bg-gray-50'">
                                        <div class="flex items-center gap-2 truncate">
                                            <template x-if="method.logo">
                                                <img :src="method.logo" :alt="method.name" class="w-4 h-4 object-contain flex-shrink-0">
                                            </template>
                                            <span class="truncate" x-text="method.name"></span>
                                        </div>
                                        <span x-show="paymentMethod === method.code" class="w-2.5 h-2.5 rounded-full bg-brand-500 flex-shrink-0"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Nominal Uang (Jika Tunai) -->
                        <div x-show="isCashSelected()" x-transition.opacity class="space-y-3">
                            <div>
                                <label class="block text-sm font-bold text-gray-800 mb-2">Uang Diterima (Rp)</label>
                                <div class="h-14 bg-white rounded-xl border border-gray-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 shadow-sm flex items-center px-4 transition-all">
                                    <span class="font-bold text-gray-400 text-base mr-3 select-none">Rp</span>
                                    <input type="number"
                                           x-model.number="cashPaid"
                                           @input="calculateChange"
                                           class="w-full text-2xl font-bold text-gray-900 border-none focus:ring-0 p-0 bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                           placeholder="0">
                                </div>
                            </div>

                            <!-- Preset Nominal Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="setCashPaid(grandTotal)" class="h-11 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">Uang Pas</button>
                                <button type="button" @click="setCashPaid(Math.ceil(grandTotal/50000)*50000)" class="h-11 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" x-text="'Rp ' + formatMoney(Math.ceil(grandTotal/50000)*50000)"></button>
                                <button type="button" @click="setCashPaid(Math.ceil(grandTotal/100000)*100000)" class="h-11 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-700 shadow-sm hover:bg-gray-50 transition-colors" x-text="'Rp ' + formatMoney(Math.ceil(grandTotal/100000)*100000)"></button>
                            </div>

                            <!-- Status Kembalian / Uang Kurang -->
                            <div class="p-3.5 rounded-xl flex items-center justify-between text-sm font-bold border shadow-sm"
                                 :class="cashPaid < grandTotal ? 'bg-red-50 border-red-200 text-red-700' : 'bg-brand-50/80 border-brand-200 text-brand-800'">
                                <span x-text="cashPaid < grandTotal ? 'UANG KURANG' : 'KEMBALIAN'"></span>
                                <span class="text-base" x-text="'Rp ' + formatMoney(Math.abs(cashChange))"></span>
                            </div>
                        </div>

                        <!-- Catatan Pembayaran Non-Tunai -->
                        <div x-show="!isCashSelected()" x-transition.opacity class="p-4 bg-white rounded-xl border border-gray-200 text-xs text-gray-600 leading-relaxed shadow-sm">
                            Metode pembayaran non-tunai (<strong x-text="paymentMethods.find(m => m.code === paymentMethod)?.name || paymentMethod"></strong>) dipilih. Transaksi akan dicatat otomatis di laporan kasir.
                        </div>

                        <!-- Identitas Pembeli (Opsional) -->
                        <div class="pt-2 border-t border-gray-200/80">
                            <label class="block text-xs font-bold text-gray-700 mb-2">Data Pelanggan (Opsional)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" x-model="customerName" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-xs py-2.5 px-3 bg-white shadow-sm" placeholder="Nama Pembeli">
                                <input type="text" x-model="customerPhone" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-xs py-2.5 px-3 bg-white shadow-sm" placeholder="No WhatsApp">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons (Equal 2 Columns) -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-200/80 mt-4 flex-shrink-0">
                    <button type="button" @click="showCheckoutModal = false" :disabled="isProcessing" class="h-12 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold rounded-xl transition-colors text-sm flex items-center justify-center">
                        Batal
                    </button>
                    <button type="button" @click="submitOrder()" 
                            :disabled="isProcessing || (isCashSelected() && cashPaid < grandTotal)" 
                            class="h-12 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all text-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing" class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Selesaikan Pembayaran
                        </span>
                        <span x-show="isProcessing" class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL: Tutup Shift -->
        <div x-show="showCloseSession"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showCloseSession = false"
                 x-show="showCloseSession"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-8 relative shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Tutup Shift Kasir</h2>
                <p class="text-gray-500 text-sm mb-6">Hitung uang fisik di laci kasir dan masukkan di bawah ini untuk mencocokkan dengan sistem.</p>
                
                @if($activeSession)
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 font-medium">Modal Awal:</span>
                        <span class="font-bold text-gray-700">Rp {{ number_format($activeSession->opening_cash, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $sales = $activeSession->orders()->sum('cash_paid') - $activeSession->orders()->sum('cash_change');
                        $expected = $activeSession->opening_cash + $sales;
                    @endphp
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 font-medium">Penjualan Tunai:</span>
                        <span class="font-bold text-green-600">+ Rp {{ number_format($sales, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-dashed border-gray-300 pt-2 mt-2 flex justify-between">
                        <span class="text-sm font-bold text-gray-700">Estimasi Sistem:</span>
                        <span class="font-black text-brand-600 text-lg">Rp {{ number_format($expected, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                <form wire:submit.prevent="closeSession" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Total Uang Fisik Aktual (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                            <input type="number" wire:model="actualEndingCash" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-xl font-bold py-3 pl-12 pr-4 text-gray-900" placeholder="0" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea wire:model="sessionNotes" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm p-3" rows="2" placeholder="Misal: Ada pengeluaran beli lakban Rp 10.000"></textarea>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showCloseSession = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-red-500/30 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Akhiri Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Hold Carts -->
        <div x-show="showHoldModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showHoldModal = false"
                 x-show="showHoldModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-2xl rounded-2xl p-6 relative max-h-[90vh] flex flex-col shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Daftar Antrean Pesanan
                    </h3>
                    <button @click="showHoldModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="overflow-y-auto flex-1 space-y-3">
                    <template x-if="heldCarts.length === 0">
                        <div class="text-center py-10 text-gray-400">
                            <p>Tidak ada antrean pesanan.</p>
                        </div>
                    </template>
                    
                    <template x-for="hold in heldCarts" :key="hold.id">
                        <div class="bg-white border border-gray-100 shadow-sm p-4 rounded-xl flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold text-gray-800" x-text="hold.name"></div>
                                <div class="text-sm text-gray-500" x-text="hold.cart.length + ' item | Antre sejak ' + hold.time"></div>
                                <div class="font-semibold text-brand-600 mt-1" x-text="'Rp ' + formatMoney(hold.total)"></div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="deleteHeldCart(hold.id)" class="px-4 py-2 bg-red-50 text-red-600 font-semibold rounded-lg hover:bg-red-100 transition-colors">Hapus</button>
                                <button @click="resumeCart(hold.id)" class="px-4 py-2 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">Lanjutkan</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL: Konfirmasi -->
        <div x-show="showConfirmModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showConfirmModal = false"
                 x-show="showConfirmModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-sm rounded-2xl p-6 relative shadow-2xl text-center">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="confirmTitle"></h3>
                <p class="text-gray-500 text-sm mb-6" x-text="confirmMessage"></p>
                
                <div class="flex gap-3">
                    <button @click="showConfirmModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="executeConfirm()" class="flex-1 px-4 py-3 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
        <!-- MODAL: Input Custom -->
        <div x-show="showInputModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showInputModal = false"
                 x-show="showInputModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-sm rounded-2xl p-6 relative shadow-2xl">
                <h3 class="text-xl font-bold text-gray-800 mb-2" x-text="inputTitle"></h3>
                <p class="text-gray-500 text-sm mb-4" x-text="inputMessage"></p>
                
                <input type="text" id="alpineInputModalField" x-model="inputValue" @keydown.enter="executeInput()" :placeholder="inputPlaceholder" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm py-3 px-4 mb-6">
                
                <div class="flex gap-3">
                    <button @click="showInputModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                    <button @click="executeInput()" class="flex-1 px-4 py-3 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-500/30">Simpan</button>
                </div>
            </div>
        </div>

        <!-- MODAL: Diskon Manual -->
        <div x-show="showManualDiscountModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display: none;">
            <div @click.away="showManualDiscountModal = false"
                 x-show="showManualDiscountModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-6 relative shadow-2xl">
                
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Diskon Manual
                    </h3>
                    <button @click="showManualDiscountModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-gray-500 text-sm mb-5">Berikan potongan harga langsung (Nominal Rp atau Persen %):</p>

                <!-- Toggle Type: Rp vs % -->
                <div class="grid grid-cols-2 gap-2 p-1.5 bg-gray-100 rounded-xl mb-4">
                    <button type="button" @click="tempManualDiscountType = 'rp'"
                            :class="tempManualDiscountType === 'rp' ? 'bg-white text-gray-900 shadow-sm font-bold' : 'text-gray-500 font-medium hover:text-gray-700'"
                            class="py-2.5 rounded-lg text-sm transition-all flex items-center justify-center gap-1.5">
                        <span>Nominal (Rp)</span>
                    </button>
                    <button type="button" @click="tempManualDiscountType = 'percent'"
                            :class="tempManualDiscountType === 'percent' ? 'bg-white text-gray-900 shadow-sm font-bold' : 'text-gray-500 font-medium hover:text-gray-700'"
                            class="py-2.5 rounded-lg text-sm transition-all flex items-center justify-center gap-1.5">
                        <span>Persentase (%)</span>
                    </button>
                </div>

                <!-- Input Nominal/Persen -->
                <div class="relative mb-4">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-lg text-gray-400" x-text="tempManualDiscountType === 'rp' ? 'Rp' : '%'"></span>
                    <input type="number" id="alpineManualDiscountField" x-model="tempManualDiscountValue" @keydown.enter="applyManualDiscount()"
                           :placeholder="tempManualDiscountType === 'rp' ? 'Contoh: 10000' : 'Contoh: 10'"
                           class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-xl font-bold py-3.5 pl-12 pr-4 text-gray-900 shadow-sm">
                </div>

                <!-- Quick Presets -->
                <div class="mb-6">
                    <div class="text-xs font-semibold text-gray-400 mb-2">Preset Cepat:</div>
                    <div class="grid grid-cols-5 gap-1.5" x-show="tempManualDiscountType === 'rp'">
                        <button type="button" @click="tempManualDiscountValue = 5000" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">5k</button>
                        <button type="button" @click="tempManualDiscountValue = 10000" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">10k</button>
                        <button type="button" @click="tempManualDiscountValue = 20000" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">20k</button>
                        <button type="button" @click="tempManualDiscountValue = 50000" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">50k</button>
                        <button type="button" @click="tempManualDiscountValue = 100000" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">100k</button>
                    </div>
                    <div class="grid grid-cols-5 gap-1.5" x-show="tempManualDiscountType === 'percent'">
                        <button type="button" @click="tempManualDiscountValue = 5" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">5%</button>
                        <button type="button" @click="tempManualDiscountValue = 10" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">10%</button>
                        <button type="button" @click="tempManualDiscountValue = 15" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">15%</button>
                        <button type="button" @click="tempManualDiscountValue = 20" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">20%</button>
                        <button type="button" @click="tempManualDiscountValue = 25" class="py-2 bg-gray-50 hover:bg-amber-50 hover:border-amber-300 border border-gray-200 text-gray-700 rounded-lg text-xs font-bold transition-all">25%</button>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showManualDiscountModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="button" @click="applyManualDiscount()" class="flex-1 px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-lg shadow-amber-500/25 transition-all active:scale-95">Terapkan Diskon</button>
                </div>
            </div>
        </div>

        <!-- OVERLAY: Mandatory Initial POS PIN Setup -->
        <div x-show="!$wire.hasPosPin"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed inset-0 z-[150] flex items-center justify-center bg-gray-950/90 backdrop-blur-xl p-4">
            <div class="glass w-full max-w-md rounded-3xl p-8 shadow-2xl relative border border-gray-700/50 text-center">
                <div class="w-16 h-16 bg-amber-500/20 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-amber-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h2 class="text-2xl font-black text-white mb-2">Buat PIN POS 6-Digit</h2>
                <p class="text-xs text-gray-400 mb-6 leading-relaxed">Untuk keamanan transaksi kasir, Anda wajib membuat PIN POS 6-digit pertama Anda sebelum menggunakan aplikasi POS.</p>

                <form wire:submit.prevent="saveInitialPosPin" class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">PIN POS Baru (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="posPinInput" placeholder="Contoh: 123456"
                               class="w-full rounded-xl border-gray-700 bg-gray-900/80 text-white placeholder-gray-600 text-center text-xl font-bold tracking-[0.5em] py-3 px-4 shadow-inner focus:border-amber-500 focus:ring-amber-500">
                        @error('posPinInput') <span class="text-red-400 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Konfirmasi PIN POS (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="posPinConfirm" placeholder="Ulangi 6 digit PIN"
                               class="w-full rounded-xl border-gray-700 bg-gray-900/80 text-white placeholder-gray-600 text-center text-xl font-bold tracking-[0.5em] py-3 px-4 shadow-inner focus:border-amber-500 focus:ring-amber-500">
                        @error('posPinConfirm') <span class="text-red-400 text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit"
                            class="w-full py-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-2xl shadow-lg shadow-amber-500/30 transition-all active:scale-95 text-sm mt-2">
                        Simpan & Aktifkan PIN POS
                    </button>
                </form>
            </div>
        </div>

        <!-- OVERLAY: Lock Screen Kasir (PIN 6-Digit) -->
        <div x-show="isLocked"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/95 backdrop-blur-xl p-4" style="display: none;">
            <div class="glass w-full max-w-sm rounded-3xl p-8 text-center shadow-2xl relative border border-gray-700/50">
                <div class="w-20 h-20 bg-brand-500 rounded-full flex items-center justify-center text-white text-2xl font-black mx-auto mb-4 shadow-xl shadow-brand-500/30 border-2 border-white/20">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <h2 class="text-2xl font-bold text-white mb-1">{{ auth()->user()->name }}</h2>
                <p class="text-xs text-gray-400 mb-6">Masukkan 6-digit PIN POS Anda untuk membuka kunci layar.</p>

                <form @submit.prevent="submitUnlock()" class="space-y-4">
                    <div class="relative">
                        <input type="password" id="posLockPasswordField" x-model="lockPasswordInput" maxlength="6" pattern="[0-9]*" inputmode="numeric" placeholder="6 Digit PIN"
                               class="w-full rounded-2xl border-gray-700 bg-gray-900/90 text-white placeholder-gray-600 focus:border-brand-500 focus:ring-brand-500 text-center text-2xl font-bold tracking-[0.5em] py-3.5 px-4 shadow-inner">
                    </div>
                    
                    <div x-show="lockErrorMessage" class="text-xs text-red-400 font-semibold" x-text="lockErrorMessage"></div>

                    <!-- Visual Numpad Cepat -->
                    <div class="grid grid-cols-3 gap-2 pt-2">
                        <template x-for="num in [1,2,3,4,5,6,7,8,9]" :key="num">
                            <button type="button" @click="appendLockPin(num)"
                                    class="py-3 bg-gray-800/80 hover:bg-gray-700 text-white font-bold text-lg rounded-xl transition-all border border-gray-700/40 active:scale-95" x-text="num"></button>
                        </template>
                        <button type="button" @click="clearLockPin()" class="py-3 bg-red-900/30 hover:bg-red-800/50 text-red-400 font-bold text-xs rounded-xl transition-all border border-red-800/40">Hapus</button>
                        <button type="button" @click="appendLockPin(0)" class="py-3 bg-gray-800/80 hover:bg-gray-700 text-white font-bold text-lg rounded-xl transition-all border border-gray-700/40 active:scale-95">0</button>
                        <button type="submit" :disabled="!lockPasswordInput || String(lockPasswordInput).trim().length !== 6" class="py-3 bg-emerald-600 disabled:opacity-40 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition-all border border-emerald-500/40 cursor-pointer">Buka</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Otorisasi PIN Supervisor -->
        <div x-show="showSupervisorPinModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[120] flex items-center justify-center bg-gray-950/80 backdrop-blur-md p-4" style="display: none;">
            <div @click.away="showSupervisorPinModal = false"
                 x-show="showSupervisorPinModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-6 relative shadow-2xl text-center border border-amber-500/30">
                
                <div class="w-14 h-14 bg-amber-500/20 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-amber-500/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>

                <h3 class="text-xl font-bold text-gray-800 mb-1">Otorisasi Supervisor</h3>
                <p class="text-xs text-gray-500 mb-4" x-text="supervisorReasonMessage || 'Tindakan ini memerlukan verifikasi PIN Supervisor/Manager.'"></p>

                <form @submit.prevent="submitSupervisorAuth()" class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">1. Pilih Supervisor yang Bertugas</label>
                        <select x-model="selectedSupervisorId" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-bold py-3 px-4 shadow-sm bg-white mb-3">
                            <option value="">-- Pilih Supervisor / Manager --</option>
                            <template x-for="sup in supervisors" :key="sup.id">
                                <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">2. Masukkan 6-Digit PIN Supervisor</label>
                        <input type="password" id="posSupervisorPinField" x-model="supervisorPinInput" maxlength="6" pattern="[0-9]*" inputmode="numeric" placeholder="6 Digit PIN"
                               class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-center text-xl font-bold tracking-[0.5em] py-3 px-4 shadow-sm">
                        <div x-show="supervisorErrorMessage" class="text-xs text-red-500 font-semibold mt-1" x-text="supervisorErrorMessage"></div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showSupervisorPinModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="!selectedSupervisorId || !supervisorPinInput || String(supervisorPinInput).trim().length !== 6"
                                class="flex-1 px-4 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-bold rounded-xl shadow-lg shadow-amber-500/25 transition-all active:scale-95">Verifikasi PIN</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Void Transaksi POS -->
        <div x-show="showVoidModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[110] flex items-center justify-center bg-gray-950/80 backdrop-blur-md p-4" style="display: none;">
            <div @click.away="showVoidModal = false"
                 x-show="showVoidModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-6 relative shadow-2xl border border-red-500/30">
                
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Batalkan Transaksi (Void)
                    </h3>
                    <button type="button" @click="showVoidModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-red-50 rounded-xl p-4 mb-4 border border-red-100">
                    <div class="text-xs font-semibold text-red-500 uppercase tracking-wider mb-1">Nota yang akan dibatalkan</div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-gray-800 text-lg" x-text="'#' + voidOrderNumber"></span>
                        <span class="font-black text-red-600 text-lg" x-text="'Rp ' + formatMoney(voidOrderTotal)"></span>
                    </div>
                </div>

                <form @submit.prevent="submitVoidOrder()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">1. Pilih Supervisor Pengizin Void</label>
                        <select x-model="voidSupervisorIdInput" class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm font-bold py-3 px-4 shadow-sm bg-white mb-3">
                            <option value="">-- Pilih Supervisor / Manager --</option>
                            <template x-for="sup in supervisors" :key="sup.id">
                                <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">2. Alasan Pembatalan / Void</label>
                        <input type="text" x-model="voidReasonInput" placeholder="Contoh: Salah input barang / Batal beli"
                               class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm py-3 px-4 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">3. PIN Supervisor (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" x-model="voidSupervisorPinInput" placeholder="Masukkan 6-digit PIN Supervisor"
                               class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-center text-xl font-bold tracking-[0.5em] py-3 px-4 shadow-sm">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showVoidModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="!voidSupervisorIdInput || !voidSupervisorPinInput || String(voidSupervisorPinInput).trim().length !== 6"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-40 text-white font-bold rounded-xl shadow-lg shadow-red-500/25 transition-all active:scale-95">Batalkan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Retur & Penukaran Ukuran Barang POS -->
        <div x-show="showReturnModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[115] flex items-center justify-center bg-gray-950/80 backdrop-blur-md p-4" style="display: none;">
            <div @click.away="showReturnModal = false"
                 x-show="showReturnModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl p-6 relative shadow-2xl border border-amber-500/30">
                
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Retur & Penukaran Ukuran Barang
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="'Nota Asli: #' + returnOrderNumber"></p>
                    </div>
                    <button type="button" @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitReturnProcess()" class="space-y-5">
                    <!-- Step 1: Pilih Barang Di-retur -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">1. Pilih Barang & Jumlah yang Dikembalikan</label>
                        <div class="space-y-2 bg-gray-50/80 rounded-xl p-3 border border-gray-200">
                            <template x-for="(item, idx) in returnOrderItems" :key="item.id">
                                <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex-1 pr-3">
                                        <div class="font-bold text-sm text-gray-800" x-text="item.name"></div>
                                        <div class="text-xs text-gray-500" x-text="'Rp ' + formatMoney(item.price) + ' · Beli: ' + item.quantity + ' unit'"></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-semibold text-gray-500">Jumlah Retur:</label>
                                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-gray-50">
                                            <button type="button" @click="item.return_qty = Math.max(0, (item.return_qty || 0) - 1)" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 font-bold text-gray-600">-</button>
                                            <input type="number" x-model.number="item.return_qty" min="0" :max="item.quantity" class="w-12 text-center text-xs font-bold border-none bg-transparent p-1 focus:ring-0">
                                            <button type="button" @click="item.return_qty = Math.min(item.quantity, (item.return_qty || 0) + 1)" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 font-bold text-gray-600">+</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Step 2: Pilih Tipe Aksi -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">2. Jenis Transaksi Retur</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="returnType === 'exchange' ? 'border-amber-500 bg-amber-50/50 text-amber-900 font-bold' : 'border-gray-200 bg-white text-gray-600'">
                                <input type="radio" x-model="returnType" value="exchange" class="text-amber-600 focus:ring-amber-500">
                                <div>
                                    <div class="text-sm">Tukar Ukuran / Barang</div>
                                    <div class="text-[11px] font-normal text-gray-500">Tukar ke varian/produk pengganti</div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="returnType === 'refund' ? 'border-red-500 bg-red-50/50 text-red-900 font-bold' : 'border-gray-200 bg-white text-gray-600'">
                                <input type="radio" x-model="returnType" value="refund" class="text-red-600 focus:ring-red-500">
                                <div>
                                    <div class="text-sm">Pengembalian Uang (Refund)</div>
                                    <div class="text-[11px] font-normal text-gray-500">Kembalikan kas ke pelanggan</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Summary Calculation Box -->
                    <div class="bg-amber-50/60 rounded-xl p-4 border border-amber-200 space-y-2">
                        <div class="flex justify-between text-xs font-medium text-gray-600">
                            <span>Subtotal Barang Retur:</span>
                            <span class="font-bold text-gray-800" x-text="'- Rp ' + formatMoney(returnSubtotal)"></span>
                        </div>
                        <template x-if="returnType === 'exchange'">
                            <div class="flex justify-between text-xs font-medium text-gray-600">
                                <span>Subtotal Barang Tukar:</span>
                                <span class="font-bold text-gray-800" x-text="'+ Rp ' + formatMoney(exchangeSubtotal)"></span>
                            </div>
                        </template>
                        <div class="pt-2 border-t border-amber-200/80 flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Status Selisih:</span>
                            <template x-if="returnNetAmount > 0">
                                <span class="text-sm font-black text-green-700" x-text="'Pelanggan Tambah Bayar: Rp ' + formatMoney(returnNetAmount)"></span>
                            </template>
                            <template x-if="returnNetAmount < 0">
                                <span class="text-sm font-black text-red-700" x-text="'Pengembalian Uang Kas: Rp ' + formatMoney(Math.abs(returnNetAmount))"></span>
                            </template>
                            <template x-if="returnNetAmount === 0">
                                <span class="text-sm font-black text-gray-700">Pas (Selisih Rp 0)</span>
                            </template>
                        </div>
                    </div>

                    <!-- Reason Field -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alasan Retur / Tukar Barang</label>
                        <input type="text" x-model="returnReasonInput" placeholder="Contoh: Ukuran kekecilan / Tukar warna / Cacat jahitan"
                               class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm py-2.5 px-4 shadow-sm">
                    </div>

                    <!-- Supervisor Otorisasi (If refund or netAmount < 0) -->
                    <template x-if="returnType === 'refund' || returnNetAmount < 0">
                        <div class="p-4 bg-red-50/80 rounded-xl border border-red-200 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-red-700 uppercase tracking-wider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Otorisasi PIN Supervisor Wajib (Pengembalian Uang Kas)
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Supervisor Pengizin</label>
                                    <select x-model="returnSupervisorIdInput" class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-xs font-bold py-2.5 px-3 shadow-sm bg-white">
                                        <option value="">-- Pilih Supervisor --</option>
                                        <template x-for="sup in supervisors" :key="sup.id">
                                            <option :value="sup.id" x-text="sup.name + ' (' + sup.role + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">PIN 6-Digit</label>
                                    <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" x-model="returnSupervisorPinInput" placeholder="6 Digit PIN"
                                           class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 text-center text-sm font-bold tracking-[0.3em] py-2.5 px-3 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showReturnModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" :disabled="returnSubtotal <= 0"
                                class="flex-1 px-4 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-bold rounded-xl shadow-lg shadow-amber-500/25 transition-all active:scale-95">Proses Retur / Tukar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Ganti PIN POS 6-Digit -->
        <div x-show="showChangePinModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display: none;">
            <div @click.away="showChangePinModal = false"
                 x-show="showChangePinModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-6 relative shadow-2xl">
                
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Ganti PIN POS 6-Digit
                    </h3>
                    <button type="button" @click="showChangePinModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="changePosPin" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">PIN Lama (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="oldPosPin" placeholder="PIN Lama" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-center text-lg font-bold tracking-[0.4em] py-3 px-4 shadow-sm">
                        @error('oldPosPin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">PIN Baru (6 Digit)</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="newPosPin" placeholder="PIN Baru" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-center text-lg font-bold tracking-[0.4em] py-3 px-4 shadow-sm">
                        @error('newPosPin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Konfirmasi PIN Baru</label>
                        <input type="password" maxlength="6" pattern="[0-9]*" inputmode="numeric" wire:model="newPosPinConfirm" placeholder="Ulangi PIN Baru" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-center text-lg font-bold tracking-[0.4em] py-3 px-4 shadow-sm">
                        @error('newPosPinConfirm') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showChangePinModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-lg shadow-brand-500/25 transition-all active:scale-95">Perbarui PIN</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Petty Cash (Kas Masuk/Keluar) -->
        <div x-show="showPettyCashModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display: none;">
            <div @click.away="showPettyCashModal = false"
                 x-show="showPettyCashModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-md rounded-2xl p-6 relative shadow-2xl">
                
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Kas Masuk / Keluar (Petty Cash)
                    </h3>
                    <button type="button" @click="showPettyCashModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="recordPettyCash" class="space-y-4">
                    <!-- Toggle Type: Kas Keluar vs Kas Masuk -->
                    <div class="grid grid-cols-2 gap-2 p-1.5 bg-gray-100 rounded-xl">
                        <button type="button" wire:click="$set('pettyCashType', 'out')"
                                class="py-2.5 rounded-lg text-sm transition-all font-bold flex items-center justify-center gap-1.5 {{ $pettyCashType === 'out' ? 'bg-red-500 text-white shadow-md' : 'text-gray-600 hover:text-gray-900' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                            <span>Kas Keluar</span>
                        </button>
                        <button type="button" wire:click="$set('pettyCashType', 'in')"
                                class="py-2.5 rounded-lg text-sm transition-all font-bold flex items-center justify-center gap-1.5 {{ $pettyCashType === 'in' ? 'bg-green-600 text-white shadow-md' : 'text-gray-600 hover:text-gray-900' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                            <span>Kas Masuk</span>
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nominal Uang (Rp)</label>
                        <input type="number" wire:model="pettyCashAmount" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-xl font-bold py-3 px-4 shadow-sm" placeholder="0">
                        @error('pettyCashAmount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Keterangan / Alasan</label>
                        <input type="text" wire:model="pettyCashNotes" class="w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm py-3 px-4 shadow-sm" placeholder="Contoh: Beli air galon ruko / Beli lakban">
                        @error('pettyCashNotes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showPettyCashModal = false" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-3 {{ $pettyCashType === 'out' ? 'bg-red-600 hover:bg-red-700 shadow-red-500/25' : 'bg-green-600 hover:bg-green-700 shadow-green-500/25' }} text-white font-bold rounded-xl shadow-lg transition-all active:scale-95">Simpan Kas</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Voucher / Promo -->
        <div x-show="showVoucherModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" style="display: none;">
            <div @click.away="showVoucherModal = false"
                 x-show="showVoucherModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="glass w-full max-w-lg rounded-2xl p-6 relative max-h-[80vh] flex flex-col shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        Pilih Kupon Promo
                    </h3>
                    <button @click="showVoucherModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 p-2 rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="overflow-y-auto flex-1 space-y-3 pr-2">
                    <template x-if="vouchers.length === 0">
                        <div class="text-center py-10 text-gray-400">
                            <p>Tidak ada promo yang sedang aktif saat ini.</p>
                        </div>
                    </template>
                    
                    <template x-for="v in vouchers" :key="v.id">
                        <div 
                            class="border rounded-xl p-4 flex flex-col gap-2 transition-all"
                            :class="isVoucherEligible(v) ? (activeVoucher && activeVoucher.id === v.id ? 'bg-brand-50 border-brand-400 shadow-md' : 'bg-white border-gray-200 hover:border-brand-300 cursor-pointer') : 'bg-gray-50 border-gray-200 opacity-60 cursor-not-allowed'"
                            @click="isVoucherEligible(v) ? applyVoucher(v) : null">
                            
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-800" x-text="v.name"></h4>
                                    <div class="text-xs text-gray-500 font-mono mt-1 bg-gray-100 px-2 py-0.5 rounded inline-block" x-text="v.code"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-brand-600" x-text="v.discount_type === 'percent' ? v.discount_amount + '%' : 'Rp ' + formatMoney(v.discount_amount)"></div>
                                </div>
                            </div>
                            
                            <div class="text-sm text-gray-500 mt-2 flex items-center justify-between">
                                <div class="flex flex-col gap-0.5">
                                    <span x-show="v.min_purchase > 0" x-text="'Min. Belanja: Rp ' + formatMoney(v.min_purchase)"></span>
                                    <span x-show="v.min_items > 0" x-text="'Min. Item: ' + v.min_items + ' pcs'"></span>
                                    <span x-show="v.min_purchase <= 0 && v.min_items <= 0">Tanpa min. belanja</span>
                                </div>
                                
                                <span x-show="!isVoucherEligible(v)" class="text-xs font-semibold text-red-500">Syarat belum terpenuhi</span>
                                <span x-show="activeVoucher && activeVoucher.id === v.id" class="text-xs font-bold text-brand-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Dipakai
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div x-show="activeVoucher" class="mt-4 pt-4 border-t border-gray-100 text-center">
                    <button @click="removeVoucher()" class="text-red-500 text-sm font-semibold hover:underline">Lepas Kupon Saat Ini</button>
                </div>
            </div>
        </div>


        <!-- ============================================ -->
        <!-- PAGE: Riwayat Transaksi                     -->
        <!-- ============================================ -->
        <div x-show="activePage === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50 overflow-hidden" style="display:none;">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 px-6 py-5 flex items-center gap-4 shadow-sm">
                <button @click="activePage = 'kasir'" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 hover:text-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h1>
                    <p class="text-sm text-gray-400">Shift hari ini &mdash; {{ count($sessionOrders) }} transaksi</p>
                </div>
            </div>
            <!-- List -->
            <div class="flex-1 overflow-y-auto">
                @if(count($sessionOrders) === 0)
                <div class="flex flex-col items-center justify-center h-full text-gray-300 py-24">
                    <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-lg font-medium">Belum ada transaksi hari ini</p>
                    <p class="text-sm mt-1">Transaksi yang selesai akan muncul di sini</p>
                </div>
                @else
                <div class="max-w-3xl mx-auto p-6 space-y-3">
                    @foreach($sessionOrders as $order)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:border-brand-200 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-brand-50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800">#{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}{{ $order->customer_name ? ' · ' . $order->customer_name : '' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-xl text-brand-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $order->payment_method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $order->payment_method === 'cash' ? 'Tunai' : 'QRIS / Transfer' }}
                                </span>
                            </div>
                        </div>
                        <div class="border-t border-gray-50 pt-3 space-y-1">
                            @foreach($order->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $item->product_name }}{{ $item->variant_name ? ' - '.$item->variant_name : '' }} <span class="text-gray-400">x{{ $item->quantity }}</span></span>
                                <span class="font-medium text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                                                <!-- Card Action Footer: Reprint Struk & Void Order -->
                        <div class="border-t border-gray-100 mt-3 pt-3 flex flex-wrap justify-between items-center gap-2">
                            <span class="text-xs text-gray-400 font-medium">Kasir: {{ $order->cashier->name ?? 'Kasir' }}</span>
                            
                            @if($order->status === 'cancelled')
                                <div class="flex items-center gap-2 bg-red-50 text-red-700 px-3 py-1.5 rounded-xl border border-red-200 text-xs font-bold">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>VOID / DIBATALKAN</span>
                                    @if($order->voidBy)
                                        <span class="text-[11px] font-normal text-red-500">· Disetujui: {{ $order->voidBy->name }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <button wire:click="reprintReceipt({{ $order->id }})" wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-brand-50 text-gray-700 hover:text-brand-700 font-bold text-xs rounded-xl border border-gray-200 hover:border-brand-200 transition-all active:scale-95 shadow-sm group">
                                        <svg class="w-3.5 h-3.5 text-gray-500 group-hover:text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        <span>Cetak Ulang Struk</span>
                                    </button>
                                    
                                    <button type="button" @click="openVoidModal({{ $order->id }}, '{{ $order->order_number }}', {{ $order->grand_total }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs rounded-xl border border-red-200 hover:border-red-300 transition-all active:scale-95 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Batalkan (Void)</span>
                                    </button>

                                    <button type="button" @click="openReturnModal({{ $order->id }}, '{{ $order->order_number }}', @js($order->items->map(fn($i) => ['id' => $i->id, 'product_id' => $i->product_id, 'product_variant_id' => $i->product_variant_id, 'name' => $i->name, 'price' => (float)$i->price, 'quantity' => (int)$i->quantity])))"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl border border-amber-200 hover:border-amber-300 transition-all active:scale-95 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        <span>Retur / Tukar</span>
                                    </button>
                                </div>
                            @endif
                        </div>  </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Pelanggan                             -->
        <!-- ============================================ -->
        <div x-show="activePage === 'customers'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50 overflow-hidden" style="display:none;">
            <div class="bg-white border-b border-gray-100 px-6 py-5 flex items-center gap-4 shadow-sm">
                <button @click="activePage = 'kasir'" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 hover:text-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Pelanggan</h1>
                    <p class="text-sm text-gray-400">{{ count($sessionCustomers) }} pelanggan tercatat shift ini</p>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto">
                @if(count($sessionCustomers) === 0)
                <div class="flex flex-col items-center justify-center h-full text-gray-300 py-24">
                    <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-lg font-medium">Belum ada pelanggan tercatat</p>
                    <p class="text-sm mt-1">Isi nama pelanggan saat checkout agar muncul di sini</p>
                </div>
                @else
                <div class="max-w-3xl mx-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($sessionCustomers as $customer)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:border-brand-200 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                            {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-gray-800 truncate">{{ $customer->customer_name }}</div>
                            <div class="text-sm text-gray-400">{{ $customer->customer_phone ?: 'Tanpa nomor telepon' }}</div>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-xs bg-brand-50 text-brand-600 font-semibold px-2 py-0.5 rounded-full">{{ $customer->visit_count }}x kunjungan</span>
                                <span class="text-xs font-bold text-gray-700">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAGE: Rekap Kas                             -->
        <!-- ============================================ -->
        <div x-show="activePage === 'cashsummary'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="flex-1 flex flex-col h-full bg-gray-50 overflow-hidden" style="display:none;">
            <div class="bg-white border-b border-gray-100 px-6 py-5 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="activePage = 'kasir'" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 hover:text-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Rekap Kas</h1>
                        @if(!empty($sessionStats))
                        <p class="text-sm text-gray-400">Shift dibuka sejak {{ $sessionStats['opened_at'] }}</p>
                        @endif
                    </div>
                </div>
                <button @click="showPettyCashModal = true" class="px-4 py-2.5 bg-brand-600 text-white font-bold text-sm rounded-xl hover:bg-brand-700 transition-all flex items-center gap-2 shadow-lg shadow-brand-500/25 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>+ Catat Kas Masuk / Keluar</span>
                </button>
            </div>
            @if(!empty($sessionStats))
            <div class="flex-1 overflow-y-auto">
                <div class="max-w-2xl mx-auto p-6 space-y-5">
                    <!-- Total Transaksi -->
                    <div class="bg-brand-600 rounded-3xl p-8 text-center text-white shadow-xl shadow-brand-500/30">
                        <div class="text-6xl font-black">{{ $sessionStats['total_trx'] }}</div>
                        <div class="text-brand-200 font-medium mt-2">Total Transaksi Shift Ini</div>
                    </div>
                    <!-- Breakdown -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Rincian Penjualan</h3>
                        <div class="flex justify-between items-center py-3 border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <span class="text-gray-600">Modal Awal Shift</span>
                            </div>
                            <span class="font-bold text-gray-800">Rp {{ number_format($sessionStats['opening_cash'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <span class="text-gray-600">Penjualan Tunai</span>
                            </div>
                            <span class="font-bold text-green-600">+ Rp {{ number_format($sessionStats['cash_sales'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <span class="text-gray-600">QRIS / Transfer</span>
                            </div>
                            <span class="font-bold text-blue-600">+ Rp {{ number_format($sessionStats['non_cash_sales'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                </div>
                                <span class="text-gray-600">Kas Masuk (Tambahan)</span>
                            </div>
                            <span class="font-bold text-emerald-600">+ Rp {{ number_format($sessionStats['petty_cash_in'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                </div>
                                <span class="text-gray-600">Kas Keluar (Pengeluaran)</span>
                            </div>
                            <span class="font-bold text-red-600">- Rp {{ number_format($sessionStats['petty_cash_out'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="font-bold text-gray-800">Total Omzet</span>
                            <span class="font-black text-2xl text-gray-900">Rp {{ number_format($sessionStats['total_sales'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <!-- Estimasi Laci -->
                    <div class="bg-green-50 rounded-2xl border border-green-200 p-6">
                        <div class="text-sm font-bold text-green-600 uppercase tracking-wider mb-2">Estimasi Uang di Laci Kasir</div>
                        <div class="text-4xl font-black text-green-700">Rp {{ number_format($sessionStats['expected_cash'], 0, ',', '.') }}</div>
                        <div class="text-sm text-green-500 mt-1">Modal awal + penjualan tunai + kas masuk - kas keluar</div>
                    </div>

                    <!-- Riwayat Petty Cash Shift Ini -->
                    @if(count($sessionPettyCash) > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-2">Riwayat Kas Masuk / Keluar Shift Ini</h3>
                        <div class="space-y-2">
                            @foreach($sessionPettyCash as $cashLog)
                            <div class="flex items-center justify-between p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs {{ $cashLog->type === 'out' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                        {{ $cashLog->type === 'out' ? 'OUT' : 'IN' }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">{{ $cashLog->description }}</div>
                                        <div class="text-xs text-gray-400">{{ $cashLog->created_at->format('H:i') }}</div>
                                    </div>
                                </div>
                                <div class="font-bold text-sm {{ $cashLog->type === 'out' ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $cashLog->type === 'out' ? '-' : '+' }} Rp {{ number_format($cashLog->amount, 0, ',', '.') }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <button @click="activePage = 'kasir'; $nextTick(() => showCloseSession = true)" class="w-full py-4 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-700 transition-colors flex items-center justify-center gap-2 shadow-lg shadow-red-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Tutup Shift Sekarang
                    </button>
                </div>
            </div>
            @endif
        </div>

    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', () => ({
                isSidebarOpen: false,
                cart: [],
                discount: 0,
                cartBouncing: false,
                bounceTimeout: null,
                
                // Voucher State
                vouchers: @json($vouchers ?? []),
                paymentMethods: @json($paymentMethods ?? []),
                showVoucherModal: false,
                activeVoucher: null,
                
                // Modals
                showVariantModal: false,
                showCheckoutModal: false,
                showCloseSession: false,
                activePage: 'kasir',
                
                // Checkout State
                cashPaid: 0,
                cashChange: 0,
                paymentMethod: 'cash',
                customerName: '',
                customerPhone: '',
                isProcessing: false,
                
                // Toasts
                toasts: [],
                toastId: 0,

                currentProductForVariant: null,
                currentVariants: [],
                
                // Printer State
                printerConnected: false,
                printerDevice: null,
                printerType: 'bluetooth',
                printerCharacteristic: null,
                printerPort: null,
                heldCarts: [],
                showHoldModal: false,

                // Confirmation Modal State
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                confirmAction: null,
                
                askConfirm(title, message, callback) {
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.confirmAction = callback;
                    this.showConfirmModal = true;
                },
                
                executeConfirm() {
                    if (this.confirmAction) this.confirmAction();
                    this.showConfirmModal = false;
                },

                // Void Order Modal State
                supervisors: @json($supervisorsList ?? []),
                selectedSupervisorId: '',
                voidSupervisorIdInput: '',
                showVoidModal: false,
                voidOrderId: null,
                voidOrderNumber: '',
                voidOrderTotal: 0,
                voidReasonInput: '',
                voidSupervisorPinInput: '',

                openVoidModal(id, number, total) {
                    this.voidOrderId = id;
                    this.voidOrderNumber = number;
                    this.voidOrderTotal = total;
                    this.voidReasonInput = '';
                    this.voidSupervisorIdInput = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.voidSupervisorPinInput = '';
                    this.showVoidModal = true;
                },

                submitVoidOrder() {
                    if (!this.voidSupervisorIdInput) {
                        this.showToast('Pilih Supervisor terlebih dahulu', 'error');
                        return;
                    }
                    const pin = (this.voidSupervisorPinInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.showToast('Masukkan 6-digit PIN Supervisor', 'error');
                        return;
                    }
                    @this.call('voidOrder', this.voidOrderId, this.voidSupervisorIdInput, pin, this.voidReasonInput);
                },

                // Return & Exchange Modal State
                showReturnModal: false,
                returnOrderId: null,
                returnOrderNumber: '',
                returnType: 'exchange',
                returnReasonInput: '',
                returnSupervisorIdInput: '',
                returnSupervisorPinInput: '',
                returnOrderItems: [],
                returnExchangedItems: [],

                openReturnModal(id, number, items) {
                    this.returnOrderId = id;
                    this.returnOrderNumber = number;
                    this.returnType = 'exchange';
                    this.returnReasonInput = '';
                    this.returnSupervisorIdInput = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.returnSupervisorPinInput = '';
                    this.returnOrderItems = (items || []).map(i => ({
                        ...i,
                        return_qty: 0
                    }));
                    this.returnExchangedItems = [];
                    this.showReturnModal = true;
                },

                get returnSubtotal() {
                    return (this.returnOrderItems || []).reduce((acc, i) => acc + (i.price * (i.return_qty || 0)), 0);
                },

                get exchangeSubtotal() {
                    return (this.returnExchangedItems || []).reduce((acc, i) => acc + (i.price * (i.quantity || 0)), 0);
                },

                get returnNetAmount() {
                    return this.returnType === 'refund' ? -this.returnSubtotal : (this.exchangeSubtotal - this.returnSubtotal);
                },

                addExchangeItem(product, variant = null) {
                    const price = variant ? (variant.pos_discount_price || variant.pos_price || variant.price || product.price) : (product.pos_discount_price || product.pos_price || product.price);
                    const name = variant ? (product.name + ' - ' + variant.name) : product.name;
                    
                    const existing = this.returnExchangedItems.find(i => i.product_id === product.id && i.product_variant_id === (variant ? variant.id : null));
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        this.returnExchangedItems.push({
                            product_id: product.id,
                            product_variant_id: variant ? variant.id : null,
                            name: name,
                            price: parseFloat(price),
                            quantity: 1
                        });
                    }
                },

                removeExchangeItem(index) {
                    this.returnExchangedItems.splice(index, 1);
                },

                submitReturnProcess() {
                    const returned = this.returnOrderItems
                        .filter(i => (i.return_qty || 0) > 0)
                        .map(i => ({
                            product_id: i.product_id,
                            product_variant_id: i.product_variant_id,
                            quantity: parseInt(i.return_qty)
                        }));

                    if (returned.length === 0) {
                        this.showToast('Pilih minimal 1 barang yang akan diretur', 'error');
                        return;
                    }

                    const exchanged = this.returnType === 'exchange' ? this.returnExchangedItems.map(i => ({
                        product_id: i.product_id,
                        product_variant_id: i.product_variant_id,
                        quantity: parseInt(i.quantity)
                    })) : [];

                    const net = this.returnNetAmount;
                    if (net < 0 || this.returnType === 'refund') {
                        if (!this.returnSupervisorIdInput) {
                            this.showToast('Pilih Supervisor pengizin pengembalian uang terlebih dahulu', 'error');
                            return;
                        }
                        const pin = (this.returnSupervisorPinInput || '').toString().trim();
                        if (!pin || pin.length !== 6) {
                            this.showToast('Masukkan 6-digit PIN Supervisor', 'error');
                            return;
                        }
                    }

                    const payload = {
                        order_id: this.returnOrderId,
                        type: this.returnType,
                        reason: this.returnReasonInput,
                        returned_items: returned,
                        exchanged_items: exchanged,
                        supervisor_id: this.returnSupervisorIdInput,
                        supervisor_pin: this.returnSupervisorPinInput
                    };

                    @this.call('processReturn', JSON.stringify(payload));
                    this.showReturnModal = false;
                },

                // Input Modal State
                showInputModal: false,
                showPettyCashModal: false,
                showChangePinModal: false,
                showSupervisorPinModal: false,
                supervisorPinInput: '',
                supervisorErrorMessage: '',
                supervisorReasonMessage: '',
                pendingSupervisorCallback: null,

                requestSupervisorAuth(reason, callback) {
                    this.supervisorReasonMessage = reason;
                    this.selectedSupervisorId = this.supervisors.length === 1 ? this.supervisors[0].id : '';
                    this.supervisorPinInput = '';
                    this.supervisorErrorMessage = '';
                    this.pendingSupervisorCallback = callback;
                    this.showSupervisorPinModal = true;
                    setTimeout(() => {
                        const el = document.getElementById('posSupervisorPinField');
                        if (el) el.focus();
                    }, 100);
                },

                submitSupervisorAuth() {
                    if (!this.selectedSupervisorId) {
                        this.supervisorErrorMessage = 'Pilih Supervisor yang bertugas terlebih dahulu.';
                        return;
                    }
                    const pin = (this.supervisorPinInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.supervisorErrorMessage = 'Masukkan 6 digit angka PIN Supervisor.';
                        return;
                    }
                    this.supervisorErrorMessage = '';
                    @this.call('verifySupervisorPin', this.selectedSupervisorId, pin, 'general');
                },
                isLocked: false,
                lockPasswordInput: '',
                lockErrorMessage: '',
                lastActivityTime: Date.now(),

                appendLockPin(num) {
                    this.lockPasswordInput = (this.lockPasswordInput || '').toString();
                    if (this.lockPasswordInput.length < 6) {
                        this.lockPasswordInput += num.toString();
                    }
                },

                clearLockPin() {
                    this.lockPasswordInput = '';
                    this.lockErrorMessage = '';
                },

                lockScreen() {
                    this.isLocked = true;
                    this.lockPasswordInput = '';
                    this.lockErrorMessage = '';
                    sessionStorage.setItem('pos_locked', 'true');
                    setTimeout(() => {
                        const el = document.getElementById('posLockPasswordField');
                        if (el) el.focus();
                    }, 100);
                },

                submitUnlock() {
                    const pin = (this.lockPasswordInput || '').toString().trim();
                    if (!pin || pin.length !== 6) {
                        this.lockErrorMessage = 'Masukkan 6 digit angka PIN.';
                        return;
                    }
                    this.lockErrorMessage = '';
                    @this.call('unlockScreenWithPin', pin);
                },

                resetAutoLockTimer() {
                    this.lastActivityTime = Date.now();
                },

                startAutoLockChecker() {
                    // Check every 10 seconds for 5 minutes (300,000 ms) inactivity
                    setInterval(() => {
                        if (!this.isLocked && (Date.now() - this.lastActivityTime > 300000)) {
                            this.lockScreen();
                        }
                    }, 10000);

                    const events = ['mousemove', 'keydown', 'touchstart', 'click'];
                    events.forEach(evt => {
                        window.addEventListener(evt, () => this.resetAutoLockTimer(), { passive: true });
                    });
                },
                inputTitle: '',
                inputMessage: '',
                inputValue: '',
                inputPlaceholder: '',
                inputAction: null,
                
                askInput(title, message, placeholder, defaultValue, callback) {
                    this.inputTitle = title;
                    this.inputMessage = message;
                    this.inputPlaceholder = placeholder;
                    this.inputValue = defaultValue || '';
                    this.inputAction = callback;
                    this.showInputModal = true;
                    // Focus input after modal renders
                    setTimeout(() => {
                        const el = document.getElementById('alpineInputModalField');
                        if(el) el.focus();
                    }, 50);
                },
                
                executeInput() {
                    if (this.inputAction) this.inputAction(this.inputValue);
                    this.showInputModal = false;
                },

                init() {
                    // Load dari localStorage
                    const storedHold = localStorage.getItem('pos_held_carts');
                    if (storedHold) {
                        try {
                            this.heldCarts = JSON.parse(storedHold);
                        } catch(e) {}
                    }

                    // Menerima event dari Livewire
                    if (sessionStorage.getItem('pos_locked') === 'true') {
                        this.isLocked = true;
                    }
                    window.addEventListener('screen-unlocked', () => {
                        this.isLocked = false;
                        this.lockPasswordInput = '';
                        this.lockErrorMessage = '';
                        sessionStorage.removeItem('pos_locked');
                        this.showToast('Layar kasir berhasil dibuka', 'success');
                    });
                    window.addEventListener('screen-unlock-failed', (e) => {
                        this.lockErrorMessage = e.detail[0].message || 'Password salah!';
                        this.showToast(this.lockErrorMessage, 'error');
                    });
                    window.addEventListener('session-opened', () => { this.showToast('Sesi kasir berhasil dibuka', 'success'); });
                    window.addEventListener('session-closed', () => { this.showCloseSession = false; this.showToast('Sesi kasir berhasil ditutup', 'success'); });
                    window.addEventListener('petty-cash-saved', () => { this.showPettyCashModal = false; });
                    window.addEventListener('pin-created', () => { this.showToast('PIN POS 6-digit berhasil dibuat!', 'success'); });
                    window.addEventListener('pin-changed', () => { this.showChangePinModal = false; this.showToast('PIN POS berhasil diperbarui', 'success'); });
                    window.addEventListener('supervisor-authorized', () => {
                        this.showSupervisorPinModal = false;
                        this.showToast('Otorisasi Supervisor Berhasil', 'success');
                        if (this.pendingSupervisorCallback) {
                            const cb = this.pendingSupervisorCallback;
                            this.pendingSupervisorCallback = null;
                            cb();
                        }
                    });
                    window.addEventListener('supervisor-auth-failed', (e) => {
                        this.supervisorErrorMessage = e.detail[0].message || 'PIN Supervisor Salah!';
                        this.showToast(this.supervisorErrorMessage, 'error');
                    });
                    window.addEventListener('order-voided', () => {
                        this.showVoidModal = false;
                    });
                    this.startAutoLockChecker();
                    window.addEventListener('notify', (e) => { this.showToast(e.detail[0].message, e.detail[0].type); this.isProcessing = false; });
                    window.addEventListener('checkout-success', (e) => {
                        this.isProcessing = false;
                        this.showCheckoutModal = false;
                        this.clearCart(true);
                        this.showToast('Pembayaran Berhasil! Kembalian: Rp ' + this.formatMoney(e.detail[0].cash_change), 'success');
                    });
                    window.addEventListener('print-receipt', (e) => {
                        this.printBase64(e.detail[0].base64);
                    });
                    window.addEventListener('print-z-report', (e) => {
                        this.printBase64(e.detail[0].base64);
                    });
                },

                saveHeldCarts() {
                    localStorage.setItem('pos_held_carts', JSON.stringify(this.heldCarts));
                },

                holdCart() {
                    if (this.cart.length === 0) return;
                    
                    let defaultName = this.customerName;
                    if (!defaultName) {
                        const firstItem = this.cart[0].name;
                        const otherItems = this.cart.length > 1 ? ` + ${this.cart.length - 1} item` : '';
                        defaultName = `${firstItem}${otherItems}`;
                    }

                    this.askInput(
                        'Simpan ke Antrean',
                        'Berikan catatan, nomor meja, atau nama pelanggan untuk antrean ini:',
                        'Contoh: Meja 4 / Budi',
                        defaultName,
                        (val) => {
                            const holdId = Date.now();
                            const now = new Date();
                            const holdTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                            this.heldCarts.push({
                                id: holdId,
                                time: holdTime,
                                name: val || defaultName,
                                cart: JSON.parse(JSON.stringify(this.cart)),
                                activeVoucher: this.activeVoucher,
                                manualDiscountType: this.manualDiscountType,
                                manualDiscountValue: this.manualDiscountValue,
                                customerName: this.customerName,
                                customerPhone: this.customerPhone,
                                total: this.subtotal
                            });
                            
                            this.saveHeldCarts();
                            this.clearCart(true);
                            this.showToast('Pesanan berhasil dimasukkan ke antrean', 'success');
                        }
                    );
                },

                resumeCart(id) {
                    const index = this.heldCarts.findIndex(h => h.id === id);
                    if (index !== -1) {
                        const doResume = () => {
                            const hold = this.heldCarts[index];
                            this.cart = hold.cart;
                            this.activeVoucher = hold.activeVoucher || null;
                            this.manualDiscountType = hold.manualDiscountType || 'rp';
                            this.manualDiscountValue = hold.manualDiscountValue || 0;
                            this.customerName = hold.customerName || '';
                            this.customerPhone = hold.customerPhone || '';
                            
                            this.heldCarts.splice(index, 1);
                            this.saveHeldCarts();
                            this.showHoldModal = false;
                            this.showToast('Antrean berhasil dilanjutkan', 'success');
                        };

                        if (this.cart.length > 0) {
                            this.askConfirm(
                                'Tumpuk Keranjang?', 
                                'Keranjang Anda saat ini tidak kosong. Jika dilanjutkan, belanjaan saat ini akan terganti oleh antrean yang dipanggil. Lanjutkan?', 
                                doResume
                            );
                        } else {
                            doResume();
                        }
                    }
                },

                deleteHeldCart(id) {
                    this.askConfirm(
                        'Hapus Antrean?', 
                        'Apakah Anda yakin ingin menghapus antrean ini? Data tidak bisa dikembalikan.', 
                        () => {
                            this.heldCarts = this.heldCarts.filter(h => h.id !== id);
                            this.saveHeldCarts();
                        }
                    );
                },

                addProduct(id, name, price, hasVariants, variants = null) {
                    if (hasVariants && variants) {
                        this.currentProductForVariant = { id, name };
                        this.currentVariants = variants;
                        this.showVariantModal = true;
                        return;
                    }
                    this.addToCart(id, null, name, price);
                },

                addVariantToCart(variantId, variantName, variantPrice) {
                    const fullName = this.currentProductForVariant.name + ' - ' + variantName;
                    this.addToCart(this.currentProductForVariant.id, variantId, fullName, variantPrice);
                    this.showVariantModal = false;
                },

                async connectPrinter() {
                    if (this.printerType === 'bluetooth') {
                        // Cek apakah browser mendukung Bluetooth
                        if (!navigator.bluetooth) {
                            this.showToast('Browser Anda tidak mendukung koneksi Bluetooth. Gunakan Google Chrome atau Microsoft Edge, dan pastikan halaman dibuka lewat HTTPS.', 'error');
                            return;
                        }
                        try {
                            const device = await navigator.bluetooth.requestDevice({
                                filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }],
                                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                            });
                            const server = await device.gatt.connect();
                            const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                            const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                            
                            this.printerDevice = device;
                            this.printerCharacteristic = characteristic;
                            this.printerConnected = true;
                            this.showToast('Printer Bluetooth berhasil terhubung!', 'success');
                            
                            device.addEventListener('gattserverdisconnected', () => {
                                this.printerConnected = false;
                                this.showToast('Koneksi printer terputus. Klik "Printer Offline" untuk menyambung kembali.', 'error');
                            });
                        } catch (error) {
                            console.error(error);
                            // Pesan error yang ramah pengguna
                            if (error.name === 'NotFoundError' || error.message.includes('cancelled')) {
                                this.showToast('Pencarian printer dibatalkan.', 'error');
                            } else if (error.name === 'SecurityError') {
                                this.showToast('Akses Bluetooth ditolak. Pastikan halaman dibuka lewat HTTPS dan izin Bluetooth diaktifkan di browser.', 'error');
                            } else {
                                this.showToast('Tidak bisa terhubung ke printer. Pastikan printer sudah dinyalakan dan dalam jangkauan Bluetooth.', 'error');
                            }
                        }
                    } else if (this.printerType === 'serial') {
                        // Cek apakah browser mendukung Serial/USB
                        if (!navigator.serial) {
                            this.showToast('Browser Anda tidak mendukung koneksi USB/Serial. Gunakan Google Chrome atau Microsoft Edge.', 'error');
                            return;
                        }
                        try {
                            const port = await navigator.serial.requestPort();
                            await port.open({ baudRate: 9600 });
                            this.printerPort = port;
                            this.printerConnected = true;
                            this.showToast('Printer USB/Serial berhasil terhubung!', 'success');
                        } catch (error) {
                            console.error(error);
                            if (error.name === 'NotFoundError' || error.message.includes('No port selected')) {
                                this.showToast('Tidak ada printer USB yang dipilih.', 'error');
                            } else {
                                this.showToast('Tidak bisa terhubung ke printer USB. Pastikan kabel terpasang dengan benar.', 'error');
                            }
                        }
                    }
                },

                async printBase64(base64Data) {
                    if (!this.printerConnected) {
                        this.showToast('Gagal mencetak: Printer belum dihubungkan!', 'error');
                        return;
                    }
                    try {
                        const binaryString = window.atob(base64Data);
                        const bytes = new Uint8Array(binaryString.length);
                        for (let i = 0; i < binaryString.length; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        
                        if (this.printerType === 'bluetooth' && this.printerCharacteristic) {
                            const chunkSize = 100;
                            for (let i = 0; i < bytes.length; i += chunkSize) {
                                const chunk = bytes.slice(i, i + chunkSize);
                                await this.printerCharacteristic.writeValue(chunk);
                            }
                        } else if (this.printerType === 'serial' && this.printerPort) {
                            const writer = this.printerPort.writable.getWriter();
                            await writer.write(bytes);
                            writer.releaseLock();
                        }
                    } catch (error) {
                        console.error(error);
                        this.showToast('Kesalahan cetak: ' + error.message, 'error');
                    }
                },

                triggerCartBounce() {
                    this.cartBouncing = true;
                    if (this.bounceTimeout) clearTimeout(this.bounceTimeout);
                    this.bounceTimeout = setTimeout(() => { this.cartBouncing = false; }, 500);
                },

                addToCart(productId, variantId, name, price) {
                    this.triggerCartBounce();
                    // Cek jika produk sudah ada di keranjang
                    const existingIndex = this.cart.findIndex(item => item.product_id === productId && item.product_variant_id === variantId);
                    if (existingIndex > -1) {
                        this.cart[existingIndex].quantity++;
                    } else {
                        this.cart.unshift({ // Add to top
                            product_id: productId,
                            product_variant_id: variantId,
                            name: name,
                            price: price,
                            quantity: 1
                        });
                    }
                    this.calculateVoucherDiscount();
                },

                updateQty(index, change) {
                    let newQty = this.cart[index].quantity + change;
                    if (newQty > 0) {
                        this.cart[index].quantity = newQty;
                    }
                    this.calculateVoucherDiscount();
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.calculateVoucherDiscount();
                },

                clearCart(force = false) {
                    if (force) {
                        this.cart = [];
                        this.activeVoucher = null;
                        this.manualDiscountValue = 0;
                        this.cashPaid = 0;
                        return;
                    }

                    this.askConfirm(
                        'Kosongkan Keranjang?',
                        'Semua barang di keranjang akan dihapus. Lanjutkan?',
                        () => {
                            this.cart = [];
                            this.activeVoucher = null;
                            this.manualDiscountValue = 0;
                            this.cashPaid = 0;
                        }
                    );
                },

                applyVoucher(voucher) {
                    if (!this.isVoucherEligible(voucher)) {
                        this.showToast('Minimal belanja untuk voucher ini belum terpenuhi', 'error');
                        return;
                    }
                    this.activeVoucher = voucher;
                    this.showVoucherModal = false;
                    this.calculateVoucherDiscount();
                    this.showToast('Voucher ' + voucher.name + ' dipasang!', 'success');
                },
                
                removeVoucher() {
                    this.activeVoucher = null;
                    this.calculateVoucherDiscount();
                    this.showToast('Voucher dilepas', 'success');
                },
                
                isVoucherEligible(voucher) {
                    let eligible = true;
                    if (voucher.min_purchase > 0 && parseFloat(this.subtotal) < parseFloat(voucher.min_purchase)) {
                        eligible = false;
                    }
                    if (voucher.min_items > 0 && this.totalItems < voucher.min_items) {
                        eligible = false;
                    }
                    return eligible;
                },
                
                // Diskon Manual State
                manualDiscountType: 'rp',
                manualDiscountValue: 0,
                showManualDiscountModal: false,
                tempManualDiscountType: 'rp',
                tempManualDiscountValue: '',

                openManualDiscountModal() {
                    this.tempManualDiscountType = this.manualDiscountType;
                    this.tempManualDiscountValue = this.manualDiscountValue > 0 ? this.manualDiscountValue : '';
                    this.showManualDiscountModal = true;
                    setTimeout(() => {
                        const el = document.getElementById('alpineManualDiscountField');
                        if (el) el.focus();
                    }, 50);
                },

                applyManualDiscount() {
                    let val = parseFloat(this.tempManualDiscountValue) || 0;
                    if (val < 0) val = 0;

                    if (this.tempManualDiscountType === 'percent' && val > 100) {
                        this.showToast('Diskon persen maksimal 100%', 'error');
                        return;
                    }

                    const isHighDiscount = (this.tempManualDiscountType === 'percent' && val > 20) || (this.tempManualDiscountType === 'rp' && val > 50000);

                    if (val > 0 && isHighDiscount) {
                        const reasonText = `Diskon manual ${this.tempManualDiscountType === 'percent' ? val + '%' : 'Rp ' + this.formatMoney(val)} memerlukan otorisasi Supervisor (> 20% / > Rp 50k).`;
                        this.requestSupervisorAuth(reasonText, () => {
                            this.commitManualDiscount(val);
                        });
                    } else {
                        this.commitManualDiscount(val);
                    }
                },

                commitManualDiscount(val) {
                    this.manualDiscountType = this.tempManualDiscountType;
                    this.manualDiscountValue = val;
                    this.showManualDiscountModal = false;

                    if (val > 0) {
                        this.showToast('Diskon manual berhasil diterapkan', 'success');
                    }
                },

                removeManualDiscount() {
                    this.manualDiscountValue = 0;
                    this.showToast('Diskon manual dilepas', 'success');
                },

                get voucherDiscountAmount() {
                    if (!this.activeVoucher) return 0;
                    if (!this.isVoucherEligible(this.activeVoucher)) return 0;

                    if (this.activeVoucher.discount_type === 'percent') {
                        let disc = (this.subtotal * parseFloat(this.activeVoucher.discount_amount)) / 100;
                        if (this.activeVoucher.max_discount && disc > parseFloat(this.activeVoucher.max_discount)) {
                            disc = parseFloat(this.activeVoucher.max_discount);
                        }
                        return disc;
                    } else {
                        return parseFloat(this.activeVoucher.discount_amount);
                    }
                },

                get manualDiscountAmount() {
                    if (!this.manualDiscountValue || this.manualDiscountValue <= 0 || this.subtotal <= 0) return 0;
                    if (this.manualDiscountType === 'percent') {
                        return Math.min(this.subtotal, (this.subtotal * parseFloat(this.manualDiscountValue)) / 100);
                    } else {
                        return Math.min(this.subtotal, parseFloat(this.manualDiscountValue));
                    }
                },

                get discount() {
                    return Math.min(this.subtotal, this.voucherDiscountAmount + this.manualDiscountAmount);
                },

                calculateVoucherDiscount() {
                    if (!this.activeVoucher) return;
                    
                    if (!this.isVoucherEligible(this.activeVoucher)) {
                        this.activeVoucher = null;
                        this.showToast('Voucher otomatis dilepas karena keranjang tidak memenuhi syarat minimum', 'error');
                    }
                },

                get totalItems() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal - this.discount);
                },

                isCashSelected() {
                    const found = this.paymentMethods.find(m => m.code === this.paymentMethod);
                    if (found) return found.is_cash;
                    return this.paymentMethod === 'tunai' || this.paymentMethod === 'cash';
                },

                selectPaymentMethod(method) {
                    this.paymentMethod = method.code;
                    if (method.is_cash) {
                        this.calculateChange();
                    } else {
                        this.cashPaid = this.grandTotal;
                        this.calculateChange();
                    }
                },

                openCheckoutModal() {
                    if (this.cart.length === 0) return;
                    if (this.paymentMethods.length > 0) {
                        const cashMethod = this.paymentMethods.find(m => m.is_cash);
                        this.paymentMethod = cashMethod ? cashMethod.code : this.paymentMethods[0].code;
                    } else {
                        this.paymentMethod = 'tunai';
                    }
                    
                    this.cashPaid = this.grandTotal;
                    this.calculateChange();
                    this.showCheckoutModal = true;
                },

                setCashPaid(amount) {
                    this.cashPaid = amount;
                    this.calculateChange();
                },

                calculateChange() {
                    this.cashChange = this.cashPaid - this.grandTotal;
                },

                submitOrder() {
                    if (this.isCashSelected() && this.cashPaid < this.grandTotal) {
                        this.showToast('Uang yang dibayarkan kurang!', 'error');
                        return;
                    }

                    this.isProcessing = true;
                    
                    const payload = {
                        items: this.cart,
                        discount: this.discount,
                        payment_method: this.paymentMethod,
                        cash_paid: this.cashPaid,
                        cash_change: this.cashChange,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        payment_details: {
                            type: this.paymentMethod,
                            // UUID/Idempotency key dapat digenerate di sini
                            idempotency_key: crypto.randomUUID()
                        }
                    };

                    // Panggil Livewire backend
                    @this.call('processCheckout', payload);
                },

                formatMoney(amount) {
                    return Number(amount).toLocaleString('id-ID');
                },

                showToast(message, type = 'success') {
                    const id = this.toastId++;
                    this.toasts.push({ id, message, type });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 4000);
                }
            }));
        });
    </script>
</div>
