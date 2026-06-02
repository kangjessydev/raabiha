<x-layouts.app>
    <x-slot:header>
        <x-global.mobile-subnav title="Detail Produk" backUrl="/shop" transparent="true" :share="true" />
    </x-slot:header>

    <div id="product-detail-container" class="page-slide-in">
        <main class="bg-[#fcf9f5] min-h-screen pt-0 md:pt-12 pb-20">
        <div class="max-w-[1440px] mx-auto px-6 lg:px-12">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 mb-32">
                
                <!-- Left Column (Product Gallery) -->
                <div class="lg:col-span-7 relative">
                    
                    <!-- Gallery View (Unified Desktop & Mobile) -->
                    <div class="w-full">
                        <div class="w-[calc(100%+3rem)] -mx-6 md:mx-0 md:w-full aspect-square md:aspect-[4/5] lg:h-[65vh] lg:aspect-auto bg-[#ebebeb] overflow-hidden relative">
                            <img id="main-gallery-image" src="" alt="Detail Produk" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300 ease-in-out opacity-100" onerror="this.onerror=null; this.src='{{ asset('/assets/images/gallery-1.png') }}';">
                            
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
                                <!-- Dynamically Rendered -->
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
                                <span id="breadcrumb-category" class="text-[#1c1c1a]">OUTERWEAR</span>
                            </nav>
                            
                            <!-- Share Button -->
                            <button type="button" id="desktop-share-btn" class="text-[#1c1c1a] hover:text-[#615e57] transition-colors focus:outline-none flex items-center justify-center w-8 h-8 rounded-full hover:bg-gray-100" onclick="document.getElementById('share-modal').classList.remove('hidden')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>
                        </div>
                        
                        <div class="lg:hidden flex items-center justify-between mb-2 mt-4">
                            <div class="text-[#615e57] text-[9px] font-mono uppercase tracking-widest" id="collection-badge">FALL/WINTER 2024</div>
                            <!-- Mobile Wishlist Button -->
                            <button type="button" class="flex items-center justify-center w-8 h-8 text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </div>
                        <h1 id="product-name" class="text-[#1c1c1a] text-2xl lg:text-5xl font-serif font-bold tracking-tight mb-2 mt-0 lg:mt-0 capitalize">
                            Nama Produk
                        </h1>
                        
                        <!-- Price -->
                        <div id="main-product-price" class="text-[#615e57] text-lg md:text-3xl font-serif mb-10">
                            Rp0
                        </div>

                        <!-- Product Form (Add to Cart / Variation selectors) -->
                        <div class="w-full mb-8 pt-6 border-t border-[#e5e2de]">
                            <!-- Size & Color selectors (Desktop only on mobile, shown in bottomsheet) -->
                            <div class="hidden md:block">
                                <!-- Size Select -->
                                <div class="mb-6" id="size-selector-container">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase">SELECT SIZE</label>
                                        <span class="text-[#615e57] text-[9px] font-mono uppercase tracking-widest cursor-pointer underline">SIZE GUIDE</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2" id="size-options-grid">
                                        <!-- Dynamic sizes -->
                                    </div>
                                </div>
                                
                                <!-- Color Select -->
                                <div class="mb-8" id="color-selector-container">
                                    <label class="block text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-2">Color: <span id="selected-color-label" class="font-normal text-[#615e57]"></span></label>
                                    <div class="flex flex-wrap gap-3" id="color-options-grid">
                                        <!-- Dynamic colors -->
                                    </div>
                                </div>
                            </div>

                            <!-- Add to Cart Widget -->
                            <div class="woocommerce-variation-add-to-cart variations_button w-full flex flex-col gap-3">
                                
                                <!-- Desktop Single Row: QTY + CTA + Wishlist -->
                                <div class="hidden md:flex items-center gap-3 w-full h-14">
                                    <!-- QTY -->
                                    <div class="flex items-center border border-[#e5e2de] w-28 shrink-0 h-full">
                                        <button type="button" class="w-8 h-full flex items-center justify-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none" onclick="document.getElementById('qty').stepDown()">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                        </button>
                                        <input type="number" id="qty" value="1" min="1" class="w-full text-center bg-transparent border-none focus:outline-none text-[#1c1c1a] font-mono text-[13px] appearance-none m-0" style="-moz-appearance: textfield;">
                                        <button type="button" class="w-8 h-full flex items-center justify-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none" onclick="document.getElementById('qty').stepUp()">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>

                                    <!-- Add to Cart -->
                                    <button type="button" id="desktop-add-to-cart-btn" class="flex-1 h-full bg-[#064e3b] text-white hover:bg-[#053e2f] flex items-center justify-center gap-3 border-none transition-colors focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">TAMBAH KE KERANJANG</span>
                                    </button>

                                    <!-- Wishlist (Icon Only) -->
                                    <button class="w-14 shrink-0 h-full border border-[#e5e2de] text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors flex justify-center items-center focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        

                        <div class="flex flex-col md:flex-row gap-6 mt-8 pt-8 border-t border-[#e5e2de] text-[9px] text-[#1c1c1a] font-mono tracking-widest uppercase">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                FREE SHIPPING ON ORDERS OVER 500K
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
                    <div class="group py-6 product-accordion">
                        <button class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                            01. PRODUCT DESCRIPTION
                            <svg class="w-4 h-4 transition-transform duration-300 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="accordion-desc" class="mt-6 text-[#1c1c1a] text-[14px] leading-relaxed font-sans accordion-content">
                            <!-- Dynamic Content -->
                        </div>
                    </div>
                    
                    <!-- 02. Reviews & Ratings -->
                    <div class="group py-6 product-accordion">
                        <button class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                            02. REVIEWS & RATINGS (4.9 ★)
                            <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="mt-6 text-[#1c1c1a] font-sans hidden accordion-content">
                            
                            <!-- Rating Overview Section -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 pb-8 border-b border-[#e5e2de]">
                                <!-- Overall Rating -->
                                <div class="flex flex-col items-center justify-center text-center p-6 bg-[#fcf9f5] border border-[#e5e2de]">
                                    <span class="text-5xl font-serif text-[#1c1c1a] mb-2">4.9</span>
                                    <div class="flex gap-1 text-[#064e3b] mb-1">
                                        <!-- 5 Stars -->
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                    </div>
                                    <span class="text-[10px] font-mono tracking-widest text-[#615e57] uppercase">Berdasarkan 18 Ulasan</span>
                                </div>

                                <!-- Rating Bars -->
                                <div class="md:col-span-2 flex flex-col justify-center gap-3">
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="w-12 flex items-center justify-end gap-1 text-[#615e57] font-mono">
                                            <span>5</span>
                                            <svg class="w-3 h-3 fill-current text-[#064e3b]" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                        <div class="flex-1 h-1.5 bg-[#e5e2de] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#064e3b]" style="width: 90%"></div>
                                        </div>
                                        <span class="w-8 text-left text-[#615e57] font-mono">90%</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="w-12 flex items-center justify-end gap-1 text-[#615e57] font-mono">
                                            <span>4</span>
                                            <svg class="w-3 h-3 fill-current text-[#064e3b]" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                        <div class="flex-1 h-1.5 bg-[#e5e2de] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#064e3b]" style="width: 10%"></div>
                                        </div>
                                        <span class="w-8 text-left text-[#615e57] font-mono">10%</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="w-12 flex items-center justify-end gap-1 text-[#615e57] font-mono">
                                            <span>3</span>
                                            <svg class="w-3 h-3 fill-current text-[#064e3b]" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                        <div class="flex-1 h-1.5 bg-[#e5e2de] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#064e3b]" style="width: 0%"></div>
                                        </div>
                                        <span class="w-8 text-left text-[#615e57] font-mono">0%</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="w-12 flex items-center justify-end gap-1 text-[#615e57] font-mono">
                                            <span>2</span>
                                            <svg class="w-3 h-3 fill-current text-[#064e3b]" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                        <div class="flex-1 h-1.5 bg-[#e5e2de] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#064e3b]" style="width: 0%"></div>
                                        </div>
                                        <span class="w-8 text-left text-[#615e57] font-mono">0%</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="w-12 flex items-center justify-end gap-1 text-[#615e57] font-mono">
                                            <span>1</span>
                                            <svg class="w-3 h-3 fill-current text-[#064e3b]" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                        <div class="flex-1 h-1.5 bg-[#e5e2de] rounded-full overflow-hidden">
                                            <div class="h-full bg-[#064e3b]" style="width: 0%"></div>
                                        </div>
                                        <span class="w-8 text-left text-[#615e57] font-mono">0%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- List of Reviews -->
                            <div class="space-y-6">
                                <!-- Review Item 1 -->
                                <div class="border-b border-[#e5e2de] pb-6 last:border-b-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#e5e2de] flex items-center justify-center text-[#1c1c1a] font-mono font-bold text-xs uppercase">
                                                SH
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-semibold text-[#1c1c1a]">Siti Humaira</h4>
                                                <p class="text-[9px] text-[#615e57] font-mono">28 Mei 2026</p>
                                            </div>
                                        </div>
                                        <div class="flex text-[#064e3b] gap-0.5">
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-[#525252] leading-relaxed pl-11">
                                        Bahannya luar biasa premium, sangat adem dan jahitannya super rapi. Potongannya asimetris dan pas sekali dipakai untuk acara formal maupun santai. Sangat *recommended*!
                                    </p>
                                </div>

                                <!-- Review Item 2 -->
                                <div class="border-b border-[#e5e2de] pb-6 last:border-b-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#e5e2de] flex items-center justify-center text-[#1c1c1a] font-mono font-bold text-xs uppercase">
                                                RA
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-semibold text-[#1c1c1a]">Rina Amalia</h4>
                                                <p class="text-[9px] text-[#615e57] font-mono">15 Mei 2026</p>
                                            </div>
                                        </div>
                                        <div class="flex text-[#064e3b] gap-0.5">
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </div>
                                    </div>
                                    <p class="text-xs text-[#525252] leading-relaxed pl-11">
                                        Desainnya sangat elegan dan modern. Meskipun longgar (*modest*), siluetnya tetap terlihat rapi dan berkelas. Sangat puas dengan kualitasnya.
                                    </p>
                                </div>
                            </div>

                            <!-- Write a Review Form -->
                            <div class="mt-8 pt-8 border-t border-[#e5e2de]">
                                <button type="button" id="toggle-review-btn" class="border border-[#1c1c1a] px-6 py-3 text-[10px] font-mono font-bold uppercase tracking-[0.2em] hover:bg-[#f2efe8] text-[#1c1c1a] transition-colors focus:outline-none">
                                    TULIS ULASAN
                                </button>
                                
                                <form id="write-review-form" class="hidden mt-10 space-y-10 max-w-lg">
                                    <div class="flex flex-col">
                                        <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold block">RATING ANDA</label>
                                        <div class="flex gap-2 text-[#d1cec9]" id="rating-stars-input">
                                            <button type="button" data-rating="1" class="focus:outline-none transition-transform hover:scale-110 text-[#d1cec9] flex items-center justify-center">
                                                <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            </button>
                                            <button type="button" data-rating="2" class="focus:outline-none transition-transform hover:scale-110 text-[#d1cec9] flex items-center justify-center">
                                                <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            </button>
                                            <button type="button" data-rating="3" class="focus:outline-none transition-transform hover:scale-110 text-[#d1cec9] flex items-center justify-center">
                                                <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            </button>
                                            <button type="button" data-rating="4" class="focus:outline-none transition-transform hover:scale-110 text-[#d1cec9] flex items-center justify-center">
                                                <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            </button>
                                            <button type="button" data-rating="5" class="focus:outline-none transition-transform hover:scale-110 text-[#d1cec9] flex items-center justify-center">
                                                <svg class="w-6 h-6 fill-current pointer-events-none" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold block">NAMA LENGKAP</label>
                                        <input type="text" placeholder="ENTER YOUR NAME" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors rounded-none font-mono uppercase text-xs" required>
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#1c1c1a] mb-3 font-bold block">KOMENTAR / ULASAN</label>
                                        <textarea rows="4" placeholder="WRITE YOUR REVIEW" class="w-full bg-transparent border-b border-[#e5e2de] pb-3 text-sm text-[#1c1c1a] placeholder:text-[#a09e99] focus:outline-none focus:border-[#064e3b] transition-colors resize-none rounded-none font-mono uppercase text-xs" required></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-[#064e3b] text-white text-[11px] font-mono uppercase tracking-[0.2em] py-5 hover:bg-[#1c1c1a] transition-colors mt-4">
                                        KIRIM ULASAN
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Related Products -->
            <section class="border-t border-[#e5e2de] pt-20 mt-12">
                <h2 class="text-3xl lg:text-[2rem] font-serif tracking-[0.05em] text-[#1c1c1a] text-center mb-12 uppercase">
                    RELATED PRODUCTS
                </h2>
                
                <div class="relative group">
                    <div id="related-products-track" class="flex md:grid md:grid-cols-4 gap-6 md:gap-8 overflow-x-auto md:overflow-x-visible pb-6 md:pb-0 scrollbar-none scroll-smooth" style="scrollbar-width: none; -ms-overflow-style: none;">
                        
                        <!-- Product 1 -->
                        <div class="w-[calc(50%-12px)] md:w-auto shrink-0">
                            <a href="{{ url('/product/asymmetrical-tunic') }}" wire:navigate class="group block">
                                <div class="w-full aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    <img width="1024" height="1024" src="{{ asset('assets/images/gallery-3.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Asymmetrical Tunic" />                                                                
                                    <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </button>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">ASYMMETRICAL TUNIC</h3>
                                <div class="text-[13px] text-[#525252]">Rp750.000 - Rp950.000</div>
                            </a>
                        </div>

                        <!-- Product 2 -->
                        <div class="w-[calc(50%-12px)] md:w-auto shrink-0">
                            <a href="{{ url('/product/kimono-structural-parka') }}" wire:navigate class="group block">
                                <div class="w-full aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    <img width="1024" height="1024" src="{{ asset('assets/images/gallery-1.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Kimono Structural Parka" />                                                                
                                    <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </button>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">KIMONO STRUCTURAL PARKA</h3>
                                <div class="text-[13px] text-[#525252]">Rp1.499.000</div>
                            </a>
                        </div>

                        <!-- Product 3 -->
                        <div class="w-[calc(50%-12px)] md:w-auto shrink-0">
                            <a href="{{ url('/product/monolith-overcoat') }}" wire:navigate class="group block">
                                <div class="w-full aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    <img width="1024" height="1024" src="{{ asset('assets/images/blog-coat.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Monolith Overcoat" />                                                                
                                    <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </button>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">MONOLITH OVERCOAT</h3>
                                <div class="text-[13px] text-[#525252]">Rp2.850.000</div>
                            </a>
                        </div>

                        <!-- Product 4 -->
                        <div class="w-[calc(50%-12px)] md:w-auto shrink-0">
                            <a href="{{ url('/product/modest-urban-coat') }}" wire:navigate class="group block">
                                <div class="w-full aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                    <div class="absolute top-3 right-3 bg-[#1a1a1a] text-white text-[9px] px-2 py-1 uppercase tracking-widest z-10">Sale</div>
                                    <img width="1024" height="1024" src="{{ asset('assets/images/blog-coat.png') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Modest Urban Coat" />                                                                
                                    <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    </button>
                                </div>
                                <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">MODEST URBAN COAT</h3>
                                <div class="text-[13px] text-[#525252]">Rp3.100.000</div>
                            </a>
                        </div>

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
        
        <script>
        const products = {
            'asymmetrical-tunic': {
                id: 'asymmetrical-tunic',
                name: 'ASYMMETRICAL TUNIC',
                price_html: 'Rp750.000 - Rp950.000',
                price_min: 750000,
                price_max: 950000,
                badge: 'SALE',
                badge_bg: '#1c1c1a',
                badge_color: '#ffffff',
                images: [
                    '/assets/images/gallery-3.png',
                    '/assets/images/prod_wrap_1779445244495.png',
                    '/assets/images/gallery-4.png',
                    '/assets/images/gallery-5.png'
                ],
                category: 'Gamis',
                collection: 'FALL/WINTER 2024',
                description: 'Tunika asimetris premium dengan drapery elegan yang mengalir indah. Dibuat dengan material sutra berkualitas tinggi, potongan asimetris unik di bagian bawah memberikan siluet arsitektural modern yang dinamis namun tetap anggun dan modest.',
                shipping_care: 'Pengiriman standar memakan waktu 3-5 hari kerja.<br>Hanya dicuci kering (dry clean). Jangan menggunakan mesin pengering.',
                journal: 'Koleksi ini terinspirasi dari geometri arsitektur brutalist, di mana bentuk-bentuk tegas bertemu dengan kelembutan material drape.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand', 'Dusty Rose'],
                    sizes: ['XS/S', 'M/L', 'Oversized']
                }
            },
            'kimono-structural-parka': {
                id: 'kimono-structural-parka',
                name: 'KIMONO STRUCTURAL PARKA',
                price_html: 'Rp1.499.000',
                price_min: 1499000,
                price_max: 1499000,
                badge: 'NEW',
                badge_bg: '#e0d6cd',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/gallery-1.png',
                    '/assets/images/prod_outer_1779445195829.png',
                    '/assets/images/gallery-2.png'
                ],
                category: 'Outerwear',
                collection: 'ESSENTIALS',
                description: 'Parka berstruktur dengan kerah gaya kimono tradisional Jepang yang diadaptasi untuk tampilan urban modern. Dilengkapi dengan kantong utilitas tak terlihat dan material tahan air ringan.',
                shipping_care: 'Dicuci dengan tangan menggunakan air dingin.<br>Jangan gunakan pemutih. Setrika suhu rendah jika diperlukan.',
                journal: 'Eksperimen menyatukan pakaian tradisional Asia Timur dengan fungsionalitas jaket teknis perkotaan.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand', 'Off-White'],
                    sizes: ['M/L', 'Uni-Size']
                }
            },
            'monolith-overcoat': {
                id: 'monolith-overcoat',
                name: 'MONOLITH OVERCOAT',
                price_html: 'Rp2.850.000',
                price_min: 2850000,
                price_max: 2850000,
                badge: 'NEW ARRIVAL',
                badge_bg: '#1c1c1a',
                badge_color: '#ffffff',
                images: [
                    '/assets/images/blog-coat.png',
                    '/assets/images/prod_abaya_1779445230398.png',
                    '/assets/images/gallery-5.png'
                ],
                category: 'Outerwear',
                collection: 'FALL/WINTER 2024',
                description: 'The Monolith Overcoat is a testament to RAABIHA\'s architectural approach to modesty. Crafted from heavy-weight Boiled Wool, this piece creates a protective yet fluid silhouette that challenges traditional outerwear structures.<br><br>• 100% Premium Boiled Wool construction<br>• Exaggerated structural shoulders<br>• Hidden button placket for a seamless finish<br>• Interior pockets for tech utility<br>• Hand-finished editorial lines and seams',
                shipping_care: 'Dry clean only.<br>Store using a wide padded suit hanger.',
                journal: 'Exploring the monolithic concept in modest fashion - a single outer layer of robust yet unassuming protection.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand', 'Green'],
                    sizes: ['M', 'OVERSIZED', 'L']
                }
            },
            'raw-edge-box-tee': {
                id: 'raw-edge-box-tee',
                name: 'RAW EDGE BOX TEE',
                price_html: 'Rp449.000',
                price_min: 449000,
                price_max: 449000,
                badge: 'NEW',
                badge_bg: '#e0d6cd',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/gallery-3.png',
                    '/assets/images/gallery-4.png'
                ],
                category: 'Essential Tees',
                collection: 'ESSENTIALS',
                description: 'Kaos bersiluet kotak (boxy fit) dengan detail raw edge di bagian keliman bawah dan lengan. Menggunakan material katun organik 24s heavy-weight yang nyaman dan jatuh dengan sempurna.',
                shipping_care: 'Cuci dengan mesin dingin dengan warna serupa.<br>Jangan gunakan mesin pengering. Setrika bagian dalam.',
                journal: 'Menciptakan kaos dasar yang tidak membosankan dengan fokus penuh pada proporsi boxy dan detail jahitan tepi kasar.',
                variations: {
                    colors: ['Charcoal', 'Off-White', 'Slate Sand'],
                    sizes: ['XS/S', 'M/L', 'Oversized']
                }
            },
            'geometric-cargo-pants': {
                id: 'geometric-cargo-pants',
                name: 'GEOMETRIC CARGO PANTS',
                price_html: 'Rp1.120.000',
                price_min: 1120000,
                price_max: 1120000,
                badge: 'NEW',
                badge_bg: '#e0d6cd',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/gallery-2.png',
                    '/assets/images/gallery-1.png'
                ],
                category: 'Modest Bottoms',
                collection: 'ESSENTIALS',
                description: 'Celana kargo bersiluet rileks dengan penempatan saku geometris yang unik. Menawarkan kenyamanan bergerak maksimal berkat material kanvas katun lentur berkualitas tinggi.',
                shipping_care: 'Cuci terbalik menggunakan mesin.<br>Setrika suhu sedang.',
                journal: 'Memindahkan saku kargo tradisional ke posisi geometris baru yang memecah siluet lurus kaki.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand'],
                    sizes: ['XS/S', 'M/L', 'Uni-Size']
                }
            },
            'column-maxi-dress': {
                id: 'column-maxi-dress',
                name: 'COLUMN MAXI DRESS',
                price_html: 'Rp1.890.000',
                price_min: 1890000,
                price_max: 1890000,
                badge: 'BESTSELLER',
                badge_bg: '#d9a596',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/blog-white-suit.png',
                    '/assets/images/prod_set_1779445214046.png',
                    '/assets/images/gallery-5.png'
                ],
                category: 'Gamis',
                collection: 'FALL/WINTER 2024',
                description: 'Gamis kolom minimalis dengan potongan vertikal bersih yang memperpanjang siluet tubuh. Dibuat dari rajutan viscose premium bertekstur jatuh yang sangat lembut dan sejuk di kulit.',
                shipping_care: 'Hanya dry clean atau cuci tangan lembut.<br>Jangan diperas keras. Jemur mendatar.',
                journal: 'Inspirasi pilar kuil klasik Romawi yang dituangkan ke dalam bentuk gaun kolom lurus berbahan jatuh.',
                variations: {
                    colors: ['Off-White', 'Dusty Rose', 'Slate Sand'],
                    sizes: ['XS/S', 'M/L']
                }
            },
            'sculpted-hoodie-02': {
                id: 'sculpted-hoodie-02',
                name: 'SCULPTED HOODIE 02',
                price_html: 'Rp899.000',
                price_min: 899000,
                price_max: 899000,
                badge: 'NEW',
                badge_bg: '#e0d6cd',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/blog-objects.png',
                    '/assets/images/blog-hero.png'
                ],
                category: 'Outerwear',
                collection: 'ESSENTIALS',
                description: 'Hoodie bersiluet sculpted (terstruktur melengkung) dengan tudung kepala dobel besar dan detail jahitan panel melingkar. Material fleece premium ultra-soft berpemberat tinggi.',
                shipping_care: 'Cuci dengan mesin dingin.<br>Balikkan pakaian sebelum dicuci. Hindari pemutih.',
                journal: 'Mengubah hoodie olahraga biasa menjadi karya seni pakai melalui konstruksi panel 3D melengkung.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand', 'Off-White'],
                    sizes: ['XS/S', 'M/L', 'Oversized']
                }
            },
            'utility-beanie': {
                id: 'utility-beanie',
                name: 'UTILITY BEANIE',
                price_html: 'Rp299.000',
                price_min: 299000,
                price_max: 299000,
                badge: 'NEW',
                badge_bg: '#e0d6cd',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/blog-hero.png',
                    '/assets/images/gallery-1.png'
                ],
                category: 'Headwear',
                collection: 'ESSENTIALS',
                description: 'Beanie rajut rib tebal fungsional dengan anyaman benang akrilik premium tahan lama. Memberikan kehangatan sempurna dengan kenyamanan elastisitas kepala yang pas.',
                shipping_care: 'Cuci dengan tangan lembut.<br>Jangan diperas kencang. Jemur mendatar.',
                journal: 'Topi rajut serbaguna yang dirancang untuk melengkapi setelan outerwear perkotaan.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand', 'Off-White'],
                    sizes: ['Uni-Size']
                }
            },
            'field-shell-jacket': {
                id: 'field-shell-jacket',
                name: 'FIELD SHELL JACKET',
                price_html: 'Rp2.150.000',
                price_min: 2150000,
                price_max: 2150000,
                badge: 'NEW',
                badge_bg: '#e0d6cd',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/gallery-1.png',
                    '/assets/images/prod_outer_1779445195829.png'
                ],
                category: 'Outerwear',
                collection: 'ESSENTIALS',
                description: 'Jaket cangkang pelindung lapangan dengan tudung kepala badai yang bisa dilipat. Terbuat dari kain nilon ripstop tahan lama berpelapis anti-angin dan anti-air ringan.',
                shipping_care: 'Dicuci dingin secara terpisah.<br>Jangan menggunakan pembutih atau disetrika.',
                journal: 'Memaksimalkan pertahanan terhadap cuaca ekstrem perkotaan dengan desain cangkang luar minimalis terstruktur.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand'],
                    sizes: ['M/L', 'Oversized', 'Uni-Size']
                }
            },
            'modest-urban-coat': {
                id: 'modest-urban-coat',
                name: 'MODEST URBAN COAT',
                price_html: 'Rp3.100.000',
                price_min: 3100000,
                price_max: 3100000,
                badge: 'BESTSELLER',
                badge_bg: '#d9a596',
                badge_color: '#1c1c1a',
                images: [
                    '/assets/images/blog-coat.png',
                    '/assets/images/prod_abaya_1779445230398.png',
                    '/assets/images/gallery-1.png',
                    '/assets/images/prod_outer_1779445195829.png',
                    '/assets/images/blog-hero.png',
                    '/assets/images/blog-objects.png'
                ],
                category: 'Outerwear',
                collection: 'FALL/WINTER 2024',
                description: 'Mantel panjang bersiluet longgar elegan yang dirancang khusus untuk mobilitas perkotaan modern yang santun (modest). Menggunakan material wol blend premium super-halus.',
                shipping_care: 'Hanya dry clean.<br>Gantung dengan benar untuk mempertahankan bentuk bahu.',
                journal: 'Keseimbangan antara keanggunan siluet draping feminin dan perlindungan mantel tebal perkotaan.',
                variations: {
                    colors: ['Charcoal', 'Slate Sand', 'Off-White'],
                    sizes: ['XS/S', 'M/L', 'Oversized']
                }
            }
        };

        // Current product state
        let currentProduct = null;
        let selectedSize = '';
        let selectedColor = '';
        let selectedQty = 1;

        // Initialize UI
        function initProductUI() {
            const container = document.getElementById('product-detail-container');
            if (!container || container.dataset.initialized === 'true') return;
            container.dataset.initialized = 'true';

            // Get slug from Blade variable
            const productId = "{{ $slug }}" || 'asymmetrical-tunic';
            currentProduct = products[productId] || products['asymmetrical-tunic'];

            // Setup Page details
            document.title = `${currentProduct.name} - Raabiha Olshop`;
            document.getElementById('breadcrumb-category').textContent = currentProduct.category.toUpperCase();
            document.getElementById('collection-badge').textContent = currentProduct.collection;
            document.getElementById('product-name').textContent = currentProduct.name;
            
            // Set prices
            updatePriceDisplay();

            // Set Badges
            const saleBadge = document.getElementById('product-sale-badge');
            if (currentProduct.badge && currentProduct.badge.trim() !== '') {
                saleBadge.textContent = currentProduct.badge;
                saleBadge.style.backgroundColor = currentProduct.badge_bg || '#1c1c1a';
                saleBadge.style.color = currentProduct.badge_color || '#ffffff';
                saleBadge.classList.remove('hidden');
            } else {
                saleBadge.classList.add('hidden');
            }

            // Set Description and accordions
            const descContent = `
                <div class="space-y-6">
                    <p class="leading-relaxed">${currentProduct.description}</p>
                    <div class="border-t border-[#e5e2de] pt-6 mt-6">
                        <h4 class="text-[10px] font-mono font-bold tracking-widest uppercase text-[#615e57] mb-3">SHIPPING & CARE</h4>
                        <p class="text-xs text-[#525252] leading-relaxed">${currentProduct.shipping_care}</p>
                    </div>
                </div>
            `;
            document.getElementById('accordion-desc').innerHTML = descContent;

            // Render sizes options
            const sizeGrid = document.getElementById('size-options-grid');
            if (sizeGrid) {
                sizeGrid.innerHTML = '';
                currentProduct.variations.sizes.forEach((size, idx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'flex-1 py-3 text-[10px] font-mono border uppercase tracking-wider transition-all duration-200 ';
                    if (idx === 0) {
                        btn.className += 'border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold';
                        selectedSize = size;
                    } else {
                        btn.className += 'border-[#e5e2de] text-[#1c1c1a] hover:border-[#1c1c1a]';
                    }
                    btn.textContent = size;
                    btn.addEventListener('click', function() {
                        // Deselect other
                        sizeGrid.querySelectorAll('button').forEach(x => {
                            x.className = 'flex-1 py-3 text-[10px] font-mono border border-[#e5e2de] text-[#1c1c1a] uppercase tracking-wider hover:border-[#1c1c1a] transition-all duration-200';
                        });
                        // Select current
                        this.className = 'flex-1 py-3 text-[10px] font-mono border border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold uppercase tracking-wider transition-all duration-200';
                        selectedSize = size;
                        updatePriceDisplay();
                    });
                    sizeGrid.appendChild(btn);
                });
            }

            // Render colors options (Swatches)
            const colorGrid = document.getElementById('color-options-grid');
            const colorLabel = document.getElementById('selected-color-label');
            const colorMap = {
                'Charcoal': '#333333',
                'Slate Sand': '#d9cbb8',
                'Dusty Rose': '#c09891',
                'Off-White': '#f2efe8',
                'Green': '#064e3b'
            };

            if (colorGrid) {
                colorGrid.innerHTML = '';
                // If the product doesn't have Green but we want to show mockup, let's inject it for dummy
                let colors = currentProduct.variations.colors;
                if(currentProduct.id === 'monolith-overcoat' && !colors.includes('Green')) {
                    colors.push('Green');
                }

                colors.forEach((color, idx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    // Outer ring for selected state
                    btn.className = 'w-8 h-8 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 ';
                    
                    const hex = colorMap[color] || colorMap['Charcoal'];
                    const innerCircle = `<div class="w-full h-full rounded-full border border-black/10" style="background-color: ${hex}"></div>`;
                    btn.innerHTML = innerCircle;

                    if (idx === 0) {
                        btn.className += 'border-[#1c1c1a]'; // Selected ring
                        selectedColor = color;
                        if(colorLabel) colorLabel.textContent = color;
                    } else {
                        btn.className += 'border-transparent hover:border-gray-300';
                    }
                    
                    btn.addEventListener('click', function() {
                        // Deselect other
                        colorGrid.querySelectorAll('button').forEach(x => {
                            x.className = 'w-8 h-8 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 border-transparent hover:border-gray-300';
                        });
                        // Select current
                        this.className = 'w-8 h-8 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 border-[#1c1c1a]';
                        selectedColor = color;
                        if(colorLabel) colorLabel.textContent = color;
                    });
                    colorGrid.appendChild(btn);
                });
            }

            // Gallery and thumbnails setup (Unified Desktop and Mobile)
            const mainImg = document.getElementById('main-gallery-image');
            if (mainImg && currentProduct.images.length > 0) {
                mainImg.src = currentProduct.images[0];
            }

            const thumbsContainer = document.getElementById('gallery-thumbnails');
            if (thumbsContainer) {
                thumbsContainer.innerHTML = '';
                currentProduct.images.forEach((img, idx) => {
                    const li = document.createElement('li');
                    li.className = 'thumb-item relative shrink-0 cursor-pointer overflow-hidden bg-[#ebebeb] border border-transparent transition-all ';
                    li.style.width = '20%';
                    li.style.aspectRatio = '4/5';
                    if (idx === 0) li.className += 'border-[#1c1c1a] active';
                    
                    li.innerHTML = `<img src="${img}" alt="Thumb" class="w-full h-full object-cover pointer-events-none" onerror="this.onerror=null; this.src='{{ asset('/assets/images/gallery-1.png') }}';">`;
                    li.addEventListener('click', function() {
                        thumbsContainer.querySelectorAll('li').forEach(x => {
                            x.classList.remove('active');
                            x.classList.remove('border-[#1c1c1a]');
                        });
                        this.classList.add('active', 'border-[#1c1c1a]');
                        
                        if (mainImg && mainImg.src !== img) {
                            // Smooth transition
                            mainImg.classList.remove('opacity-100');
                            mainImg.classList.add('opacity-0');
                            
                            setTimeout(() => {
                                mainImg.src = img;
                                mainImg.onload = () => {
                                    mainImg.classList.remove('opacity-0');
                                    mainImg.classList.add('opacity-100');
                                };
                            }, 300); // matches the duration-300 class
                        }
                    });
                    thumbsContainer.appendChild(li);
                });
            }

            // Setup Custom Gallery Navs
            const thumbPrev = document.getElementById('thumb-prev');
            const thumbNext = document.getElementById('thumb-next');
            if (thumbPrev && thumbNext && thumbsContainer) {
                const updateArrows = () => {
                    if (thumbsContainer.scrollWidth > thumbsContainer.clientWidth) {
                        thumbPrev.classList.remove('hidden');
                        thumbNext.classList.remove('hidden');
                    } else {
                        thumbPrev.classList.add('hidden');
                        thumbNext.classList.add('hidden');
                    }
                };
                
                thumbPrev.addEventListener('click', () => {
                    thumbsContainer.scrollBy({ left: -150, behavior: 'smooth' });
                });
                thumbNext.addEventListener('click', () => {
                    thumbsContainer.scrollBy({ left: 150, behavior: 'smooth' });
                });

                window.addEventListener('resize', updateArrows);
                setTimeout(updateArrows, 500);
            }

            // Quantity picker (Desktop)
            const qtyInput = document.getElementById('quantity-input');
            const qtyMinus = document.getElementById('qty-minus');
            const qtyPlus = document.getElementById('qty-plus');
            if (qtyInput && qtyMinus && qtyPlus) {
                qtyMinus.addEventListener('click', () => {
                    let val = parseInt(qtyInput.value);
                    if (val > 1) {
                        qtyInput.value = val - 1;
                        selectedQty = val - 1;
                    }
                });
                qtyPlus.addEventListener('click', () => {
                    let val = parseInt(qtyInput.value);
                    if (val < 10) {
                        qtyInput.value = val + 1;
                        selectedQty = val + 1;
                    }
                });
                qtyInput.addEventListener('change', () => {
                    let val = parseInt(qtyInput.value);
                    if (isNaN(val) || val < 1) val = 1;
                    if (val > 10) val = 10;
                    qtyInput.value = val;
                    selectedQty = val;
                });
            }

            // Add To Cart logic (Desktop & Mobile)
            const desktopAddBtn = document.getElementById('desktop-add-to-cart-btn');
            const mobileAddBtn = document.getElementById('mobile-add-to-cart-btn');
            const mobileBuyBtn = document.getElementById('mobile-buy-now-btn');

            function addToCartAction(redirect = false) {
                // Get existing cart
                let cart = JSON.parse(localStorage.getItem('raabiha_cart') || '[]');
                
                // Check if variation already exists in cart
                const existingIdx = cart.findIndex(item => item.id === currentProduct.id && item.size === selectedSize && item.color === selectedColor);
                
                // Get price for specific variation
                const finalPrice = getVariationPrice(selectedSize);

                if (existingIdx > -1) {
                    cart[existingIdx].qty += selectedQty;
                } else {
                    cart.push({
                        id: currentProduct.id,
                        name: currentProduct.name,
                        image: currentProduct.images[0],
                        size: selectedSize,
                        color: selectedColor,
                        price: finalPrice,
                        qty: selectedQty
                    });
                }

                localStorage.setItem('raabiha_cart', JSON.stringify(cart));
                updateCartBadges();

                if (redirect) {
                    window.location.href = 'cart.html';
                } else {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                }
            }

            if (desktopAddBtn) desktopAddBtn.addEventListener('click', () => addToCartAction(false));

            // Mobile: CART button → open bottomsheet in "cart" mode
            if (mobileAddBtn) mobileAddBtn.addEventListener('click', () => openMobileBottomsheet('cart'));

            // Mobile: BELI SEKARANG button
            if (mobileBuyBtn) mobileBuyBtn.addEventListener('click', () => {
                const hasVariants = currentProduct.variations &&
                    ((currentProduct.variations.sizes && currentProduct.variations.sizes.length > 0) ||
                     (currentProduct.variations.colors && currentProduct.variations.colors.length > 0));
                if (hasVariants) {
                    openMobileBottomsheet('buynow');
                } else {
                    // No variants: go directly to checkout
                    window.location.href = '/checkout';
                }
            });

            // WA link setup
            const mobileWaBtn = document.getElementById('mobile-wa-btn');
            if (mobileWaBtn) {
                const titleEncoded = encodeURIComponent(currentProduct.name);
                mobileWaBtn.href = `https://wa.me/6281234567890?text=Halo%20Raabiha%20Olshop%2C%20saya%20tertarik%20dengan%20produk%20${titleEncoded}`;
            }

            // Accordion logic
            const accordions = document.querySelectorAll('.product-accordion');
            accordions.forEach(acc => {
                const btn = acc.querySelector('button');
                const content = acc.querySelector('.accordion-content');
                const icon = btn.querySelector('svg');
                
                btn.addEventListener('click', () => {
                    const isOpen = !content.classList.contains('hidden');
                    
                    // Close all
                    accordions.forEach(a => {
                        a.querySelector('.accordion-content').classList.add('hidden');
                        a.querySelector('svg').classList.remove('rotate-180');
                    });
                    
                    // Toggle current
                    if (!isOpen) {
                        content.classList.remove('hidden');
                        icon.classList.add('rotate-180');
                    }
                });
            });

            // Write review toggle form
            const toggleReviewBtn = document.getElementById('toggle-review-btn');
            const writeReviewForm = document.getElementById('write-review-form');
            if (toggleReviewBtn && writeReviewForm) {
                toggleReviewBtn.addEventListener('click', () => {
                    writeReviewForm.classList.toggle('hidden');
                });
            }

            // Rating stars input interactive styling
            const starInputContainer = document.getElementById('rating-stars-input');
            if (starInputContainer) {
                const stars = starInputContainer.querySelectorAll('button');
                stars.forEach((star, index) => {
                    star.addEventListener('click', () => {
                        stars.forEach((s, idx) => {
                            if (idx <= index) {
                                s.classList.add('text-[#064e3b]');
                                s.classList.remove('text-[#d1cec9]');
                            } else {
                                s.classList.remove('text-[#064e3b]');
                                s.classList.add('text-[#d1cec9]');
                            }
                        });
                    });
                });
            }

            // Handle review form submit
            if (writeReviewForm) {
                writeReviewForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    alert('Ulasan Anda telah dikirim dan sedang menunggu moderasi. Terima kasih!');
                    writeReviewForm.reset();
                    writeReviewForm.classList.add('hidden');
                    // Reset stars
                    if (starInputContainer) {
                        const stars = starInputContainer.querySelectorAll('button');
                        stars.forEach(s => {
                            s.classList.remove('text-[#064e3b]');
                            s.classList.add('text-[#d1cec9]');
                        });
                    }
                });
            }

            // Initial cart badge update
            updateCartBadges();
        }

        document.addEventListener('DOMContentLoaded', initProductUI);
        document.addEventListener('livewire:navigated', initProductUI);

        // Price formatting helpers
        function formatRupiah(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function getVariationPrice(size) {
            // Simple logic: Asymmetrical tunic price varies by size
            if (currentProduct.id === 'asymmetrical-tunic') {
                if (size === 'XS/S') return 750000;
                if (size === 'M/L') return 850000;
                if (size === 'Oversized') return 950000;
            }
            return currentProduct.price_min;
        }

        function updatePriceDisplay() {
            const priceDiv = document.getElementById('main-product-price');
            if (!priceDiv) return;

            if (currentProduct.price_min === currentProduct.price_max) {
                priceDiv.innerHTML = `Rp${formatRupiah(currentProduct.price_min)}`;
            } else {
                // If variable, show active size price or range
                if (selectedSize) {
                    const price = getVariationPrice(selectedSize);
                    priceDiv.innerHTML = `Rp${formatRupiah(price)}`;
                } else {
                    priceDiv.innerHTML = `Rp${formatRupiah(currentProduct.price_min)} - Rp${formatRupiah(currentProduct.price_max)}`;
                }
            }
        }

        function updateCartBadges() {
            const cart = JSON.parse(localStorage.getItem('raabiha_cart') || '[]');
            const count = cart.reduce((sum, item) => sum + item.qty, 0);
            
            const badges = document.querySelectorAll('.raabiha-cart-count-badge');
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        }

        // ===== MOBILE BOTTOMSHEET LOGIC =====
        let bsMode = 'cart'; // 'cart' or 'buynow'
        let bsSelectedSize = '';
        let bsSelectedColor = '';
        let bsQty = 1;

        const colorMapBS = {
            'Charcoal': '#333333', 'Slate Sand': '#d9cbb8', 'Dusty Rose': '#c09891',
            'Off-White': '#f2efe8', 'Green': '#064e3b'
        };

        function openMobileBottomsheet(mode) {
            bsMode = mode;
            bsSelectedSize = selectedSize;
            bsSelectedColor = selectedColor;
            bsQty = 1;

            const panel = document.getElementById('mobile-bs-panel');
            const backdrop = document.getElementById('mobile-bs-backdrop');
            const body = document.getElementById('mobile-bs-body');
            const confirmBtn = document.getElementById('mobile-bs-confirm');

            if (!panel || !backdrop || !body) return;

            // Fill header info
            document.getElementById('bs-product-name').textContent = currentProduct.name;
            document.getElementById('bs-product-price').textContent = currentProduct.price_html;

            // Build body content
            body.innerHTML = '';
            const hasVariants = currentProduct.variations &&
                ((currentProduct.variations.sizes && currentProduct.variations.sizes.length > 0) ||
                 (currentProduct.variations.colors && currentProduct.variations.colors.length > 0));

            // Size picker
            if (hasVariants && currentProduct.variations.sizes && currentProduct.variations.sizes.length > 0) {
                const sizeSection = document.createElement('div');
                sizeSection.className = 'mb-5';
                sizeSection.innerHTML = `<div class="text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-3">SELECT SIZE</div>`;
                const sizeGrid = document.createElement('div');
                sizeGrid.className = 'flex flex-wrap gap-2';
                currentProduct.variations.sizes.forEach((size, idx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = size;
                    const isFirst = idx === 0;
                    if (!bsSelectedSize && isFirst) bsSelectedSize = size;
                    const isActive = bsSelectedSize === size;
                    btn.className = `flex-1 min-w-[70px] py-3 text-[10px] font-mono border uppercase tracking-wider transition-all duration-200 ${isActive ? 'border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold' : 'border-[#e5e2de] text-[#1c1c1a] hover:border-[#1c1c1a]'}`;
                    btn.addEventListener('click', function() {
                        sizeGrid.querySelectorAll('button').forEach(b => { b.className = 'flex-1 min-w-[70px] py-3 text-[10px] font-mono border border-[#e5e2de] text-[#1c1c1a] uppercase tracking-wider hover:border-[#1c1c1a] transition-all duration-200'; });
                        this.className = 'flex-1 min-w-[70px] py-3 text-[10px] font-mono border border-[#1c1c1a] bg-[#1c1c1a] text-white font-bold uppercase tracking-wider transition-all duration-200';
                        bsSelectedSize = size;
                    });
                    sizeGrid.appendChild(btn);
                });
                sizeSection.appendChild(sizeGrid);
                body.appendChild(sizeSection);
            }

            // Color picker
            if (hasVariants && currentProduct.variations.colors && currentProduct.variations.colors.length > 0) {
                const colorSection = document.createElement('div');
                colorSection.className = 'mb-5';
                const colorLabelEl = document.createElement('div');
                colorLabelEl.className = 'text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-3';
                colorLabelEl.innerHTML = `COLOR: <span id="bs-color-label" class="font-normal text-[#615e57]">${bsSelectedColor || currentProduct.variations.colors[0]}</span>`;
                colorSection.appendChild(colorLabelEl);
                const colorRow = document.createElement('div');
                colorRow.className = 'flex flex-wrap gap-3';
                currentProduct.variations.colors.forEach((color, idx) => {
                    if (!bsSelectedColor && idx === 0) bsSelectedColor = color;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    const hex = colorMapBS[color] || '#333333';
                    btn.className = `w-9 h-9 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 ${bsSelectedColor === color ? 'border-[#1c1c1a]' : 'border-transparent hover:border-gray-300'}`;
                    btn.innerHTML = `<div class="w-full h-full rounded-full border border-black/10" style="background-color:${hex}"></div>`;
                    btn.addEventListener('click', function() {
                        colorRow.querySelectorAll('button').forEach(b => { b.className = 'w-9 h-9 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 border-transparent hover:border-gray-300'; });
                        this.className = 'w-9 h-9 rounded-full border flex items-center justify-center p-0.5 transition-all duration-200 border-[#1c1c1a]';
                        bsSelectedColor = color;
                        const lbl = document.getElementById('bs-color-label');
                        if (lbl) lbl.textContent = color;
                    });
                    colorRow.appendChild(btn);
                });
                colorSection.appendChild(colorRow);
                body.appendChild(colorSection);
            }

            // QTY picker (only for Cart mode)
            if (mode === 'cart') {
                const qtySection = document.createElement('div');
                qtySection.className = 'mb-2';
                qtySection.innerHTML = `
                    <div class="text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase mb-3">QUANTITY</div>
                    <div class="flex items-center border border-[#e5e2de] w-32 h-11">
                        <button type="button" id="bs-qty-minus" class="w-10 h-full flex items-center justify-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <span id="bs-qty-display" class="flex-1 text-center font-mono text-sm text-[#1c1c1a]">1</span>
                        <button type="button" id="bs-qty-plus" class="w-10 h-full flex items-center justify-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>`;
                body.appendChild(qtySection);
                // Wire qty buttons
                document.getElementById('bs-qty-minus').addEventListener('click', () => {
                    if (bsQty > 1) { bsQty--; document.getElementById('bs-qty-display').textContent = bsQty; }
                });
                document.getElementById('bs-qty-plus').addEventListener('click', () => {
                    if (bsQty < 10) { bsQty++; document.getElementById('bs-qty-display').textContent = bsQty; }
                });
            }

            // Set confirm button label
            confirmBtn.textContent = mode === 'buynow' ? 'BELI SEKARANG' : 'TAMBAH KE KERANJANG';

            // Show sheet
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.add('opacity-100');
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('translate-y-full');
            }, 10);

            // Close handlers
            backdrop.onclick = closeMobileBottomsheet;
            document.getElementById('mobile-bs-close').onclick = closeMobileBottomsheet;

            // Confirm action
            confirmBtn.onclick = function() {
                if (mode === 'buynow') {
                    // Quick buy: go directly to checkout (no cart save)
                    closeMobileBottomsheet();
                    window.location.href = '/checkout';
                } else {
                    // Add to cart
                    let cart = JSON.parse(localStorage.getItem('raabiha_cart') || '[]');
                    const existingIdx = cart.findIndex(item => item.id === currentProduct.id && item.size === bsSelectedSize && item.color === bsSelectedColor);
                    const finalPrice = getVariationPrice(bsSelectedSize);
                    if (existingIdx > -1) {
                        cart[existingIdx].qty += bsQty;
                    } else {
                        cart.push({ id: currentProduct.id, name: currentProduct.name, image: currentProduct.images[0], size: bsSelectedSize, color: bsSelectedColor, price: finalPrice, qty: bsQty });
                    }
                    localStorage.setItem('raabiha_cart', JSON.stringify(cart));
                    updateCartBadges();
                    closeMobileBottomsheet();
                    // Brief success feedback
                    const feedbackDiv = document.createElement('div');
                    feedbackDiv.className = 'fixed top-20 left-1/2 -translate-x-1/2 bg-[#064e3b] text-white text-[10px] font-mono tracking-widest uppercase px-5 py-3 z-[200] shadow-lg transition-opacity duration-500';
                    feedbackDiv.textContent = 'Ditambahkan ke keranjang!';
                    document.body.appendChild(feedbackDiv);
                    setTimeout(() => { feedbackDiv.classList.add('opacity-0'); setTimeout(() => feedbackDiv.remove(), 500); }, 2000);
                }
            };
        }

        function closeMobileBottomsheet() {
            const panel = document.getElementById('mobile-bs-panel');
            const backdrop = document.getElementById('mobile-bs-backdrop');
            if (!panel || !backdrop) return;
            panel.classList.add('translate-y-full');
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            setTimeout(() => { backdrop.classList.add('hidden'); }, 300);
        }
        // ===== END MOBILE BOTTOMSHEET LOGIC =====

    </script>
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
    <!-- Backdrop -->
    <div id="mobile-bs-backdrop" class="md:hidden fixed inset-0 bg-black/50 z-[110] hidden transition-opacity duration-300 opacity-0"></div>

    <!-- Sheet Panel -->
    <div id="mobile-bs-panel" class="md:hidden fixed bottom-0 left-0 right-0 z-[120] bg-[#fcf9f5] rounded-t-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.15)] transform translate-y-full transition-transform duration-300">
        <!-- Handle -->
        <div class="flex justify-center pt-3 pb-2">
            <div class="w-10 h-1 bg-[#e5e2de] rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 pb-4 border-b border-[#e5e2de]">
            <div>
                <div id="bs-product-name" class="text-[#1c1c1a] text-sm font-serif font-bold uppercase leading-tight"></div>
                <div id="bs-product-price" class="text-[#615e57] text-xs font-mono mt-0.5"></div>
            </div>
            <button type="button" id="mobile-bs-close" class="w-8 h-8 flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] transition-colors focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body (Variant selectors injected here by JS) -->
        <div id="mobile-bs-body" class="px-5 pt-4 pb-4 overflow-y-auto max-h-[55vh]">
            <!-- Filled by JS: size, color, qty rows -->
        </div>

        <!-- CTA Footer -->
        <div class="px-5 pb-6 pt-3 border-t border-[#e5e2de]">
            <button type="button" id="mobile-bs-confirm" class="w-full h-14 bg-[#09493B] text-white text-[10px] font-mono font-bold tracking-[0.2em] uppercase flex items-center justify-center transition-colors hover:bg-[#07362c] focus:outline-none">
                KONFIRMASI
            </button>
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
        
        <!-- Cart Icon -->
        <button type="button" id="mobile-add-to-cart-btn" class="w-[60px] shrink-0 h-full flex flex-col items-center justify-center border-r border-[#e5e2de] text-[#1c1c1a] hover:bg-gray-50 transition-colors focus:outline-none">
            <div class="relative">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="raabiha-cart-count-badge absolute -top-1 -right-2 bg-[#064e3b] text-white text-[8px] font-bold w-3 h-3 rounded-full flex items-center justify-center hidden">0</span>
            </div>
            <span class="text-[8px] font-mono tracking-widest uppercase">Cart</span>
        </button>
        
        <!-- Buy Now Button -->
        <button type="button" id="mobile-buy-now-btn" class="flex-1 h-full bg-[#09493B] text-white flex flex-col items-center justify-center hover:bg-[#07362c] transition-colors focus:outline-none">
            <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">BELI SEKARANG</span>
        </button>
    </div>
</x-layouts.app>
