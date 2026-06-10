<footer class="bg-[#fcf9f5] md:bg-[#262626] text-[#1c1c1a] md:text-[#d4d4d4] pt-12 md:pt-20 pb-24 md:pb-8 px-6 md:px-16 mt-auto">
        @php
            $siteName = \App\Models\SiteSetting::where('key', 'site_name')->value('value') ?? 'RAABIHA';
            $logoLightId = \App\Models\SiteSetting::where('key', 'site_logo_light')->value('value');
            $logoLightMedia = $logoLightId ? \Awcodes\Curator\Models\Media::find($logoLightId) : null;
            $logoLightUrl = $logoLightMedia ? Storage::url($logoLightMedia->path) : null;
            
            $logoDarkId = \App\Models\SiteSetting::where('key', 'site_logo_dark')->value('value');
            $logoDarkMedia = $logoDarkId ? \Awcodes\Curator\Models\Media::find($logoDarkId) : null;
            $logoDarkUrl = $logoDarkMedia ? Storage::url($logoDarkMedia->path) : null;

            $footerDesc = \App\Models\SiteSetting::where('key', 'footer_description')->value('value') ?? "Architectural modesty for the modern intellectual.<br />Bridging high-fashion editorial with raw urban energy.";
            $ig = \App\Models\SiteSetting::where('key', 'social_instagram')->value('value');
            $tiktok = \App\Models\SiteSetting::where('key', 'social_tiktok')->value('value');
            $copyright = \App\Models\SiteSetting::where('key', 'footer_copyright')->value('value') ?? '&copy; ' . date('Y') . ' ' . strtoupper($siteName) . '. ARCHITECTURAL MODESTY.';
            
            $rawFooterLinks = \App\Models\SiteSetting::where('key', 'footer_links')->value('value');
            $footerLinks = $rawFooterLinks ? json_decode($rawFooterLinks, true) : [];
            if (!is_array($footerLinks)) $footerLinks = [];
            
            $rawShopLinks = \App\Models\SiteSetting::where('key', 'footer_shop_links')->value('value');
            $shopLinks = $rawShopLinks ? json_decode($rawShopLinks, true) : [];
            if (!is_array($shopLinks)) $shopLinks = [];

            $rawBrandLinks = \App\Models\SiteSetting::where('key', 'footer_brand_links')->value('value');
            $brandLinks = $rawBrandLinks ? json_decode($rawBrandLinks, true) : [];
            if (!is_array($brandLinks)) $brandLinks = [];

            $kolom1Title = \App\Models\SiteSetting::where('key', 'footer_kolom1_title')->value('value') ?: 'Shop';
            $kolom2Title = \App\Models\SiteSetting::where('key', 'footer_kolom2_title')->value('value') ?: 'Brand';
        @endphp
        
        <!-- Desktop Grid (Hidden on Mobile) -->
        <div class="hidden md:grid max-w-[1400px] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1.5fr] gap-8 md:gap-12 mb-16">
            <div>
                @if($logoDarkUrl)
                    <img src="{{ $logoDarkUrl }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain mb-6">
                @else
                    <div class="font-serif text-3xl font-semibold tracking-widest text-[#f5f5f5] mb-6">{{ strtoupper($siteName) }}</div>
                @endif
                <p class="font-inter text-[13px] leading-relaxed text-[#a3a3a3] max-w-[320px] mb-8">
                    {!! nl2br(e($footerDesc)) !!}
                </p>
                <div class="flex gap-6">
                    @if($ig)
                        <a href="{{ $ig }}" target="_blank" class="text-[#a3a3a3] hover:text-white transition-colors text-[11px] font-medium uppercase tracking-[0.1em]">Instagram</a>
                    @endif
                    @if($tiktok)
                        <a href="{{ $tiktok }}" target="_blank" class="text-[#a3a3a3] hover:text-white transition-colors text-[11px] font-medium uppercase tracking-[0.1em]">TikTok</a>
                    @endif
                    @if(!$ig && !$tiktok)
                        <span class="text-[#a3a3a3] text-[11px] font-medium uppercase tracking-[0.1em]">Belum ada sosial media diset.</span>
                    @endif
                </div>
            </div>
            <div>
                <h4 class="font-inter text-[10px] tracking-widest uppercase text-[#d4d4d4] mb-6">{{ $kolom1Title }}</h4>
                <ul class="flex flex-col gap-4 font-inter text-[13px] text-[#a3a3a3]">
                    @forelse($shopLinks as $link)
                        <li><a href="{{ url($link['url']) }}" class="hover:text-white transition-colors">{{ $link['label'] }}</a></li>
                    @empty
                        <li><a href="{{ url('/shop') }}" class="hover:text-white transition-colors">New Arrivals</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-white transition-colors">Gamis</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-white transition-colors">Outerwear</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-white transition-colors">Sets</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-white transition-colors">Hijabs</a></li>
                    @endforelse
                </ul>
            </div>
            <div>
                <h4 class="font-inter text-[10px] tracking-widest uppercase text-[#d4d4d4] mb-6">{{ $kolom2Title }}</h4>
                <ul class="flex flex-col gap-4 font-inter text-[13px] text-[#a3a3a3]">
                    @forelse($brandLinks as $link)
                        <li><a href="{{ url($link['url']) }}" class="hover:text-white transition-colors">{{ $link['label'] }}</a></li>
                    @empty
                        <li><a href="{{ url('/about') }}" class="hover:text-white transition-colors">Our Story</a></li>
                        <li><a href="{{ url('/blog') }}" class="hover:text-white transition-colors">Journal</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white transition-colors">Reseller</a></li>
                        <li><a href="{{ url('/about') }}" class="hover:text-white transition-colors">Sustainability</a></li>
                    @endforelse
                </ul>
            </div>
            <div>
                <h4 class="font-inter text-[10px] tracking-widest uppercase text-[#d4d4d4] mb-6">Newsletter</h4>
                <p class="font-inter text-[13px] leading-relaxed text-[#a3a3a3] mb-8">Join the architectural elite. Be the first to know about new drops.</p>
                <div class="flex border-b border-[#525252] pb-2">
                    <input type="email" placeholder="YOUR EMAIL" class="bg-transparent border-none font-inter text-[11px] text-[#f5f5f5] w-full outline-none placeholder-[#737373] tracking-[0.1em]">
                    <button class="text-[#a3a3a3] hover:text-white transition-colors focus:outline-none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Footer Layout -->
        <div class="md:hidden flex flex-col items-center text-center">
            @if($logoLightUrl)
                <img src="{{ $logoLightUrl }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain mb-6">
            @else
                <div class="font-serif text-3xl font-semibold tracking-widest text-black mb-6">{{ strtoupper($siteName) }}</div>
            @endif
            <div class="flex gap-6 mb-8 text-[9px] font-mono tracking-widest text-[#615e57] uppercase">
                @forelse($footerLinks as $link)
                    <a href="{{ url($link['url']) }}" class="hover:text-black">{{ $link['label'] }}</a>
                @empty
                    <a href="#" class="hover:text-black">Privacy</a>
                    <a href="#" class="hover:text-black">Terms</a>
                    <a href="#" class="hover:text-black">Shipping</a>
                @endforelse
            </div>
            <div class="text-[9px] font-mono tracking-widest text-[#615e57] uppercase">{!! $copyright !!}</div>
        </div>

        <!-- Desktop Footer Bottom -->
        <div class="hidden md:flex max-w-[1400px] mx-auto flex-col md:flex-row justify-between pt-8 border-t border-[#404040] font-inter text-[9px] text-[#737373] uppercase tracking-widest">
            <div>{!! $copyright !!}</div>
            <div class="flex gap-8 mt-4 md:mt-0">
                @forelse($footerLinks as $link)
                    <a href="{{ url($link['url']) }}" class="hover:text-white transition-colors">{{ $link['label'] }}</a>
                @empty
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                @endforelse
            </div>
        </div>
    </footer>