<div x-data="{ bsOpen: $wire.entangle('bsOpen'), bsMode: $wire.entangle('bsMode') }">
    @slot('header')
        <x-global.mobile-subnav title="Detail Produk" transparent="true" :cart="true" />
    @endslot

    <div id="product-detail-container" class="page-slide-in">
        <main class="bg-[#fcf9f5] min-h-screen pt-0 md:pt-12 pb-20">
        <div class="max-w-[1440px] mx-auto px-6 lg:px-12">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 mb-32">
                
                <!-- Left Column (Product Gallery) -->
                <div class="lg:col-span-7 relative">
                    
                    <!-- Gallery View (Unified Desktop & Mobile) -->
                    <div class="w-full" x-data="{ activeIndex: 0 }" @change-gallery-image.window="activeIndex = $event.detail.index">
                        <div class="w-[calc(100%+3rem)] -mx-6 md:mx-0 md:w-full aspect-square md:aspect-[4/5] lg:h-[65vh] lg:aspect-auto bg-[#ebebeb] overflow-hidden relative">
                            
                            <!-- Main Slider Container -->
                            <div class="flex w-full h-full transition-transform duration-500 ease-out" :style="'transform: translateX(-' + (activeIndex * 100) + '%)'">
                                @if(!empty($galleryUrls))
                                    @foreach($galleryUrls as $idx => $img)
                                        <div class="w-full h-full shrink-0">
                                            <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="w-full h-full shrink-0">
                                        <img src="{{ asset('assets/images/placeholder.webp') }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Badges Container (Inside Image on Mobile, Top Left on Desktop) -->
                            <div class="absolute bottom-4 left-4 md:bottom-auto md:top-6 md:left-6 z-40 flex flex-row md:flex-col gap-2 items-center md:items-start pointer-events-none">
                                <div class="bg-[#1c1c1a] text-white text-[9px] font-mono font-bold tracking-widest uppercase px-3 py-1 shadow-sm">
                                    NEW ARRIVAL
                                </div>
                                <div id="product-sale-badge" class="bg-[#09493B] text-white text-[9px] font-mono font-bold tracking-widest uppercase px-3 py-1 shadow-sm hidden">
                                    SALE!
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Wrapper -->
                        <div class="relative w-[calc(100%+3rem)] -mx-6 md:mx-0 md:w-full group mt-4 lg:mt-6">
                            <ol id="gallery-thumbnails" class="flex flex-nowrap overflow-x-auto gap-3 lg:gap-4 p-0 px-6 md:px-0 m-0 list-none scroll-smooth scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                                @if(!empty($galleryUrls))
                                    @foreach($galleryUrls as $idx => $img)
                                        <li @click="activeIndex = {{ $idx }}" 
                                            class="thumb-item relative shrink-0 cursor-pointer overflow-hidden bg-[#ebebeb] transition-all group" style="width: 20%; aspect-ratio: 4/5;">
                                            <img src="{{ $img }}" alt="Thumb" class="w-full h-full object-cover pointer-events-none">
                                            <div class="absolute inset-0 bg-[#064e3b]/30 transition-opacity duration-300" :class="activeIndex === {{ $idx }} ? 'opacity-100' : 'opacity-0 group-hover:opacity-50'"></div>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="thumb-item relative shrink-0 cursor-pointer overflow-hidden bg-[#ebebeb] transition-all" style="width: 20%; aspect-ratio: 4/5;">
                                        <img src="{{ asset('assets/images/placeholder.webp') }}" alt="Placeholder" class="w-full h-full object-cover pointer-events-none">
                                    </li>
                                @endif
                            </ol>
                            
                            <!-- Gallery navigation arrows -->
                            <div id="thumb-prev" class="absolute top-0 left-0 w-12 h-full text-[#1c1c1a] flex items-center justify-center cursor-pointer z-10 transition-colors bg-white/70 hover:bg-white/90 hidden">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </div>
                            <div id="thumb-next" class="absolute top-0 right-0 w-12 h-full text-[#1c1c1a] flex items-center justify-center cursor-pointer z-10 transition-colors bg-white/70 hover:bg-white/90 hidden">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>
                    </div>
    </div>
                <!-- Right Column (Product Info) -->
                <div class="lg:col-span-5">
                    <div class="summary entry-summary">
                        
                        <!-- Breadcrumb & Share -->
                        <div class="mb-8 hidden lg:flex justify-between items-center relative">
                            <nav class="text-[#615e57] text-[9px] font-mono uppercase tracking-widest inline-block">
                                <a href="index.html" class="hover:text-[#1c1c1a] transition-colors">BERANDA</a>
                                <span class="mx-2">/</span>
                                <a href="shop.html" class="hover:text-[#1c1c1a] transition-colors">KATALOG</a>
                                <span class="mx-2">/</span>
                                <span id="breadcrumb-category" class="text-[#1c1c1a]">{{ strtoupper($product->category->name ?? "KATEGORI") }}</span>
                            </nav>
                            
                            <!-- Share Button -->
                            <button type="button" id="desktop-share-btn" class="text-[#1c1c1a] hover:text-[#615e57] transition-colors focus:outline-none flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-100" onclick="document.getElementById('share-modal').classList.remove('hidden')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>
                        </div>
                        
                        <div class="lg:hidden flex items-center justify-between mb-2 mt-4">
                            <div class="text-[#615e57] text-[9px] font-mono uppercase tracking-widest" id="collection-badge">FALL/WINTER 2024</div>
                            <div class="flex items-center gap-1">
                                <!-- Mobile Share Button -->
                                <button type="button" onclick="document.getElementById('share-modal').classList.remove('hidden')" class="flex items-center justify-center w-8 h-8 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                </button>
                                <!-- Mobile Wishlist Button -->
                                <button type="button" class="flex items-center justify-center w-8 h-8 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </button>
                            </div>
                        </div>
                        <h1 id="product-name" class="text-[#1c1c1a] text-2xl lg:text-5xl font-serif font-bold tracking-tight mb-2 mt-0 lg:mt-0 capitalize">
                            {{ $product->name }}
                        </h1>
                        
                        <!-- Promo, Rating & Sold Count -->
                        <div class="flex flex-wrap items-center gap-3 mb-6 mt-3">
                            @if($product->effective_rating > 0 || $product->effective_sold_count > 0)
                                <div class="flex items-center gap-2 text-[10px] text-[#615e57] uppercase tracking-widest font-mono border-r border-[#e5e2de] pr-3">
                                    @if($product->effective_rating > 0)
                                        <div class="flex items-center text-[#eab308] gap-1">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            <span class="font-bold text-[#1c1c1a]">{{ number_format($product->effective_rating, 1, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    @if($product->effective_rating > 0 && $product->effective_sold_count > 0)
                                        <span class="text-[#d1cec9] px-0.5">•</span>
                                    @endif
                                    @if($product->effective_sold_count > 0)
                                        <span>Terjual {{ number_format($product->effective_sold_count, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            @endif

                            @php
                                $globalPromos = \App\Models\Voucher::getGlobalPromoLabels();
                            @endphp
                            
                            @if($product->has_free_shipping || count($globalPromos) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @if($product->has_free_shipping && !collect($globalPromos)->contains('type', 'shipping'))
                                        <span class="text-[9px] font-bold text-[#064e3b] bg-[#e6f4ea] px-2 py-0.5 rounded flex items-center gap-1 border border-[#064e3b]/20 uppercase tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                            Gratis Ongkir
                                        </span>
                                    @endif
                                    
                                    @foreach($globalPromos as $promo)
                                        <span class="text-[9px] font-bold {{ $promo['color'] }} {{ $promo['bg'] }} px-2 py-0.5 rounded flex items-center gap-1 border {{ $promo['border'] }} uppercase tracking-widest">
                                            @if($promo['icon']) {!! str_replace('w-2.5 h-2.5', 'w-3 h-3', $promo['icon']) !!} @endif
                                            {{ $promo['text'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <!-- Price -->
                        <div id="main-product-price" class="text-[#615e57] text-lg md:text-3xl font-serif mb-10 flex items-center gap-3 flex-wrap">
                            @if($this->currentPrice < $this->currentOriginalPrice)
                                <span class="font-bold text-[#064e3b]">Rp{{ number_format($this->currentPrice, 0, ",", ".") }}</span>
                                <span class="text-sm md:text-lg text-[#9b9b9b] line-through font-normal">Rp{{ number_format($this->currentOriginalPrice, 0, ",", ".") }}</span>
                            @else
                                <span>Rp{{ number_format($this->currentPrice, 0, ",", ".") }}</span>
                            @endif
                        </div>

                        <!-- Product Form (Add to Cart / Variation selectors) -->
                        <div class="w-full mb-8 pt-6 border-t border-[#e5e2de]">
                            <!-- Size & Color selectors (Desktop only on mobile, shown in bottomsheet) -->
                            <!-- Dynamic Selectors (Desktop only on mobile, shown in bottomsheet) -->
                            <div class="hidden md:block">
                                @foreach($this->availableAttributes as $attrSlug => $data)
                                    <div class="mb-6">
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase">{{ $data['name'] }}@if($data['type'] === 'color') : <span class="font-normal text-[#615e57]">{{ $selectedOptions[$attrSlug] ?? '' }}</span>@endif</label>
                                        </div>
                                        
                                        @if($data['type'] === 'color')
                                            <div class="flex flex-wrap gap-3">
                                                @foreach($data['options'] as $opt)
                                                    @php 
                                                        $val = $opt['value'];
                                                        $hex = $opt['meta'] ?? '#333333';
                                                        $isSelected = ($selectedOptions[$attrSlug] ?? '') === $val;
                                                        $isAvailable = $this->isOptionAvailable($attrSlug, $val);
                                                        $escapedVal = addslashes($val);
                                                    @endphp
                                                    <button 
                                                        type="button"
                                                        @if($isAvailable) wire:click="selectOption('{{ $attrSlug }}', '{{ $escapedVal }}')" @endif
                                                        class="flex flex-col items-center gap-1.5 group transition-all duration-200 {{ !$isAvailable ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                                        <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center p-0.5 transition-all duration-200 relative {{ $isSelected ? 'border-[#1c1c1a]' : ($isAvailable ? 'border-gray-300 hover:border-gray-500' : 'border-transparent') }}">
                                                            <div class="w-full h-full rounded-full border border-black/10 {{ !$isAvailable ? 'opacity-20' : '' }}" style="background-color: {{ $hex }}">
                                                            </div>
                                                            @if(!$isAvailable)
                                                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                                    <div class="w-[70%] h-[1.5px] bg-[#9e9a92] transform rotate-45"></div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <span class="text-[9px] font-mono uppercase tracking-wide transition-colors duration-200 {{ $isSelected ? 'text-[#1c1c1a] font-bold' : ($isAvailable ? 'text-[#615e57] group-hover:text-[#1c1c1a]' : 'text-[#c2bfb8] line-through font-light') }}">{{ $val }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($data['options'] as $opt)
                                                    @php 
                                                        $val = $opt['value'];
                                                        $isSelected = ($selectedOptions[$attrSlug] ?? '') === $val;
                                                        $isAvailable = $this->isOptionAvailable($attrSlug, $val);
                                                        $escapedVal = addslashes($val);
                                                    @endphp
                                                    <button 
                                                        type="button" 
                                                        @if($isAvailable) wire:click="selectOption('{{ $attrSlug }}', '{{ $escapedVal }}')" @endif
                                                        class="flex-1 py-3 px-2 text-[10px] font-mono border uppercase tracking-wider transition-all duration-200 relative overflow-hidden
                                                        {{ $isSelected ? 'border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold' : 'border-[#e5e2de] text-[#1c1c1a] ' . ($isAvailable ? 'hover:border-[#1c1c1a]' : 'opacity-40 bg-[#f9f8f6] text-[#a09e99] cursor-not-allowed') }}">
                                                        {{ $val }}
                                                        @if(!$isAvailable)
                                                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-40">
                                                                <div class="w-full h-[1px] bg-[#1c1c1a] transform -rotate-[20deg]"></div>
                                                            </div>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Add to Cart Widget -->
                            <div class="woocommerce-variation-add-to-cart variations_button w-full flex flex-col gap-3">
                                
                                <!-- Stock Status Row -->
                                <div class="text-[10px] font-mono tracking-widest text-[#615e57] uppercase mb-1 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $product->is_out_of_stock ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                    <span>STATUS STOK: </span>
                                    @if($product->has_variants)
                                        @if($this->getMatchedVariant())
                                            <span class="font-bold text-[#1c1c1a]">{{ $this->getMatchedVariant()->stock }} PCS TERSEDIA</span>
                                        @else
                                            <span class="font-bold text-[#1c1c1a]">{{ $product->total_stock }} PCS TERSEDIA (TOTAL SEMUA VARIAN)</span>
                                        @endif
                                    @else
                                        <span class="font-bold text-[#1c1c1a]">{{ $product->stock }} PCS TERSEDIA</span>
                                    @endif
                                </div>

                                <!-- Messages will be handled via JS/SweetAlert/Animation -->

                                @if (session()->has('error'))
                                    <div class="bg-red-900/10 border border-red-900 text-red-900 px-4 py-3 rounded text-[11px] font-mono tracking-widest mb-2">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <!-- Desktop Single Row: QTY + CTA + Wishlist -->
                                <div class="hidden md:flex items-center gap-3 w-full h-14">
                                    <!-- QTY -->
                                    <div class="flex items-center border border-[#e5e2de] w-28 shrink-0 h-full {{ $product->is_out_of_stock ? 'opacity-40 pointer-events-none' : '' }}">
                                        <button type="button" class="w-8 h-full flex items-center justify-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none" wire:click="decrementQuantity" @if($product->is_out_of_stock) disabled @endif>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                        </button>
                                        <input type="number" id="qty" wire:model.live="quantity" min="1" class="w-full text-center bg-transparent border-none focus:outline-none text-[#1c1c1a] font-mono text-[13px] appearance-none m-0" style="-moz-appearance: textfield;" @if($product->is_out_of_stock) disabled @endif>
                                        <button type="button" class="w-8 h-full flex items-center justify-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none" wire:click="incrementQuantity" @if($product->is_out_of_stock) disabled @endif>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>

                                    <!-- Add to Cart / Out of Stock CTA -->
                                    @if($product->is_out_of_stock)
                                        <button type="button" disabled class="flex-1 h-full bg-[#ebebeb] text-[#a09e99] flex items-center justify-center gap-3 border-none cursor-not-allowed focus:outline-none">
                                            <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">STOK HABIS</span>
                                        </button>
                                    @else
                                        <button type="button" wire:click="addToCart" id="desktop-add-to-cart-btn" class="flex-1 h-full bg-[#064e3b] text-white hover:bg-[#053e2f] flex items-center justify-center gap-3 border-none transition-colors focus:outline-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">TAMBAH KE KERANJANG</span>
                                        </button>
                                    @endif

                                    <!-- Wishlist (Icon Only) -->
                                    <livewire:wishlist-toggle :product_id="$product->id" :isDetail="true" :key="'wishlist-detail-'.$product->id" />
                                </div>
                            </div>
                        </div>
                        
                        

                        <div class="flex flex-col md:flex-row gap-6 mt-8 pt-8 border-t border-[#e5e2de] text-[9px] text-[#1c1c1a] font-mono tracking-widest uppercase">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                ALWAYS OPEN
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                AUTHENTICITY GUARANTEE
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <!-- Full Width Accordion Section -->
            <div class="max-w-4xl mx-auto px-4 lg:px-0 mt-24 md:mt-32 mb-32">
                <div class="border-y border-[#e5e2de] divide-y divide-[#e5e2de]">
                    
                    <!-- 01. Product Description -->
                    <div class="group py-6 product-accordion" x-data="{ open: true }">
                        <button @click="open = !open" type="button" class="w-full flex justify-between items-center text-[#1c1c1a] text-[13px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                            01. DESKRIPSI PRODUK
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="mt-6 text-[14px] leading-relaxed font-sans accordion-content">
                            <style>
                                .accordion-prose-colors * { color: #1c1c1a !important; }
                                .accordion-prose-colors li::marker { color: #1c1c1a !important; }
                            </style>
                            <div class="prose prose-sm max-w-none accordion-prose-colors prose-p:my-2 prose-li:my-1 prose-ul:my-2 prose-ol:my-2">
                                {!! $product->description !!}
                            </div>
                        </div>
                    </div>
                    
                    <!-- 02. Reviews & Ratings -->
                    <div class="group py-6 product-accordion" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="w-full flex justify-between items-center text-[#1c1c1a] text-[13px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                            02. REVIEW & RATING ({{ number_format($product->effective_rating, 1, ',', '.') }} ★)
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-collapse style="display: none;" class="mt-6 text-[#1c1c1a] font-sans accordion-content">
                            
                            <!-- Rating Overview Section -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 pb-8 border-b border-[#e5e2de]">
                                <!-- Overall Rating -->
                                <div class="flex flex-col items-center justify-center text-center p-6 bg-[#fcf9f5] border border-[#e5e2de]">
                                    <span class="text-5xl font-serif text-[#1c1c1a] mb-2">{{ number_format($product->effective_rating, 1, ',', '.') }}</span>
                                    <div class="flex gap-1 text-[#064e3b] mb-1">
                                        <!-- 5 Stars -->
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= round($product->effective_rating) ? 'fill-current' : 'text-[#d1cec9] fill-current' }}" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="text-[10px] font-mono tracking-widest text-[#615e57] uppercase">Berdasarkan {{ max($this->reviews->count(), $product->effective_rating > 0 ? 1 : 0) }} Ulasan</span>
                                </div>

                                <!-- Rating Bars -->
                                <div class="md:col-span-2 flex flex-col justify-center gap-3">
                                    @foreach($this->ratingDistribution as $star => $percentage)
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="w-12 flex items-center justify-end gap-1 text-[#615e57] font-mono">
                                            <span>{{ $star }}</span>
                                            <svg class="w-3 h-3 fill-current text-[#064e3b]" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                        <div class="flex-1 h-1.5 bg-[#e5e2de] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#064e3b] transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="w-8 text-left text-[#615e57] font-mono">{{ $percentage }}%</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- List of Reviews -->
                            <div class="space-y-6">
                                @forelse($this->reviews as $review)
                                    <div class="border-b border-[#e5e2de] pb-6 last:border-b-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-[#e5e2de] flex items-center justify-center text-[#1c1c1a] font-mono font-bold text-xs uppercase">
                                                    {{ substr($review->customer_name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <h4 class="text-xs font-semibold text-[#1c1c1a]">{{ $review->customer_name }}</h4>
                                                    <p class="text-[9px] text-[#615e57] font-mono">{{ $review->created_at->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-[#064e3b] fill-current' : 'text-[#d1cec9] fill-current' }}" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="text-xs text-[#525252] leading-relaxed pl-11">
                                            {{ $review->comment }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-xs text-[#525252] font-mono">Belum ada ulasan untuk produk ini.</p>
                                @endforelse
                            </div>

                            <!-- Write a Review Form -->
                            <div class="mt-8 pt-8 border-t border-[#e5e2de]">
                                
                                @if (session()->has('review_success'))
                                    <div class="bg-[#064e3b]/10 border border-[#064e3b]/20 text-[#064e3b] px-4 py-3 text-[11px] font-mono tracking-widest mb-4">
                                        {{ session('review_success') }}
                                    </div>
                                @elseif (session()->has('review_error'))
                                    <div class="bg-red-900/10 border border-red-900/20 text-red-900 px-4 py-3 text-[11px] font-mono tracking-widest mb-4">
                                        {{ session('review_error') }}
                                    </div>
                                @endif

                                @if($this->canReview && !$this->hasReviewed)
                                    <button type="button" wire:click="$toggle('showReviewForm')" class="border border-[#1c1c1a] px-6 py-3 text-[10px] font-mono font-bold uppercase tracking-[0.2em] hover:bg-[#f2efe8] text-[#1c1c1a] transition-colors focus:outline-none">
                                        {{ $showReviewForm ? 'BATALKAN' : 'TULIS ULASAN' }}
                                    </button>
                                    
                                    @if($showReviewForm)
                                        <form wire:submit="submitReview" class="mt-10 space-y-10 max-w-lg">
                                            <div class="flex flex-col">
                                                <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold block">RATING ANDA</label>
                                                <div class="flex gap-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <button type="button" wire:click="$set('reviewRating', {{ $i }})" class="focus:outline-none transition-transform hover:scale-110 {{ $reviewRating >= $i ? 'text-[#064e3b]' : 'text-[#d1cec9]' }} flex items-center justify-center">
                                                            <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                        </button>
                                                    @endfor
                                                </div>
                                                @error('reviewRating') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold block">NAMA LENGKAP</label>
                                                <input type="text" wire:model="reviewName" placeholder="ENTER YOUR NAME" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none font-mono uppercase text-xs" required>
                                                @error('reviewName') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold block">KOMENTAR / ULASAN</label>
                                                <textarea rows="4" wire:model="reviewComment" placeholder="WRITE YOUR REVIEW" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors resize-none rounded-none font-mono uppercase text-xs" required></textarea>
                                                @error('reviewComment') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <button type="submit" class="w-full bg-[#064e3b] text-white text-[11px] font-mono uppercase tracking-[0.2em] py-5 hover:bg-[#1c1c1a] transition-colors mt-4">
                                                KIRIM ULASAN
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 03. Wholesale & Promo -->
                    @if(!empty($product->wholesale_pricing) || !empty($product->promo_rules))
                    <div class="group py-6 product-accordion" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                            03. PROMO & GROSIR
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-collapse style="display: none;" class="mt-6 text-[#1c1c1a] font-sans accordion-content">
                            @if(!empty($product->wholesale_pricing))
                            <div class="mb-6">
                                <h4 class="text-[10px] font-mono font-bold tracking-widest uppercase mb-2">Aturan Harga Grosir</h4>
                                <div class="text-[14px] leading-relaxed font-sans p-4 bg-[#fcf9f5] border border-[#e5e2de] rounded-sm text-[#615e57]">
                                    {!! nl2br(e(is_array($product->wholesale_pricing) ? json_encode($product->wholesale_pricing) : $product->wholesale_pricing)) !!}
                                </div>
                            </div>
                            @endif
                            
                            @if(!empty($product->promo_rules))
                            <div>
                                <h4 class="text-[10px] font-mono font-bold tracking-widest uppercase mb-2">Aturan Promo</h4>
                                <div class="text-[14px] leading-relaxed font-sans p-4 bg-[#fcf9f5] border border-[#e5e2de] rounded-sm text-[#615e57]">
                                    {!! nl2br(e(is_array($product->promo_rules) ? json_encode($product->promo_rules) : $product->promo_rules)) !!}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Related Products -->
            <section class="border-t border-[#e5e2de] pt-20 mt-12">
                <h2 class="text-3xl lg:text-[2rem] font-serif tracking-[0.05em] text-[#1c1c1a] text-center mb-12 uppercase">
                    PRODUK TERKAIT
                </h2>
                
                <div class="relative">
                    <div id="related-products-track" class="flex md:grid md:grid-cols-4 gap-6 md:gap-8 overflow-x-auto md:overflow-x-visible pb-6 md:pb-0 scrollbar-none scroll-smooth" style="scrollbar-width: none; -ms-overflow-style: none;">
                        @foreach($this->relatedProducts as $related)
                        <div class="w-[calc(50%-12px)] md:w-auto shrink-0">
                            <a href="{{ url('/product/' . $related->slug) }}" class="group block">
                                <div class="w-full aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    @if($related->discount_price && $related->discount_price < $related->price)
                                        <div class="absolute top-3 right-3 bg-[#1a1a1a] text-white text-[9px] px-2 py-1 uppercase tracking-widest z-10">Sale</div>
                                    @endif
                                    
                                    @php
                                        $imgUrl = asset('assets/images/placeholder.webp');
                                        if (!empty($related->images)) {
                                            if (is_numeric($related->images[0])) {
                                                $media = \Awcodes\Curator\Models\Media::find($related->images[0]);
                                                if ($media && \Illuminate\Support\Facades\Storage::disk('public')->exists($media->path)) {
                                                    $imgUrl = $media->url;
                                                }
                                            } else {
                                                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($related->images[0])) {
                                                    $imgUrl = asset('storage/' . $related->images[0]);
                                                }
                                            }
                                        }
                                    @endphp
                                    <img width="1024" height="1024" src="{{ $imgUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $related->name }}" />                                                                
                                    
                                    <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </button>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1 truncate">{{ $related->name }}</h3>
                                <div class="text-[13px] text-[#525252]">
                                    @if($related->discount_price && $related->discount_price < $related->price)
                                        <span class="font-bold text-[#064e3b]">Rp{{ number_format($related->discount_price, 0, ",", ".") }}</span>
                                        <span class="line-through text-[#9b9b9b] ml-1">Rp{{ number_format($related->price, 0, ",", ".") }}</span>
                                    @else
                                        Rp{{ number_format($related->price, 0, ",", ".") }}
                                    @endif
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    <!-- Navigation Arrow Triggers for Mobile Slider -->
                    <div class="flex md:hidden justify-center gap-4 mt-6">
                        <button type="button" onclick="document.getElementById('related-products-track').scrollBy({ left: -window.innerWidth / 2, behavior: 'smooth' })" class="w-10 h-10 border border-[#e5e2de] text-[#1c1c1a] flex items-center justify-center rounded-full hover:bg-[#f2efe8] transition-colors focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" onclick="document.getElementById('related-products-track').scrollBy({ left: window.innerWidth / 2, behavior: 'smooth' })" class="w-10 h-10 border border-[#e5e2de] text-[#1c1c1a] flex items-center justify-center rounded-full hover:bg-[#f2efe8] transition-colors focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </section>

        </div>
    </main>
        
        
    </div>

    <!-- Share Modal Popup -->
    <div id="share-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
        <div class="bg-white w-11/12 max-w-md p-8 relative border border-[#e5e2de] shadow-2xl">
            <!-- Close button -->
            <button type="button" class="absolute top-4 right-4 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none" onclick="document.getElementById('share-modal').classList.add('hidden')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <h3 class="text-[#1c1c1a] text-lg font-serif font-bold mb-6 text-center">Share This Product</h3>
            
            <!-- Link Copy Box -->
            <div class="mb-6">
                <label class="block text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-2">Product Link</label>
                <div class="flex items-center border border-[#e5e2de] h-12 w-full">
                    <input type="text" value="https://raabiha.com/product/outerwear-asymmetrical" readonly class="flex-1 bg-[#f9f8f6] h-full px-4 text-[#615e57] font-mono text-[11px] focus:outline-none">
                    <button class="h-full px-4 border-l border-[#e5e2de] text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors flex items-center justify-center text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                        COPY
                    </button>
                </div>
            </div>

            <div class="border-t border-[#e5e2de] w-full mb-6"></div>

            <!-- Social Share Buttons -->
            <div class="flex flex-col gap-3">
                <button class="w-full h-12 border border-[#e5e2de] text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors flex items-center justify-center gap-3 focus:outline-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/></svg>
                    <span class="text-[10px] font-mono font-bold tracking-widest uppercase">Share to Facebook</span>
                </button>
                <button class="w-full h-12 border border-[#e5e2de] text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors flex items-center justify-center gap-3 focus:outline-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.94 2A10 10 0 1 0 21.94 12A10 10 0 0 0 11.94 2ZM16.39 9.32L14.75 16.39C14.62 16.92 14.32 17.06 13.88 16.82L11.48 15.05L10.32 16.17C10.19 16.3 10.08 16.41 9.8 16.41L9.98 13.95L14.45 9.91C14.65 9.73 14.41 9.63 14.15 9.8L8.62 13.27L6.23 12.52C5.71 12.36 5.7 12 6.34 11.75L15.68 8.15C16.12 7.99 16.49 8.24 16.39 9.32Z"/></svg>
                    <span class="text-[10px] font-mono font-bold tracking-widest uppercase">Share to Telegram</span>
                </button>
                <button class="w-full h-12 border border-[#e5e2de] text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors flex items-center justify-center gap-3 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span class="text-[10px] font-mono font-bold tracking-widest uppercase">Copy Text Only</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===== MOBILE VARIANT BOTTOMSHEET ===== -->
    <!-- ===== MOBILE VARIANT BOTTOMSHEET ===== -->
    <!-- Backdrop -->
    <div x-show="bsOpen" 
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="bsOpen = false" 
         class="md:hidden fixed inset-0 bg-black/50 z-[110]" style="display: none;"></div>

    <!-- Sheet Panel -->
    <div x-show="bsOpen" 
         x-transition:enter="transition-transform duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition-transform duration-300"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="md:hidden fixed bottom-0 left-0 right-0 z-[120] bg-[#fcf9f5] rounded-t-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.15)] transform translate-y-0" style="display: none;">
        <!-- Handle -->
        <div class="flex justify-center pt-3 pb-2" @click="bsOpen = false">
            <div class="w-10 h-1 bg-[#e5e2de] rounded-full cursor-pointer"></div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 pb-4 border-b border-[#e5e2de]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-16 bg-[#e5e2de] rounded-sm overflow-hidden shrink-0">
                    <img src="{{ $galleryUrls[0] ?? asset('assets/images/placeholder.webp') }}" class="w-full h-full object-cover">
                </div>
                <div>
                    <div id="bs-product-name" class="text-[#1c1c1a] text-sm font-serif font-bold uppercase leading-tight">{{ $product->name }}</div>
                    <div id="bs-product-price" class="text-[#615e57] text-xs font-mono mt-0.5 flex items-center gap-2">
                        @if($this->currentPrice < $this->currentOriginalPrice)
                            <span class="font-bold text-[#064e3b]">Rp{{ number_format($this->currentPrice, 0, ",", ".") }}</span>
                            <span class="line-through text-[#9b9b9b]">Rp{{ number_format($this->currentOriginalPrice, 0, ",", ".") }}</span>
                        @else
                            <span>Rp{{ number_format($this->currentPrice, 0, ",", ".") }}</span>
                        @endif
                    </div>
                    <div class="text-[10px] font-mono text-[#615e57] mt-1 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $product->is_out_of_stock ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                        <span>Stok: </span>
                        @if($product->has_variants)
                            @if($this->getMatchedVariant())
                                <span class="font-bold text-[#1c1c1a]">{{ $this->getMatchedVariant()->stock }} PCS</span>
                            @else
                                <span class="font-bold text-[#1c1c1a]">{{ $product->total_stock }} PCS (TOTAL)</span>
                            @endif
                        @else
                            <span class="font-bold text-[#1c1c1a]">{{ $product->stock }} PCS</span>
                        @endif
                    </div>
                </div>
            </div>
            <button type="button" @click="bsOpen = false" class="w-8 h-8 flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none self-start">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div id="mobile-bs-body" class="px-5 pt-4 pb-4 overflow-y-auto max-h-[55vh]">
            @foreach($this->availableAttributes as $attrSlug => $data)
                <div class="mb-5">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase">{{ $data['name'] }}@if($data['type'] === 'color') : <span class="font-normal text-[#615e57]">{{ $selectedOptions[$attrSlug] ?? '' }}</span>@endif</label>
                        @if(strtolower($data['name']) === 'ukuran' || strtolower($data['name']) === 'size')
                            <span class="text-[#615e57] text-[9px] font-mono uppercase tracking-widest cursor-pointer underline">SIZE GUIDE</span>
                        @endif
                    </div>
                    
                    @if($data['type'] === 'color')
                        <div class="flex flex-wrap gap-3">
                            @foreach($data['options'] as $opt)
                                @php 
                                    $val = $opt['value'];
                                    $hex = $opt['meta'] ?? '#333333';
                                    $isSelected = ($selectedOptions[$attrSlug] ?? '') === $val;
                                    $isAvailable = $this->isOptionAvailable($attrSlug, $val);
                                    $escapedVal = addslashes($val);
                                @endphp
                                <button type="button" 
                                    @if($isAvailable) wire:click="selectOption('{{ $attrSlug }}', '{{ $escapedVal }}')" @endif
                                    class="flex flex-col items-center gap-1.5 group transition-all duration-200 {{ !$isAvailable ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center p-0.5 transition-all duration-200 relative {{ $isSelected ? 'border-[#1c1c1a]' : ($isAvailable ? 'border-gray-300 hover:border-gray-500' : 'border-transparent') }}">
                                        <div class="w-full h-full rounded-full border border-black/10 {{ !$isAvailable ? 'opacity-20' : '' }}" style="background-color: {{ $hex }}">
                                        </div>
                                        @if(!$isAvailable)
                                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                                <div class="w-[70%] h-[1.5px] bg-[#9e9a92] transform rotate-45"></div>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-[9px] font-mono uppercase tracking-wide transition-colors duration-200 {{ $isSelected ? 'text-[#1c1c1a] font-bold' : ($isAvailable ? 'text-[#615e57] group-hover:text-[#1c1c1a]' : 'text-[#c2bfb8] line-through font-light') }}">{{ $val }}</span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($data['options'] as $opt)
                                @php 
                                    $val = $opt['value'];
                                    $isSelected = ($selectedOptions[$attrSlug] ?? '') === $val;
                                    $isAvailable = $this->isOptionAvailable($attrSlug, $val);
                                    $escapedVal = addslashes($val);
                                @endphp
                                <button type="button" 
                                    @if($isAvailable) wire:click="selectOption('{{ $attrSlug }}', '{{ $escapedVal }}')" @endif
                                    class="flex-1 py-3 px-2 text-[10px] font-mono border uppercase tracking-wider transition-all duration-200 relative overflow-hidden
                                    {{ $isSelected ? 'border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold' : 'border-[#e5e2de] text-[#1c1c1a] ' . ($isAvailable ? 'hover:border-[#1c1c1a]' : 'opacity-40 bg-[#f9f8f6] text-[#a09e99] cursor-not-allowed') }}">
                                    {{ $val }}
                                    @if(!$isAvailable)
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-40">
                                            <div class="w-full h-[1px] bg-[#1c1c1a] transform -rotate-[20deg]"></div>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="mb-2" id="bs-qty-selector">
                <label class="block text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-2">QUANTITY</label>
                <div class="flex items-center w-[120px] h-10 border border-[#e5e2de] {{ $product->is_out_of_stock ? 'opacity-40 pointer-events-none' : '' }}">
                    <button type="button" wire:click="decrementQuantity" class="w-10 h-full flex items-center justify-center text-[#615e57] hover:bg-[#f2efe8] transition-colors focus:outline-none" @if($product->is_out_of_stock) disabled @endif>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <div class="flex-1 h-full flex items-center justify-center font-mono text-[12px] text-[#1c1c1a] bg-transparent">{{ $quantity }}</div>
                    <button type="button" wire:click="incrementQuantity" class="w-10 h-full flex items-center justify-center text-[#615e57] hover:bg-[#f2efe8] transition-colors focus:outline-none" @if($product->is_out_of_stock) disabled @endif>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- CTA Footer -->
        <div class="px-5 pb-6 pt-3 border-t border-[#e5e2de]">
            @if($product->is_out_of_stock)
                <button type="button" disabled class="w-full h-14 bg-[#ebebeb] text-[#a09e99] flex items-center justify-center cursor-not-allowed focus:outline-none text-[10px] font-mono font-bold tracking-[0.2em] uppercase">
                    STOK HABIS
                </button>
            @else
                <button x-show="bsMode === 'cart'" type="button" wire:click="addToCart" id="mobile-bottomsheet-cart-btn" class="w-full h-14 bg-[#064e3b] text-white flex items-center justify-center transition-colors focus:outline-none text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#053e2f]">
                    + KERANJANG
                </button>
                <button x-show="bsMode === 'buy'" style="display: none;" type="button" wire:click="buyNow" class="w-full h-14 bg-[#064e3b] text-white text-[10px] font-mono font-bold tracking-[0.2em] uppercase flex items-center justify-center transition-colors hover:bg-[#053e2f] focus:outline-none">
                    BELI SEKARANG
                </button>
            @endif
        </div>
    </div>
    <!-- ===== END MOBILE VARIANT BOTTOMSHEET ===== -->

    <!-- MOBILE Sticky Action Bar -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white flex z-[99] border-t border-[#e5e2de] w-full h-[60px] pb-safe">
        <!-- WA Button -->
        <a id="mobile-wa-btn" href="#" target="_blank" class="w-[60px] shrink-0 h-full flex flex-col items-center justify-center border-r border-[#e5e2de] text-[#1c1c1a] hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="text-[8px] font-mono tracking-widest uppercase">Chat</span>
        </a>
        
        @if($product->is_out_of_stock)
            <!-- Out of Stock Cart Icon -->
            <button type="button" disabled class="w-[60px] shrink-0 h-full flex flex-col items-center justify-center border-r border-[#e5e2de] text-[#a09e99] bg-[#f9f8f6] cursor-not-allowed focus:outline-none">
                <div class="relative">
                    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span class="text-[8px] font-mono tracking-widest uppercase">Cart</span>
            </button>
            
            <!-- Out of Stock Buy Now Button -->
            <button type="button" disabled class="flex-1 h-full bg-[#ebebeb] text-[#a09e99] flex flex-col items-center justify-center cursor-not-allowed focus:outline-none">
                <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">STOK HABIS</span>
            </button>
        @else
            <!-- Cart Icon -->
            <button type="button" id="mobile-add-to-cart-btn" @click="bsOpen = true; bsMode = 'cart'" class="w-[60px] shrink-0 h-full flex flex-col items-center justify-center border-r border-[#e5e2de] text-[#1c1c1a] hover:bg-gray-50 transition-colors focus:outline-none">
                <div class="relative">
                    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span class="text-[8px] font-mono tracking-widest uppercase">Cart</span>
            </button>
            
            <!-- Buy Now Button -->
            <button type="button" id="mobile-buy-now-btn" @click="bsOpen = true; bsMode = 'buy'" class="flex-1 h-full bg-[#064e3b] text-white flex flex-col items-center justify-center hover:bg-[#053e2f] transition-colors focus:outline-none">
                <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">BELI SEKARANG</span>
            </button>
        @endif
    </div>

    <script data-navigate-once>
        function registerCartFlyAnimation() {
            if (window.raabihaCartFlyAnimationRegistered) return;
            window.raabihaCartFlyAnimationRegistered = true;

            Livewire.on('product-added-to-cart', () => {
                let activeImage = document.querySelector('.thumb-item .opacity-100')?.previousElementSibling;
                if (!activeImage) {
                    let sliderContainer = document.querySelector('.flex.w-full.h-full.transition-transform');
                    let activeIndex = sliderContainer?.__x?.getUnobservedData()?.activeIndex || 0;
                    activeImage = sliderContainer?.children[activeIndex]?.querySelector('img');
                    if(!activeImage) {
                        activeImage = document.querySelector('.thumb-item img');
                    }
                }
                
                let targetIcon = window.innerWidth < 768 ? document.querySelector('#mobile-add-to-cart-btn') : document.querySelector('#desktop-cart-icon');
                let originElement = window.innerWidth < 768 ? document.querySelector('#bs-qty-selector') : document.querySelector('#qty'); // QTY input as origin
                
                if (activeImage && targetIcon && originElement) {
                    let imgClone = activeImage.cloneNode();
                    let originRect = originElement.getBoundingClientRect();
                    let targetRect = targetIcon.getBoundingClientRect();
                    
                    imgClone.style.position = 'fixed';
                    // Center the image over the QTY input initially
                    imgClone.style.left = (originRect.left + originRect.width / 2 - 20) + 'px';
                    imgClone.style.top = (originRect.top + originRect.height / 2 - 20) + 'px';
                    imgClone.style.width = '40px';
                    imgClone.style.height = '40px';
                    imgClone.style.objectFit = 'cover';
                    imgClone.style.zIndex = '9999';
                    imgClone.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    imgClone.style.borderRadius = '50%';
                    imgClone.style.opacity = '0.9';
                    
                    document.body.appendChild(imgClone);
                    
                    setTimeout(() => {
                        imgClone.style.left = (targetRect.left + targetRect.width / 2 - 10) + 'px';
                        imgClone.style.top = (targetRect.top + targetRect.height / 2 - 10) + 'px';
                        imgClone.style.width = '20px';
                        imgClone.style.height = '20px';
                        imgClone.style.opacity = '0';
                        imgClone.style.transform = 'scale(0.1) rotate(180deg)';
                    }, 50);
                    
                    setTimeout(() => {
                        imgClone.remove();
                    }, 850);
                }
            });
        }

        if (window.Livewire) {
            registerCartFlyAnimation();
        } else {
            document.addEventListener('livewire:initialized', registerCartFlyAnimation);
        }
    </script>
</div>
