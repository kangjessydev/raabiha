
<main class="site-main bg-[#fcf9f5] min-h-screen pb-24 pt-4 md:pt-8" x-data="{ mobileFilterOpen: false }">

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
                
                <!-- Drawer Filters Backdrop -->
                <div id="filter-backdrop" @click="mobileFilterOpen = false" x-show="mobileFilterOpen" x-transition.opacity class="fixed inset-0 bg-black/50 z-[105] transition-opacity duration-300" style="display: none;"></div>
                
                <!-- Sidebar/Drawer Filters -->
                <aside id="shop-filter-sidebar" data-lenis-prevent
                    :class="mobileFilterOpen ? 'translate-y-0 lg:translate-x-0' : 'translate-y-full lg:translate-y-0 lg:translate-x-full'"
                    class="
                    fixed inset-x-0 bottom-0 lg:inset-y-0 lg:left-auto lg:right-0 z-[110] 
                    bg-[#fcf9f5] rounded-t-2xl lg:rounded-none shadow-2xl
                    transition-transform duration-300
                    w-full lg:w-[400px] shrink-0 max-h-[85vh] lg:max-h-screen overflow-hidden
                    flex flex-col
                ">
                    <!-- Drawer Header -->
                    <div class="flex justify-between items-center p-6 pb-4 border-b border-[#e5e2de]">
                        <h3 class="text-[#1c1c1a] text-sm font-mono font-bold tracking-[0.2em] uppercase">Filter Produk</h3>
                        <button @click="mobileFilterOpen = false" class="text-[#1c1c1a] p-2 -mr-2 hover:bg-[#e5e2de] rounded-full transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-[#d1cec9] scrollbar-track-transparent">

                    <!-- Kategori -->
                    <div class="mb-10" x-data="{ expanded: false }">
                        <div class="flex justify-between items-end mb-4 pb-2 border-b border-[#e5e2de]">
                            <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase">Kategori</h4>
                            @if($categories->count() > 5)
                                <button x-show="expanded" x-cloak @click="expanded = false" class="text-[9px] text-[#615e57] hover:text-[#1c1c1a] font-mono font-bold tracking-widest uppercase transition-colors">
                                    - Sembunyikan
                                </button>
                            @endif
                        </div>
                        <div class="flex flex-col gap-4">
                            @foreach ($categories as $index => $category)
                                <label class="flex items-center gap-3 cursor-pointer group category-checkbox relative" x-show="expanded || {{ $index }} < 5" style="display: {{ $index < 5 ? 'flex' : 'none' }};">
                                    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category->id }}" class="opacity-0 absolute w-0 h-0 peer">
                                    <div class="w-4 h-4 border border-[#d1cec9] peer-checked:bg-[#1c1c1a] peer-checked:border-[#1c1c1a] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors">
                                        <svg class="w-2.5 h-2.5 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest peer-checked:font-bold">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if($categories->count() > 5)
                            <button @click="expanded = !expanded" class="text-[9px] text-[#615e57] hover:text-[#1c1c1a] font-mono font-bold tracking-widest mt-4 uppercase transition-colors flex items-center gap-1">
                                <span x-text="expanded ? '- Sembunyikan' : '+ Lihat {{ $categories->count() - 5 }} Lainnya'"></span>
                            </button>
                        @endif
                    </div>

                    <!-- Dynamic Attributes -->
                    @foreach($filterAttributes as $attr)
                        @if($attr->options->count() > 0)
                        @php $isLarge = $attr->options->count() > 10; @endphp
                        <div class="mb-10" x-data="{ expanded: false, searchOption: '' }">
                            <div class="flex justify-between items-end mb-4 pb-2 border-b border-[#e5e2de]">
                                <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase">{{ $attr->name }}</h4>
                                @if(!$isLarge && $attr->options->count() > 5)
                                    <button x-show="expanded" x-cloak @click="expanded = false" class="text-[9px] text-[#615e57] hover:text-[#1c1c1a] font-mono font-bold tracking-widest uppercase transition-colors">
                                        - Sembunyikan
                                    </button>
                                @endif
                            </div>
                            
                            @if($isLarge)
                                <div class="mb-4 relative">
                                    <input x-model="searchOption" type="text" placeholder="Cari {{ strtolower($attr->name) }}..." class="w-full border-b border-[#e5e2de] px-0 py-2 text-[10px] font-mono tracking-widest uppercase bg-transparent focus:outline-none focus:border-[#1c1c1a] transition-colors">
                                    <span class="absolute right-0 top-1/2 -translate-y-1/2 text-[#615e57]">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                </div>
                            @endif

                            <div class="{{ strtolower($attr->name) == 'ukuran' ? 'grid grid-cols-2 gap-y-4' : (strtolower($attr->name) == 'warna' ? 'flex flex-wrap gap-2' : 'flex flex-col gap-4') }} {{ $isLarge ? 'max-h-[240px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-[#d1cec9] scrollbar-track-transparent' : '' }}">
                                @foreach($attr->options as $index => $option)
                                
                                @if(strtolower($attr->name) == 'warna')
                                    <!-- Warna rendered as dense tags -->
                                    <label class="flex items-center gap-2 cursor-pointer group relative border border-[#d1cec9] hover:border-[#1c1c1a] px-2.5 py-1.5 transition-colors"
                                        :class="searchOption === '' ? '' : ('{{ strtolower(addslashes($option->value)) }}'.includes(searchOption.toLowerCase()) ? '' : 'hidden')"
                                        @if(!$isLarge) x-show="expanded || {{ $index }} < 10" style="display: {{ $index < 10 ? 'flex' : 'none' }};" @endif
                                    >
                                        <input type="checkbox" wire:model.live="selectedAttributes.{{ $attr->id }}" value="{{ $option->id }}" class="opacity-0 absolute w-0 h-0 peer">
                                        @if($option->meta && isset($option->meta['color']))
                                            <div class="w-3 h-3 shrink-0 border border-black/10 rounded-full" style="background-color: {{ $option->meta['color'] }};"></div>
                                        @endif
                                        <span class="text-[#1c1c1a] text-[9px] font-mono uppercase tracking-wider peer-checked:font-bold">{{ $option->value }}</span>
                                        <!-- absolute overlay to show it's checked with a border -->
                                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-[#1c1c1a] pointer-events-none transition-colors"></div>
                                    </label>
                                @else
                                    <!-- Default Filter Output -->
                                    <label class="flex items-center gap-3 cursor-pointer group relative" 
                                        @if($isLarge)
                                            x-show="searchOption === '' || '{{ strtolower(addslashes($option->value)) }}'.includes(searchOption.toLowerCase())"
                                        @else
                                            x-show="expanded || {{ $index }} < 5" style="display: {{ $index < 5 ? 'flex' : 'none' }};"
                                        @endif
                                    >
                                        <input type="checkbox" wire:model.live="selectedAttributes.{{ $attr->id }}" value="{{ $option->id }}" class="opacity-0 absolute w-0 h-0 peer">
                                        <div class="w-4 h-4 border border-[#d1cec9] peer-checked:bg-[#1c1c1a] peer-checked:border-[#1c1c1a] group-hover:border-[#1c1c1a] flex items-center justify-center transition-colors">
                                            <svg class="w-2.5 h-2.5 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="text-[#1c1c1a] text-[10px] font-mono uppercase tracking-widest peer-checked:font-bold">{{ $option->value }}</span>
                                    </label>
                                @endif

                                @endforeach
                            </div>
                            @if(!$isLarge && $attr->options->count() > 5)
                                <button @click="expanded = !expanded" class="text-[9px] text-[#615e57] hover:text-[#1c1c1a] font-mono font-bold tracking-widest mt-4 uppercase transition-colors flex items-center gap-1">
                                    <span x-text="expanded ? '- Sembunyikan' : '+ Lihat {{ $attr->options->count() - 5 }} Lainnya'"></span>
                                </button>
                            @endif
                        </div>
                        @endif
                    @endforeach

                    <!-- Harga -->
                    <div class="mb-10">
                        <h4 class="text-[#1c1c1a] text-[10px] font-mono tracking-[0.2em] uppercase mb-4 pb-2 border-b border-[#e5e2de]">Harga Maksimal</h4>
                        <div class="mt-6 px-1">
                            <input type="range" wire:model.live.debounce.250ms="maxPrice" min="200000" max="4000000" step="100000" class="w-full h-1 bg-[#d1cec9] appearance-none cursor-pointer accent-[#1c1c1a] focus:outline-none">
                            <div class="flex justify-between mt-4">
                                <span class="text-[#1c1c1a] text-[9px] font-mono tracking-widest uppercase">200k IDR</span>
                                <span class="text-[#064e3b] text-[10px] font-mono font-bold tracking-widest uppercase">Rp{{ number_format($maxPrice, 0, ',', '.') }} IDR</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Filters -->
                    <div class="mt-8 border-t border-[#e5e2de] pt-6">
                        <button wire:click="resetFilters" class="w-full py-3 border border-[#1c1c1a] text-[#1c1c1a] text-[10px] font-mono tracking-widest uppercase hover:bg-[#1c1c1a] hover:text-white transition-colors duration-300">
                            Reset Semua Filter
                        </button>
                    </div>
                    </div>

                    <!-- Sticky Apply Button (Now for both Mobile and Desktop Drawer) -->
                    <div class="sticky bottom-0 left-0 right-0 bg-[#fcf9f5] p-6 border-t border-[#e5e2de] z-20">
                        <button @click="mobileFilterOpen = false" class="w-full bg-[#1c1c1a] text-white text-[10px] font-mono font-bold tracking-[0.2em] uppercase py-4 hover:bg-[#333333] transition-colors">
                            Terapkan Filter
                        </button>
                    </div>
                </aside>

                <!-- Product Grid Area -->
                <div class="w-full">
                    
                    <!-- Top Bar Wrapper -->
                    <div x-data="{ 
                            isScrolled: false,
                            checkScroll() {
                                this.isScrolled = window.scrollY > this.$el.offsetTop + 100;
                            }
                         }" 
                         @scroll.window="checkScroll()"
                         class="w-full relative min-h-[120px] lg:min-h-[60px] mb-8"
                    >
                        <!-- Original Top Bar (Fades out when scrolled) -->
                        <div :class="isScrolled ? 'opacity-0 pointer-events-none' : 'opacity-100'" class="absolute inset-0 z-20 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-[#e5e2de] lg:border-none transition-opacity duration-300">
                            <!-- Toolbar -->
                            <div class="flex items-center gap-3 w-full lg:w-auto">
                                <button @click="mobileFilterOpen = true" class="flex items-center justify-center gap-2 px-4 py-2 bg-transparent border border-[#1c1c1a] text-[#1c1c1a] hover:bg-[#1c1c1a] hover:text-[#fcf9f5] transition-colors shrink-0 font-mono text-[10px] tracking-widest uppercase font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                    Filter Produk
                                </button>
                                
                                <div class="relative flex-1 lg:w-64">
                                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..." class="w-full border border-[#e5e2de] px-4 py-2 text-[10px] font-mono tracking-widest uppercase bg-[#fcf9f5] focus:outline-none focus:border-[#1c1c1a] transition-colors">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[#615e57]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                </div>
                                
                                <span class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest hidden lg:inline-block ml-3" wire:loading.class="opacity-50 transition-opacity">{{ $products->total() }} Produk</span>
                            </div>

                            <!-- Sort Dropdown -->
                            <div class="flex items-center gap-4 w-full lg:w-auto justify-between lg:justify-end">
                                <span class="text-[#615e57] text-[10px] font-mono uppercase tracking-widest hidden lg:inline-block">Urutkan:</span>
                                <div class="relative flex-1 lg:flex-none">
                                    <select wire:model.live="sort" class="w-full appearance-none bg-transparent border border-[#1c1c1a] px-4 py-2 pr-8 text-[10px] font-mono font-bold uppercase tracking-widest cursor-pointer focus:outline-none">
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

                        <!-- Floating Bottom Bar (Slides up when scrolled) -->
                        <div :class="isScrolled ? 'translate-y-0 opacity-100 pointer-events-auto' : 'translate-y-24 opacity-0 pointer-events-none'"
                             class="fixed bottom-4 lg:bottom-6 left-4 right-4 lg:left-1/2 lg:-translate-x-1/2 lg:right-auto lg:w-max z-[80] bg-[#fcf9f5]/95 backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.15)] border border-[#e5e2de] p-3 rounded-xl flex flex-row items-center justify-between gap-3 lg:gap-4 transition-all duration-500 ease-[cubic-bezier(0.23,1,0.32,1)]"
                        >
                            <button @click="mobileFilterOpen = true" class="flex flex-1 lg:flex-none justify-center items-center gap-2 px-4 py-2.5 bg-[#1c1c1a] text-[#fcf9f5] rounded-lg transition-colors font-mono text-[10px] tracking-widest uppercase font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                Filter
                            </button>
                            
                            <div class="relative flex-1 lg:w-64">
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari..." class="w-full bg-transparent border border-[#e5e2de] rounded-lg px-4 py-2.5 text-[10px] font-mono tracking-widest uppercase focus:outline-none focus:border-[#1c1c1a] transition-colors">
                            </div>
                            
                            <div class="relative flex-1 lg:flex-none">
                                <select wire:model.live="sort" class="w-full appearance-none bg-transparent border border-[#e5e2de] rounded-lg px-4 py-2.5 pr-8 text-[10px] font-mono font-bold uppercase tracking-widest cursor-pointer focus:outline-none focus:border-[#1c1c1a] transition-colors">
                                    <option value="default">Terbaru</option>
                                    <option value="price-low">Termurah</option>
                                    <option value="price-high">Termahal</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[#1c1c1a]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div id="products-grid" wire:loading.remove wire:target="search, selectedCategories, selectedSizes, selectedColors, maxPrice, sort" class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 lg:gap-x-6 gap-y-12 w-full mt-4">
                        @forelse ($products as $product)
                            <a href="{{ url('/product/' . $product->slug) }}" wire:navigate.hover class="group block">
                                <div class="aspect-[1/1] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    @if($product->discount_price !== null && $product->discount_price > 0 && !(auth()->check() && auth()->user()->hasRole('reseller')))
                                        <div class="absolute top-3 right-3 bg-[#b91c1c] text-white font-bold text-[10px] px-2.5 py-1 z-10 tracking-wider shadow-sm">-{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%</div>
                                    @endif
                                    <!-- Temporary placeholder image if product has no image yet -->
                                    <img width="1024" height="1024" src="{{ asset('assets/images/gallery-' . (rand(1, 3)) . '.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $product->name }}" />
                                    <div class="absolute bottom-3 right-3 z-10">
                                        <livewire:wishlist-toggle :product_id="$product->id" :key="'wishlist-shop-'.$product->id" />
                                    </div>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1.5 line-clamp-1">{{ $product->name }}</h3>
                                
                                <!-- Price Section -->
                                <div class="mb-2.5">
                                    @if($product->discount_price !== null && $product->discount_price > 0 && !(auth()->check() && auth()->user()->hasRole('reseller')))
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span class="text-[10px] text-[#9b9b9b] line-through">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                                            <div class="text-[13px] font-bold text-[#1c1c1a] tracking-wide">Rp{{ number_format($product->discount_price, 0, ',', '.') }}</div>
                                            <span class="text-[9px] font-bold text-[#b91c1c] bg-[#fee2e2] px-1 py-0.5 rounded-sm tracking-wider">-{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%</span>
                                        </div>
                                    @else
                                        <div class="text-[13px] font-bold text-[#1c1c1a] tracking-wide mb-1">Rp{{ number_format($product->effective_price, 0, ',', '.') }}</div>
                                    @endif
                                </div>

                                <!-- Promo & Voucher Labels -->
                                @php
                                    $globalPromos = \App\Models\Voucher::getGlobalPromoLabels();
                                @endphp
                                
                                @if($product->has_free_shipping || count($globalPromos) > 0)
                                    <div class="flex flex-wrap gap-1.5 mb-2.5">
                                        @if($product->has_free_shipping && !collect($globalPromos)->contains('type', 'shipping'))
                                            <span class="text-[8px] font-bold text-[#064e3b] bg-[#e6f4ea] px-1.5 py-0.5 rounded flex items-center gap-1 border border-[#064e3b]/20 uppercase tracking-widest">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                                Gratis Ongkir
                                            </span>
                                        @endif
                                        
                                        @foreach($globalPromos as $promo)
                                            <span class="text-[8px] font-bold {{ $promo['color'] }} {{ $promo['bg'] }} px-1.5 py-0.5 rounded flex items-center gap-1 border {{ $promo['border'] }} uppercase tracking-widest">
                                                @if($promo['icon']) {!! $promo['icon'] !!} @endif
                                                {{ $promo['text'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Rating & Sold Count -->
                                @if($product->effective_rating > 0 || $product->effective_sold_count > 0)
                                    <div class="flex items-center gap-1.5 text-[9px] text-[#615e57] uppercase tracking-widest font-mono">
                                        @if($product->effective_rating > 0)
                                            <div class="flex items-center text-[#eab308] gap-0.5">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                <span class="font-bold text-[#1c1c1a]">{{ number_format($product->effective_rating, 1, ',', '.') }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($product->effective_rating > 0 && $product->effective_sold_count > 0)
                                            <div class="w-0.5 h-0.5 rounded-full bg-[#1c1c1a]"></div>
                                        @endif
                                        
                                        @if($product->effective_sold_count > 0)
                                            @php
                                                $soldCount = $product->effective_sold_count;
                                                $soldText = $soldCount >= 1000 ? number_format($soldCount / 1000, 1, ',', '.') . 'rb' : $soldCount;
                                            @endphp
                                            <span>Terjual {{ $soldText }}</span>
                                        @endif
                                    </div>
                                @endif
                            </a>
                        @empty
                            <div class="col-span-2 lg:col-span-3 text-center py-24">
                                <p class="text-[#615e57] font-mono uppercase tracking-widest text-xs mb-4">Belum ada produk yang cocok dengan kriteria filter.</p>
                                <button wire:click="$set('search', '')" class="border border-[#1c1c1a] px-6 py-3 text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-[#f2efe8] transition-colors">Reset Filter</button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Skeleton Loading Grid -->
                    <div wire:loading wire:target="search, selectedCategories, selectedSizes, selectedColors, maxPrice, sort" class="w-full mt-4">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 lg:gap-x-6 gap-y-12">
                            @for ($i = 0; $i < 8; $i++)
                                <div class="animate-pulse block">
                                    <div class="aspect-[1/1] bg-[#e5e2de] mb-4"></div>
                                    <div class="h-3 bg-[#e5e2de] w-3/4 mb-2"></div>
                                    <div class="h-3 bg-[#e5e2de] w-1/4"></div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    @if($products->hasMorePages())
                        <div class="mt-12 flex justify-center pb-12">
                            <div x-data x-intersect="$wire.loadMore()" class="flex items-center gap-3 text-[#615e57]">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-[10px] font-mono uppercase tracking-widest">Memuat produk...</span>
                            </div>
                        </div>
                    @endif



                </div>
            </div>
        </section>

    </main>

