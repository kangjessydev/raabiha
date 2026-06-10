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

        // Ambil kategori produk aktif dari database
        $activeCategories = \App\Models\Category::where('is_active', true)->latest()->take(4)->get();
    @endphp

    <!-- Desktop Hero Section -->
    <section class="hidden md:grid grid-cols-2 w-full h-[85vh] min-h-[calc(100vh-80px)] bg-[#fcf9f5]">
        <!-- Desktop Text Column -->
        <div class="flex flex-col justify-center px-8 py-16 lg:pl-24 lg:pr-16 text-left bg-[#fcf9f5]">
            <div class="inline-block bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.15em] mb-6 uppercase w-fit">{{ $homeHeroTag }}</div>
            <h1 class="text-5xl lg:text-[4.5rem] leading-[1.05] tracking-[-0.02em] uppercase font-serif mb-6">{!! nl2br(e($homeHeroTitle)) !!}</h1>
            <p class="text-[15px] leading-relaxed text-[#525252] max-w-[400px] mb-12">{{ $homeHeroSubtitle }}</p>
            <a href="{{ $homeHeroButtonLink }}" class="inline-block bg-[#064e3b] hover:bg-[#022c22] text-white text-[11px] font-semibold tracking-[0.15em] uppercase py-4 px-10 transition-colors w-fit">
                {{ $homeHeroButtonText }}
            </a>
        </div>
        
        <!-- Desktop Image Column -->
        <div class="w-full h-full overflow-hidden">
            <img src="{{ $resolveImage($homeHeroImage, asset('assets/images/hero_model_1779445106838.png')) }}" alt="Hero" class="w-full h-full object-cover">
        </div>
    </section>

    <!-- Mobile Hero Section -->
    <section class="relative w-full h-[85vh] flex items-end md:hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $resolveImage($homeHeroImage, asset('assets/images/hero_model_1779445106838.png')) }}" alt="Hero" class="w-full h-full object-cover">
            <!-- Mobile Dark Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
        </div>

        <!-- Mobile Text Overlay -->
        <div class="relative z-10 w-full px-6 pb-12 flex flex-col items-center text-center">
            <div class="text-[9px] font-mono tracking-[0.2em] text-white mb-4 uppercase">{{ $homeHeroTag }}</div>
            <h1 class="text-4xl leading-[1.15] font-serif italic text-white mb-8">{{ $homeHeroTitle }}</h1>
            <a href="{{ $homeHeroButtonLink }}" class="inline-block bg-[#064e3b] text-white text-[10px] font-mono tracking-[0.1em] uppercase py-4 px-8 w-full max-w-[280px]">
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
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($activeCategories as $cat)
                <a href="{{ url('/shop?category=' . $cat->slug) }}" class="relative aspect-[3/4] bg-[#e5e5e5] overflow-hidden group">
                    <div class="absolute top-4 left-4 bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.1em] uppercase z-10">{{ $cat->name }}</div>
                    @php
                        $catImageUrl = $resolveCatImage($cat->image, 'https://placehold.co/800x1000/e5e2de/615e57?text=' . urlencode($cat->name));
                    @endphp
                    <img src="{{ $catImageUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $cat->name }}">
                </a>
            @empty
                <p class="col-span-full text-center text-gray-500 font-serif py-12">Belum ada kategori aktif.</p>
            @endforelse
        </div>

        <!-- Mobile Circular Grid -->
        <div class="md:hidden grid grid-cols-4 gap-4 px-2">
            @foreach($activeCategories as $cat)
                <a href="{{ url('/shop?category=' . $cat->slug) }}" class="flex flex-col items-center group">
                    <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border border-[#e5e2de] shadow-sm">
                        @php
                            $catImageUrl = $resolveCatImage($cat->image, 'https://placehold.co/100x100/e5e2de/615e57?text=' . urlencode($cat->name));
                        @endphp
                        <img src="{{ $catImageUrl }}" class="w-full h-full object-cover" alt="{{ $cat->name }}">
                    </div>
                    <span class="text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a] text-center line-clamp-1">{{ $cat->name }}</span>
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
                $latestProducts = \App\Models\Product::where('is_active', true)->latest()->take(4)->get();
            @endphp
            @forelse($latestProducts as $prod)
                <a href="{{ url('/product/' . $prod->slug) }}" class="group block">
                    <div class="aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                        @php
                            $images = $prod->images;
                            $firstImg = is_array($images) && count($images) > 0 ? $images[0] : null;
                        @endphp
                        <img width="1024" height="1024" src="{{ $resolveImage($firstImg, 'https://placehold.co/800x1000/e5e2de/615e57?text=' . urlencode($prod->name)) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $prod->name }}" />                                                                
                    </div>
                    <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1">{{ $prod->name }}</h3>
                    <div class="text-[13px] text-[#525252]">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
                </a>
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
                <img src="{{ $resolveImage($homeLookbookImage, asset('assets/images/lookbook_hero_1779445259756.png')) }}" alt="Lookbook" class="w-full h-full md:aspect-[4/3] object-cover">
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
                    $featuredProducts = \App\Models\Product::where('is_active', true)->inRandomOrder()->take(2)->get();
                @endphp
                <div class="flex flex-col gap-4">
                    @foreach($featuredProducts as $fp)
                        <a href="{{ url('/product/' . $fp->slug) }}" class="flex items-center justify-between pb-4 border-b border-[#e5e5e5] hover:opacity-85 transition-opacity">
                            <div class="flex items-center gap-4">
                                @php
                                    $fpImages = $fp->images;
                                    $fpImg = is_array($fpImages) && count($fpImages) > 0 ? $fpImages[0] : null;
                                @endphp
                                <img src="{{ $resolveImage($fpImg) }}" class="w-12 h-12 object-cover">
                                <div>
                                    <div class="text-[10px] font-semibold tracking-[0.1em] uppercase mb-1">{{ $fp->name }}</div>
                                    <div class="text-[12px] text-[#525252]">Rp {{ number_format($fp->price, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <svg class="w-4 h-4 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</main>
</x-layouts.app>
