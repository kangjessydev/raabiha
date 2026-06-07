
<main class="site-main bg-[#fcf9f5] min-h-screen pb-24 pt-12 md:pt-16">

        <!-- Header Section -->
        <section class="max-w-[1440px] mx-auto px-6 lg:px-12 mb-12">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-serif text-[#1c1c1a] mb-6 tracking-tight uppercase">
                Katalog Produk
            </h1>
            <p class="text-[#615e57] text-sm md:text-base leading-relaxed max-w-2xl mb-12 font-sans">
                Architectural silhouettes designed for the contemporary modest lifestyle. Melding urban utility with high-fashion restraint.
            </p>
            <div class="w-full h-[1px] bg-[#e5e2de]"></div>
        </section>

        <!-- Main Shop Layout -->
        <section class="max-w-[1440px] mx-auto px-6 lg:px-12">
            <div class="flex flex-col lg:flex-row gap-12 lg:gap-16">
                
                <!-- Sidebar Filters Backdrop (Mobile) -->
                <div id="filter-backdrop" class="fixed inset-0 bg-black/50 z-[60] hidden lg:hidden opacity-0 transition-opacity duration-300"></div>
                
                <!-- Sidebar Filters -->
                <aside id="shop-filter-sidebar" class="
                    fixed inset-x-0 bottom-0 z-[70] bg-[#fcf9f5] p-6 rounded-t-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] 
                    transform translate-y-full transition-transform duration-300
                    lg:static lg:transform-none lg:z-auto lg:bg-transparent lg:p-0 lg:rounded-none lg:shadow-none lg:block
                    w-full lg:w-64 shrink-0 max-h-[85vh] lg:max-h-none overflow-y-auto lg:overflow-visible
                ">
                    <!-- Mobile Bottomsheet Handle & Title -->
                    <div class="flex justify-between items-center mb-6 lg:hidden">
                        <h3 class="text-[#1c1c1a] text-sm font-mono font-bold tracking-[0.2em] uppercase">Filter Produk</h3>
                        <button id="filter-close-btn" class="text-[#1c1c1a] p-2 hover:bg-[#e5e2de] rounded-full transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-10">
                        <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Kategori</h4>
                        <div class="flex flex-col gap-4">
                            @foreach ($categories as $category)
                                <label class="flex items-center gap-3 cursor-pointer group category-checkbox">
                                    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category->id }}" class="hidden peer">
                                    <div class="w-4 h-4 border border-[#d1cec9] peer-checked:bg-[#1c1c1a] peer-checked:border-[#1c1c1a] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors">
                                        <svg class="w-2.5 h-2.5 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest peer-checked:font-bold">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Ukuran -->
                    <div class="mb-10">
                        <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Ukuran</h4>
                        <div class="grid grid-cols-2 gap-y-4" id="size-filter-list">
                            <label class="flex items-center gap-3 cursor-pointer group size-checkbox" data-size="XS/S">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">XS/S</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group size-checkbox" data-size="M/L">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">M/L</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group size-checkbox" data-size="Oversized">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">Oversized</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group size-checkbox" data-size="Uni-Size">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">Uni-Size</span>
                            </label>
                        </div>
                    </div>

                    <!-- Warna -->
                    <div class="mb-10">
                        <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Warna</h4>
                        <div class="flex flex-col gap-4" id="color-filter-list">
                            <label class="flex items-center gap-3 cursor-pointer group color-checkbox" data-color="Charcoal">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="w-3 h-3 bg-[#333333] shrink-0 border border-black/10"></div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">Charcoal</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group color-checkbox" data-color="Slate Sand">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="w-3 h-3 bg-[#d9d5cd] shrink-0 border border-black/10"></div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">Slate Sand</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group color-checkbox" data-color="Dusty Rose">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="w-3 h-3 bg-[#c99a8b] shrink-0 border border-black/10"></div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">Dusty Rose</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group color-checkbox" data-color="Off-White">
                                <div class="w-4 h-4 border border-[#d1cec9] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors checkbox-box">
                                    <svg class="w-2.5 h-2.5 text-white hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="w-3 h-3 bg-[#f2f2f2] shrink-0 border border-black/10"></div>
                                <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest checkbox-label">Off-White</span>
                            </label>
                        </div>
                    </div>

                    <!-- Harga -->
                    <div class="mb-10">
                        <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Harga Maksimal</h4>
                        <div class="mt-6 px-1">
                            <input type="range" id="price-range" min="200000" max="4000000" step="100000" value="4000000" class="w-full h-1 bg-[#d1cec9] appearance-none cursor-pointer accent-[#1c1c1a] focus:outline-none">
                            <div class="flex justify-between mt-4">
                                <span class="text-[#1c1c1a] text-[9px] font-mono tracking-widest uppercase">200k IDR</span>
                                <span id="price-display" class="text-[#064e3b] text-[10px] font-mono font-bold tracking-widest uppercase">4.000.000 IDR</span>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Product Grid Area -->
                <div class="flex-1">
                    
                    <!-- Top Bar -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                        
                        <!-- Mobile Toolbar -->
                        <div class="flex items-center gap-3 w-full lg:w-auto">
                            <!-- Filter Icon Button (Mobile Only) -->
                            <button id="mobile-filter-btn" class="lg:hidden flex items-center justify-center w-8 h-8 text-[#1c1c1a] hover:text-[#615e57] transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            </button>
                            
                            <!-- Search Bar -->
                            <div class="relative flex-1 lg:w-64">
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." class="w-full border border-[#e5e2de] px-4 py-2.5 text-[10px] font-mono tracking-widest uppercase bg-[#fcf9f5] focus:outline-none focus:border-[#1c1c1a] transition-colors">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[#615e57]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                            </div>
                            
                            <span class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest hidden lg:inline-block ml-3" wire:loading.class="opacity-50 transition-opacity">{{ $products->total() }} Produk</span>
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="flex items-center gap-4 w-full lg:w-auto justify-between lg:justify-end">
                            <span class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest">Urutkan:</span>
                            <div class="relative">
                                <select wire:model.live="sort" class="appearance-none bg-transparent border border-[#1c1c1a] px-4 py-2 pr-8 text-[10px] font-mono font-bold uppercase tracking-widest cursor-pointer focus:outline-none">
                                    <option value="default">Terbaru</option>
                                    <option value="price-low">Harga Terendah</option>
                                    <option value="price-high">Harga Tertinggi</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[#1c1c1a]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div id="products-grid" wire:loading.remove wire:target="search, selectedCategories, sort" class="grid grid-cols-2 lg:grid-cols-3 gap-x-4 lg:gap-x-6 gap-y-12 w-full">
                        @forelse ($products as $product)
                            <a href="{{ url('/product/' . $product->slug) }}" wire:navigate class="group block">
                                <div class="aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    @if ($product->promo_rules)
                                        <div class="absolute top-3 right-3 bg-[#1a1a1a] text-white text-[9px] px-2 py-1 uppercase tracking-widest z-10">Sale</div>
                                    @endif
                                    <!-- Temporary placeholder image if product has no image yet -->
                                    <img width="1024" height="1024" src="{{ asset('assets/images/gallery-' . (rand(1, 3)) . '.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}" />
                                    <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </button>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">{{ $product->name }}</h3>
                                <div class="text-[13px] text-[#525252]">Rp{{ number_format($product->effective_price, 0, ',', '.') }}</div>
                            </a>
                        @empty
                            <div class="col-span-2 lg:col-span-3 text-center py-24">
                                <p class="text-[#615e57] font-mono uppercase tracking-widest text-xs mb-4">Belum ada produk yang cocok dengan kriteria filter.</p>
                                <button wire:click="$set('search', '')" class="border border-[#1c1c1a] px-6 py-3 text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-[#f2efe8] transition-colors">Reset Filter</button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Skeleton Loading Grid -->
                    <div wire:loading wire:target="search, selectedCategories, sort" class="w-full">
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-4 lg:gap-x-6 gap-y-12">
                            @for ($i = 0; $i < 6; $i++)
                                <div class="animate-pulse block">
                                    <div class="aspect-[4/5] bg-[#e5e2de] mb-4"></div>
                                    <div class="h-3 bg-[#e5e2de] w-3/4 mb-2"></div>
                                    <div class="h-3 bg-[#e5e2de] w-1/4"></div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    @if($products->hasPages())
                        <div class="mt-12">
                            {{ $products->links() }}
                        </div>
                    @endif



                </div>
            </div>
        </section>

    </main>

