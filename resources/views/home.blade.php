<x-layouts.app title="Beranda">
<main class="w-full min-h-screen">
    @php
        // Helper untuk render image Curator or Fallback
        $resolveImage = function($curatorId, $fallback = null) {
            if ($curatorId) {
                $media = \Awcodes\Curator\Models\Media::find($curatorId);
                if ($media) return $media->url;
            }
            return $fallback ?: 'https://placehold.co/800x800/e5e2de/615e57?text=Image+Not+Available';
        };

        $resolveCatImage = function($imageValue, $fallback = null) {
            if (is_numeric($imageValue)) {
                $media = \Awcodes\Curator\Models\Media::find($imageValue);
                if ($media) return $media->url;
            }
            if ($imageValue) {
                return asset('storage/' . $imageValue);
            }
            return $fallback ?: 'https://placehold.co/800x800/e5e2de/615e57?text=Image+Not+Available';
        };

        // Load settings
        $homeHeroTag = \App\Models\SiteSetting::where('key', 'home_hero_tag')->value('value') ?: 'DROP 02 // 2026';
        $homeHeroTitle = \App\Models\SiteSetting::where('key', 'home_hero_title')->value('value') ?: 'Architectural Modesty';
        $homeHeroSubtitle = \App\Models\SiteSetting::where('key', 'home_hero_subtitle')->value('value') ?: 'A dialogue between structural precision and urban fluidity. Redefining the modest silhouette for the next generation.';
        $homeHeroImage = \App\Models\SiteSetting::where('key', 'home_hero_image')->value('value');
        $homeHeroButtonText = \App\Models\SiteSetting::where('key', 'home_hero_button_text')->value('value') ?: 'Explore The Drop';
        $homeHeroButtonLink = \App\Models\SiteSetting::where('key', 'home_hero_button_link')->value('value') ?: '#';

        $rawMarquee = \App\Models\SiteSetting::where('key', 'home_marquee_items')->value('value');
        $marqueeItems = $rawMarquee ? json_decode($rawMarquee, true) : [];
        if (!is_array($marqueeItems) || empty($marqueeItems)) {
            $marqueeItems = [
                ['text' => 'New Collection'],
                ['text' => 'Modest Fashion'],
                ['text' => 'Free Shipping'],
                ['text' => 'Reseller Open']
            ];
        }

        $homeLookbookTag = \App\Models\SiteSetting::where('key', 'home_lookbook_tag')->value('value') ?: 'LOOKBOOK // 04';
        $homeLookbookTitle = \App\Models\SiteSetting::where('key', 'home_lookbook_title')->value('value') ?: 'Urban Sanctuary';
        $homeLookbookDescription = \App\Models\SiteSetting::where('key', 'home_lookbook_description')->value('value') ?: 'Discover the pieces that define the modern modest woman. Our SS24 collection blends utility with tradition, creating a sanctuary of style within the urban grid.';
        $homeLookbookImage = \App\Models\SiteSetting::where('key', 'home_lookbook_image')->value('value');
        $homeLookbookButtonText = \App\Models\SiteSetting::where('key', 'home_lookbook_button_text')->value('value') ?: 'Read Journal';
        $homeLookbookButtonLink = \App\Models\SiteSetting::where('key', 'home_lookbook_button_link')->value('value') ?: '#';
        $rawLookbookProducts = \App\Models\SiteSetting::where('key', 'home_lookbook_products')->value('value');
        $lookbookProductIds = $rawLookbookProducts ? json_decode($rawLookbookProducts, true) : [];

        // Ambil kategori produk aktif dari database
        $activeCategories = \App\Models\Category::where('is_active', true)->latest()->get();
    @endphp

    <!-- Desktop Hero Section -->
    <section class="hidden md:grid grid-cols-2 w-full h-[calc(100vh-80px)] bg-[#fcf9f5]">
        <!-- Desktop Text Column -->
        <div class="flex flex-col justify-center px-8 py-10 lg:py-12 lg:pl-24 lg:pr-16 text-left bg-[#fcf9f5]">
            <div class="inline-block bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.15em] mb-4 uppercase w-fit">{{ $homeHeroTag }}</div>
            <h1 class="text-4xl lg:text-[3.5rem] leading-[1.1] tracking-[-0.02em] uppercase font-serif mb-4">{!! nl2br(e($homeHeroTitle)) !!}</h1>
            <p class="text-[14px] lg:text-[15px] leading-relaxed text-[#525252] max-w-[400px] mb-8">{{ $homeHeroSubtitle }}</p>
            <a href="{{ $homeHeroButtonLink }}" class="inline-block bg-[#064e3b] hover:bg-[#022c22] text-white text-[11px] font-semibold tracking-[0.15em] uppercase py-3 px-8 transition-colors w-fit">
                {{ $homeHeroButtonText }}
            </a>
        </div>
        
        <!-- Desktop Image Column -->
        <div class="w-full h-full relative overflow-hidden">
            <img src="{{ $resolveImage($homeHeroImage, asset('assets/images/hero_model_1779445106838.webp')) }}" alt="Hero" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high">
        </div>
    </section>

    <!-- Mobile Hero Section -->
    <section class="relative w-full h-[calc(100vh-64px)] flex items-center justify-center md:hidden overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $resolveImage($homeHeroImage, asset('assets/images/hero_model_1779445106838.webp')) }}" alt="Hero" class="w-full h-full object-cover" fetchpriority="high">
            <!-- Mobile Dark Gradient Overlay (Centered) -->
            <div class="absolute inset-0 bg-black/40"></div>
        </div>

        <!-- Mobile Text Overlay -->
        <div class="relative z-10 w-full px-8 py-6 flex flex-col items-center text-center mt-12">
            <div class="text-[9px] font-mono tracking-[0.2em] text-white mb-4 uppercase">{{ $homeHeroTag }}</div>
            <h1 class="text-3xl md:text-4xl leading-[1.15] font-serif italic text-white mb-6">{!! nl2br(e($homeHeroTitle)) !!}</h1>
            <a href="{{ $homeHeroButtonLink }}" class="inline-block bg-[#064e3b] text-white text-[10px] font-mono tracking-[0.1em] uppercase py-3 px-8 w-full max-w-[280px]">
                {{ $homeHeroButtonText }}
            </a>
        </div>
    </section>

    <style>
        @@keyframes marqueeScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee-clean {
            display: inline-flex;
            align-items: center;
            gap: 4rem;
            animation: marqueeScroll 25s linear infinite;
            padding-right: 4rem;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    
    <div style="background-color: #262626; color: #d4d4d4;" class="relative z-10 py-3.5 w-full overflow-hidden flex whitespace-nowrap">
        <div class="animate-marquee-clean">
            @foreach(array_merge($marqueeItems, $marqueeItems) as $item)
                <span class="text-[11px] font-semibold tracking-[0.2em] uppercase">{{ $item['text'] }}</span>
            @endforeach
        </div>
        <div class="animate-marquee-clean" aria-hidden="true">
            @foreach(array_merge($marqueeItems, $marqueeItems) as $item)
                <span class="text-[11px] font-semibold tracking-[0.2em] uppercase">{{ $item['text'] }}</span>
            @endforeach
        </div>
    </div>

    <!-- Categories Section -->
    <section class="max-w-[1400px] mx-auto px-6 lg:px-16 py-24 pb-0">
        <div class="flex justify-between items-end mb-12">
            <h2 class="text-3xl lg:text-[2rem] tracking-[0.05em] font-serif m-0">CATEGORIES</h2>
            <a href="{{ url('/shop') }}" class="text-[10px] font-semibold tracking-[0.1em] uppercase text-[#525252] border-b border-[#525252] pb-0.5 hover:text-[#1a1a1a]">View All</a>
        </div>
        <!-- Mobile Section Title -->
        <div class="md:hidden text-[9px] font-mono tracking-widest text-[#615e57] uppercase mb-6 px-2">Browse Categories</div>
        
        <!-- Desktop Grid (Hidden on Mobile) -->
        <div x-data="{ 
                interval: null,
                startAutoScroll() {
                    this.interval = setInterval(() => {
                        const maxScroll = this.$el.scrollWidth - this.$el.clientWidth;
                        if (this.$el.scrollLeft >= maxScroll - 10) {
                            this.$el.scrollTo({left: 0, behavior: 'smooth'});
                        } else {
                            this.$el.scrollBy({left: 300, behavior: 'smooth'});
                        }
                    }, 3500);
                }
             }"
             x-init="startAutoScroll()"
             x-on:mouseenter="clearInterval(interval)"
             x-on:mouseleave="startAutoScroll()"
             class="hidden md:flex overflow-x-auto snap-x snap-mandatory gap-6 pb-6 scrollbar-hide">
            @forelse($activeCategories as $cat)
                <a href="{{ url('/shop?category=' . $cat->slug) }}" class="relative aspect-[3/4] bg-[#1c1c1a] overflow-hidden group flex-none w-[280px] lg:w-[calc(25%-1.125rem)] snap-start">
                    <!-- Image -->
                    @php
                        $catImageUrl = $resolveCatImage($cat->image, 'https://placehold.co/800x1000/e5e2de/615e57?text=' . urlencode($cat->name));
                    @endphp
                    <img src="{{ $catImageUrl }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 opacity-90 group-hover:opacity-50" alt="{{ $cat->name }}" loading="lazy">
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/20 group-hover:bg-black/40 transition-colors duration-500 z-10"></div>
                    
                    <!-- Text Content -->
                    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center p-6">
                        <h3 class="text-white text-3xl font-serif tracking-[0.1em] uppercase mb-4 drop-shadow-lg scale-95 group-hover:scale-100 transition-transform duration-500">{{ $cat->name }}</h3>
                        <span class="text-white text-[9px] font-mono tracking-[0.2em] uppercase border border-white px-5 py-2.5 hover:bg-white hover:text-[#1c1c1a] transition-all duration-300 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 shadow-lg">Jelajahi</span>
                    </div>
                </a>
            @empty
                <p class="w-full text-center text-gray-500 font-serif py-12">Belum ada kategori aktif.</p>
            @endforelse
        </div>

        <!-- Mobile Circular Grid -->
        <div class="md:hidden flex overflow-x-auto gap-6 px-2 pb-4 scrollbar-hide">
            @foreach($activeCategories as $cat)
                <a href="{{ url('/shop?category=' . $cat->slug) }}" class="flex flex-col items-center group flex-none w-[72px]">
                    <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border border-[#e5e2de] shadow-sm">
                        @php
                            $catImageUrl = $resolveCatImage($cat->image, 'https://placehold.co/100x100/e5e2de/615e57?text=' . urlencode($cat->name));
                        @endphp
                        <img src="{{ $catImageUrl }}" class="w-full h-full object-cover" alt="{{ $cat->name }}" loading="lazy">
                    </div>
                    <span class="text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a] text-center line-clamp-1 w-full">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="max-w-[1400px] mx-auto px-6 lg:px-16 py-24">
        <div class="flex justify-between items-end mb-12 md:justify-center md:mb-16">
            <div class="md:hidden">
                <h2 class="text-3xl font-serif italic m-0">New Arrivals</h2>
                <div class="text-[9px] font-mono tracking-widest text-[#615e57] uppercase mt-2">Latest Modest Innovations</div>
            </div>
            <h2 class="hidden md:block text-3xl lg:text-[2rem] tracking-[0.05em] font-serif">NEW ARRIVALS</h2>
            <a href="{{ url('/shop') }}" class="md:hidden text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a] border-b border-[#c4c7c7] pb-0.5">View All</a>
        </div>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $latestProducts = \App\Models\Product::where('is_active', true)->where('is_hidden', false)->latest()->take(4)->get();
            @endphp
            @forelse($latestProducts as $prod)
                <div class="group block relative flex flex-col h-full">
                    {{-- IMAGE CONTAINER: Portrait 3:4 editorial --}}
                    <div class="aspect-[3/4] bg-[#e5e2de] mb-4 overflow-hidden relative shadow-[0_4px_24px_rgba(0,0,0,0.09)] group-hover:shadow-[0_8px_36px_rgba(0,0,0,0.15)] transition-shadow duration-500">
                        <a href="{{ url('/product/' . $prod->slug) }}" class="block w-full h-full" aria-label="Lihat produk {{ $prod->name }}">
                            @php
                                $imageUrl = asset('assets/images/placeholder.webp');
                                if (!empty($prod->images)) {
                                    if (is_numeric($prod->images[0])) {
                                        $media = \Awcodes\Curator\Models\Media::find($prod->images[0]);
                                        if ($media && Storage::disk('public')->exists($media->path)) {
                                            $imageUrl = Storage::url($media->path);
                                        }
                                    } else {
                                        if (Storage::disk('public')->exists($prod->images[0])) {
                                            $imageUrl = asset('storage/' . $prod->images[0]);
                                        }
                                    }
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-[cubic-bezier(0.25,0.46,0.45,0.94)]" alt="{{ $prod->name }}" loading="lazy" />
                            {{-- Hover overlay --}}
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-all duration-500 flex items-end justify-center pb-6 opacity-0 group-hover:opacity-100">
                                <span class="text-white text-[9px] font-mono tracking-[0.25em] uppercase border border-white/80 px-5 py-2.5 translate-y-2 group-hover:translate-y-0 transition-transform duration-500">Lihat Detail</span>
                            </div>
                        </a>
                        {{-- Discount Badge --}}
                        @if($prod->discount_price !== null && $prod->discount_price > 0 && !(auth()->check() && auth()->user()->hasRole('reseller')))
                            <div class="absolute top-3 left-3 bg-[#1c1c1a] text-white text-[9px] font-mono tracking-[0.15em] px-2.5 py-1 z-10">-{{ round((($prod->price - $prod->discount_price) / $prod->price) * 100) }}%</div>
                        @endif
                        {{-- Wishlist --}}
                        <div class="absolute top-3 right-3 z-10">
                            <livewire:wishlist-toggle :product_id="$prod->id" :key="'wishlist-home-'.$prod->id" />
                        </div>
                    </div>

                    {{-- INFO BELOW IMAGE --}}
                    <a href="{{ url('/product/' . $prod->slug) }}" class="block flex-1 flex flex-col">
                        {{-- Product Name: serif italic for premium fashion feel --}}
                        <h3 class="text-[13px] font-serif italic text-[#1c1c1a] mb-1.5 line-clamp-2 leading-snug tracking-wide">{{ $prod->name }}</h3>

                        {{-- Thin accent divider --}}
                        <div class="w-5 h-[1.5px] bg-[#1c1c1a]/30 mb-2.5"></div>

                        {{-- Price: prominent --}}
                        <div class="mb-2.5">
                            @if($prod->discount_price !== null && $prod->discount_price > 0 && !(auth()->check() && auth()->user()->hasRole('reseller')))
                                <div class="flex items-baseline gap-2">
                                    <span class="text-[15px] font-bold text-[#1c1c1a]">Rp{{ number_format($prod->discount_price, 0, ',', '.') }}</span>
                                    <span class="text-[11px] text-[#b0aca6] line-through">Rp{{ number_format($prod->price, 0, ',', '.') }}</span>
                                </div>
                            @else
                                <span class="text-[15px] font-bold text-[#1c1c1a]">Rp{{ number_format($prod->effective_price, 0, ',', '.') }}</span>
                            @endif
                        </div>

                        {{-- Promo badges: pill with subtle border --}}
                        @php $globalPromos = \App\Models\Voucher::getGlobalPromoLabels(); @endphp
                        @if($prod->has_free_shipping || count($globalPromos) > 0)
                            <div class="flex flex-wrap gap-1.5 mb-2.5">
                                @if($prod->has_free_shipping && !collect($globalPromos)->contains('type', 'shipping'))
                                    <span class="text-[8px] font-semibold text-[#064e3b] bg-[#f0faf4] border border-[#064e3b]/25 uppercase tracking-[0.1em] px-2 py-0.5 rounded-full">
                                        Gratis Ongkir
                                    </span>
                                @endif
                                @foreach($globalPromos as $promo)
                                    <span class="text-[8px] font-semibold text-[#615e57] bg-[#f5f3ef] border border-[#c4c0b8] uppercase tracking-[0.1em] px-2 py-0.5 rounded-full">
                                        {{ $promo['text'] }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Rating & Sold --}}
                        @if($prod->effective_rating > 0 || $prod->effective_sold_count > 0)
                            <div class="flex items-center gap-1.5">
                                @if($prod->effective_rating > 0)
                                    <span class="text-[#d4a843] text-[11px]">★</span>
                                    <span class="text-[10px] font-semibold text-[#1c1c1a]">{{ number_format($prod->effective_rating, 1, ',', '.') }}</span>
                                @endif
                                @if($prod->effective_rating > 0 && $prod->effective_sold_count > 0)
                                    <span class="text-[#c4c0b8] text-[10px]">·</span>
                                @endif
                                @if($prod->effective_sold_count > 0)
                                    @php $soldCount = $prod->effective_sold_count; $soldText = $soldCount >= 1000 ? number_format($soldCount / 1000, 1, ',', '.') . 'rb' : $soldCount; @endphp
                                    <span class="text-[10px] text-[#9b9b9b]">{{ $soldText }} terjual</span>
                                @endif
                            </div>
                        @endif
                    </a>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-500 font-serif">Belum ada produk terbaru.</p>
            @endforelse
        </div>
    </section>

    <!-- Lookbook / Sanctuary Section -->
    <section class="bg-[#f0f0ed] px-6 lg:px-16 py-24">
        <div class="max-w-[1400px] mx-auto md:grid md:grid-cols-[1.2fr_1fr] gap-12 lg:gap-24 items-center">
            <!-- Image (Mobile overlay vs Desktop side-by-side) -->
            <div class="relative w-full h-[65vh] md:h-auto">
                <img src="{{ $resolveImage($homeLookbookImage, asset('assets/images/lookbook_hero_1779445259756.webp')) }}" alt="Lookbook" class="w-full h-full md:aspect-[4/3] object-cover" loading="lazy">
                <!-- Mobile gradient overlay -->
                <div class="absolute inset-0 bg-black/60 md:hidden"></div>
                
                <!-- Mobile Text Overlay -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 md:hidden z-10 text-white">
                    <h2 class="text-4xl font-serif mb-4">{!! nl2br(e($homeLookbookTitle)) !!}</h2>
                    <p class="text-[13px] font-sans text-gray-200 mb-8 max-w-[250px]">{{ $homeLookbookDescription }}</p>
                    <a href="{{ $homeLookbookButtonLink }}" class="border border-white text-white px-8 py-3 text-[9px] font-mono tracking-widest uppercase">{{ $homeLookbookButtonText }}</a>
                </div>
            </div>
            
            <!-- Desktop Text Column -->
            <div class="hidden md:block">
                <div class="text-[10px] text-[#d97777] tracking-[0.15em] mb-4">{{ $homeLookbookTag }}</div>
                <h2 class="text-5xl leading-[1.1] font-serif mb-6">{!! nl2br(e($homeLookbookTitle)) !!}</h2>
                <p class="text-[14px] leading-relaxed text-[#525252] mb-12">{{ $homeLookbookDescription }}</p>
                
                @php
                    if (!empty($lookbookProductIds)) {
                        $featuredProducts = \App\Models\Product::whereIn('id', $lookbookProductIds)->where('is_active', true)->where('is_hidden', false)->take(3)->get();
                    } else {
                        $featuredProducts = \App\Models\Product::where('is_active', true)->where('is_hidden', false)->inRandomOrder()->take(3)->get();
                    }
                @endphp
                <div class="flex flex-col gap-4">
                    @foreach($featuredProducts as $fp)
                        <a href="{{ url('/product/' . $fp->slug) }}" class="flex items-center justify-between pb-4 border-b border-[#e5e5e5] hover:opacity-85 transition-opacity">
                            <div class="flex items-center gap-4">
                                @php
                                    $fpImages = $fp->images;
                                    $fpImg = is_array($fpImages) && count($fpImages) > 0 ? $fpImages[0] : null;
                                @endphp
                                <img src="{{ $resolveImage($fpImg) }}" class="w-12 h-12 object-cover" loading="lazy">
                                <div>
                                    <div class="text-[10px] font-semibold tracking-[0.1em] uppercase mb-1">{{ $fp->name }}</div>
                                    @if($fp->discount_price !== null && $fp->discount_price > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] font-semibold text-[#064e3b]">Rp {{ number_format($fp->discount_price, 0, ',', '.') }}</span>
                                            <span class="text-[10px] text-[#9b9b9b] line-through">Rp {{ number_format($fp->price, 0, ',', '.') }}</span>
                                        </div>
                                    @else
                                        <div class="text-[12px] text-[#525252]">Rp {{ number_format($fp->price, 0, ',', '.') }}</div>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-4 h-4 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </a>
                    @endforeach
                </div>
                
                <div class="mt-10">
                    <a href="{{ $homeLookbookButtonLink }}" class="inline-block border border-[#1a1a1a] text-[#1a1a1a] hover:bg-[#1a1a1a] hover:text-white px-8 py-4 text-[10px] font-semibold tracking-[0.15em] uppercase transition-colors">{{ $homeLookbookButtonText }}</a>
                </div>
            </div>
        </div>
    </section>
</main>
</x-layouts.app>
