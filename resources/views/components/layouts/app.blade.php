@props(['title' => null, 'description' => null, 'image' => null, 'header' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />

    @php
        $siteName = \App\Models\SiteSetting::where('key', 'site_name')->value('value') ?? 'Raabiha Olshop';
        $defaultDesc = \App\Models\SiteSetting::where('key', 'site_description')->value('value') ?? 'Modest fashion with modern silhouette and premium quality.';
        
        $homeMetaTitle = \App\Models\SiteSetting::where('key', 'home_meta_title')->value('value');
        $homeMetaDesc = \App\Models\SiteSetting::where('key', 'home_meta_description')->value('value');
        
        $isHome = request()->is('/');
        
        $defaultTitle = $isHome ? ($homeMetaTitle ?: $siteName) : $siteName;
        $defaultDesc = $isHome ? ($homeMetaDesc ?: $defaultDesc) : $defaultDesc;
        
        $finalTitle = isset($title) ? $title . ' - ' . $siteName : $defaultTitle;
        $finalDesc = isset($description) ? $description : $defaultDesc;

        $faviconId = \App\Models\SiteSetting::where('key', 'site_favicon')->value('value');
        $faviconMedia = $faviconId ? \Awcodes\Curator\Models\Media::find($faviconId) : null;
        $faviconUrl = $faviconMedia ? Storage::url($faviconMedia->path) : asset('favicon.ico');
    @endphp

    <title>{{ $finalTitle }}</title>
    <meta name="description" content="{{ $finalDesc }}">
    <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}" />
    <link rel="canonical" href="{{ url()->current() }}" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="{{ $finalTitle }}" />
    <meta property="og:description" content="{{ $finalDesc }}" />
    <meta property="og:site_name" content="{{ $defaultTitle }}" />
    @if(isset($image))
    <meta property="og:image" content="{{ $image }}" />
    @endif

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" content="{{ url()->current() }}" />
    <meta name="twitter:title" content="{{ $finalTitle }}" />
    <meta name="twitter:description" content="{{ $finalDesc }}" />
    @if(isset($image))
    <meta name="twitter:image" content="{{ $image }}" />
    @endif



<link rel='stylesheet' id='raabiha-fonts-css' href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Hanken+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap' media='all' />
<link rel='stylesheet' id='raabiha-dashboard-css-0-css' href="{{ asset('assets/css/main-CPG7ZTQy.css') }}" media='all' />
<link rel='stylesheet' id='raabiha-dashboard-fonts-css' href='https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap' media='all' />

	
    <!-- Tailwind CSS CDN and configuration -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              raabiha: {
                primary:   '#222523',
                secondary: '#615E57',
                emerald:   '#0B4E26',
                ivory:     '#FAF7F0',
                tertiary:  '#2F0D05',
                surface: {
                  DEFAULT: '#FAF7F0',
                  2:       '#F2EFE8',
                  3:       '#E5E1D8',
                },
              },
            },
            fontFamily: {
              sans: ['"Poppins"', 'sans-serif'],
              serif: ['"Playfair Display"', 'Georgia', 'serif'],
              mono: ['"JetBrains Mono"', 'monospace'],
              playfair: ['"Playfair Display"', 'Georgia', 'serif'],
              inter: ['"Inter"', 'system-ui', 'sans-serif'],
            },
          }
        }
      }
    </script>
    @livewireStyles
    @php
        $gaId = \App\Models\SiteSetting::where('key', 'google_analytics_id')->value('value');
        $metaId = \App\Models\SiteSetting::where('key', 'meta_pixel_id')->value('value');
        $tiktokId = \App\Models\SiteSetting::where('key', 'tiktok_pixel_id')->value('value');
        $scriptsHeader = \App\Models\SiteSetting::where('key', 'scripts_header')->value('value');
    @endphp

    <!-- Partytown Configuration & Script -->
    <script>
      partytown = {
        forward: ['dataLayer.push', 'fbq', 'ttq.load', 'ttq.page', 'ttq.track', 'ttq.identify']
      };
    </script>
    <script src="/partytown/partytown.js"></script>

    @if($gaId)
    <!-- Google tag (gtag.js) -->
    <script type="text/partytown" async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script type="text/partytown">
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $gaId }}');
    </script>
    @endif

    @if($metaId)
    <!-- Meta Pixel Code -->
    <script type="text/partytown">
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '{{ $metaId }}');
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id={{ $metaId }}&ev=PageView&noscript=1"
    /></noscript>
    @endif

    @if($tiktokId)
    <!-- TikTok Pixel Code Start -->
    <script type="text/partytown">
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
      ttq.load('{{ $tiktokId }}');
      ttq.page();
    }(window, document, 'ttq');
    </script>
    <!-- TikTok Pixel Code End -->
    @endif

    @if($scriptsHeader)
    {!! $scriptsHeader !!}
    @endif

    <!-- Phantom UI Skeleton Loader -->
    <script src="https://cdn.jsdelivr.net/npm/@aejkatappaja/phantom-ui/dist/phantom-ui.cdn.js"></script>
    <!-- Lenis Smooth Scroll CSS -->
    <style>
        html.lenis, html.lenis body {
            height: auto;
        }
        .lenis.lenis-smooth {
            scroll-behavior: auto !important;
        }
        .lenis.lenis-smooth [data-lenis-prevent] {
            overscroll-behavior: contain;
        }
        .lenis.lenis-stopped {
            overflow: hidden;
        }
        .lenis.lenis-scrolling iframe {
            pointer-events: none;
        }
        
        /* Override Phantom UI overflow to allow sticky elements */
        phantom-ui {
            overflow: visible !important;
        }
    </style>
</head>
<body class="home blog wp-theme-raabiha-theme theme-raabiha-theme woocommerce-no-js" x-data="{ navLoaded: false }">
        
    @php
        $topbar = \App\Models\TopbarAnnouncement::first();
    @endphp

    @if($topbar && $topbar->is_active)
    <!-- Topbar Promo Marquee -->
    <div class="{{ isset($header) ? 'hidden md:block' : '' }} text-[10px] tracking-[0.2em] uppercase py-2 overflow-hidden whitespace-nowrap" style="background-color: {{ $topbar->bg_color ?? '#000000' }}; color: {{ $topbar->text_color ?? '#ffffff' }};">
        <div class="inline-block md:w-full md:text-center animate-[marquee_20s_linear_infinite] md:animate-none pl-[100%] md:pl-0">
            <span class="[&_a]:underline [&_a]:font-bold [&_strong]:font-bold [&_em]:italic mr-8 md:mr-0">{!! strip_tags($topbar->text, '<strong><em><a>') !!}</span>
            <span class="[&_a]:underline [&_a]:font-bold [&_strong]:font-bold [&_em]:italic mr-8 md:hidden">{!! strip_tags($topbar->text, '<strong><em><a>') !!}</span>
        </div>
    </div>
    @endif

    @php
        $holidayMode = \App\Models\SiteSetting::where('key', 'store_holiday_mode')->value('value');
        $holidayMessage = \App\Models\SiteSetting::where('key', 'store_holiday_message')->value('value') ?? 'Mohon maaf, toko kami sedang libur.';
    @endphp

    @if($holidayMode)
    <!-- Holiday Mode Banner -->
    <div class="bg-red-700 text-white text-center py-3 px-4 shadow-sm z-[100] relative">
        <p class="text-xs md:text-sm font-sans max-w-4xl mx-auto flex items-center justify-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>{{ $holidayMessage }}</span>
        </p>
    </div>
    @endif
    <style>
    @@keyframes marquee {
        0% { transform: translateX(0%); }
        100% { transform: translateX(-50%); }
    }
    
    @@keyframes slideInRight {
        0% { transform: translateX(100%); opacity: 0; }
        100% { transform: translateX(0); opacity: 1; }
    }
    .page-slide-in {
        animation: slideInRight 0.35s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    }
    
    /* Global WooCommerce Sale Badge Override */
    span.onsale {
        display: none !important;
    }
    
    /* Hide default WooCommerce injected View Cart link */
    .woocommerce-variation-add-to-cart .added_to_cart,
    .woocommerce div.product form.cart .added_to_cart {
        display: none !important;
    }
    
    /* ==========================================================================
       MINI CART OVERRIDES
       ========================================================================== */
    /* Remove default WooCommerce floats on variations */
    .raabiha-mini-cart-variation dl { margin: 0; padding: 0; }
    .raabiha-mini-cart-variation dt { display: inline-block; font-weight: normal; margin-right: 2px; }
    .raabiha-mini-cart-variation dt::after { content: ':'; }
    .raabiha-mini-cart-variation dd { display: inline-block; margin: 0 8px 0 0; }
    .raabiha-mini-cart-variation p { display: inline; margin: 0; }
    
    /* Force Grid Table Layout for Mini Cart Items (Overrides WooCommerce) */
    .mini-cart-content .woocommerce-mini-cart-item {
        display: grid !important;
        grid-template-columns: 48px 1fr 24px auto !important;
        grid-template-rows: auto auto !important;
        grid-template-areas: 
            "img title qty price"
            "img var   qty price" !important;
        gap: 0 0.75rem !important;
        align-items: center !important;
        padding-bottom: 1.5rem !important;
        margin-bottom: 1.5rem !important;
        border-bottom: 1px solid #e5e2de !important;
        position: relative !important;
    }
    
    /* Expose img and title to the grid */
    .mini-cart-content .woocommerce-mini-cart-item > a:not(.remove) {
        display: contents !important;
    }
    
    /* Product Image Container */
    .mini-cart-content .woocommerce-mini-cart-item img {
        grid-area: img;
        width: 48px !important;
        height: 60px !important;
        border-radius: 4px !important;
        background-color: #f2efe8 !important;
        object-fit: cover !important;
        float: none !important;
        margin: 0 !important;
    }
    
    /* Title */
    .raabiha-item-title {
        grid-area: title;
        align-self: end !important;
        font-size: 11px !important;
        font-weight: bold !important;
        line-height: 1.2 !important;
        color: #1c1c1a !important;
        padding-top: 4px !important;
    }
    
    /* Variation */
    .mini-cart-content .woocommerce-mini-cart-item dl.variation {
        grid-area: var;
        align-self: start !important;
        margin-top: 4px !important;
    }
    
    /* Quantity */
    .r-qty {
        grid-area: qty;
        font-size: 11px !important;
        color: #615e57 !important;
        text-align: center !important;
    }
    
    /* Price */
    .r-price {
        grid-area: price;
        font-size: 12px !important;
        font-weight: bold !important;
        color: #1c1c1a !important;
        text-align: right !important;
    }
    
    /* Remove Button (Positioned absolute top right) */
    .mini-cart-content .woocommerce-mini-cart-item a.remove {
        position: absolute !important;
        top: -8px !important;
        right: -8px !important;
        left: auto !important;
        width: 24px !important;
        height: 24px !important;
        color: #9ca3af !important;
        background: transparent !important;
        font-size: 20px !important;
        font-weight: 300 !important;
        line-height: 24px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 50% !important;
    }
    .mini-cart-content .woocommerce-mini-cart-item a.remove:hover {
        color: #ef4444 !important;
        background-color: #fee2e2 !important;
    }
    
    /* Subtotal */
    .woocommerce-mini-cart__total strong {
        font-size: 11px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #615e57;
        font-weight: normal;
    }
    .woocommerce-mini-cart__total .woocommerce-Price-amount {
        font-size: 16px;
        font-weight: bold;
        color: #1c1c1a;
    }
    
    /* Action Buttons */
    .woocommerce-mini-cart__buttons .button {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 3.5rem;
        font-family: monospace;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        text-decoration: none;
        border-radius: 0;
        transition: all 0.2s ease;
        margin-bottom: 0 !important;
    }
    .woocommerce-mini-cart__buttons .button:not(.checkout) {
        background-color: transparent !important;
        color: #1c1c1a !important;
        border: 1px solid #1c1c1a !important;
    }
    .woocommerce-mini-cart__buttons .button:not(.checkout):hover {
        background-color: #f2efe8 !important;
    }
    .woocommerce-mini-cart__buttons .button.checkout {
        background-color: #09493B !important;
        color: #ffffff !important;
        border: 1px solid #09493B !important;
    }
    .woocommerce-mini-cart__buttons .button.checkout:hover {
        background-color: #07362c !important;
        border-color: #07362c !important;
    }
    </style>
    
    
    @if(isset($header))
        <div class="md:hidden">
            {{ $header }}
        </div>
        <div class="hidden md:block">
            <x-global.navbar />
        </div>
    @else
        <x-global.navbar />
    @endif

    <!-- Mobile Sidebar Menu -->
    <div id="mobile-sidebar" class="fixed inset-0 z-[110] transform -translate-x-full transition-transform duration-300 md:hidden">
        <!-- Backdrop -->
        <div id="mobile-sidebar-backdrop" class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300 pointer-events-none"></div>
        <!-- Sidebar Content -->
        <div class="absolute inset-y-0 left-0 w-[280px] bg-[#fcf9f5] shadow-xl flex flex-col">
            <div class="flex items-center justify-between p-6 border-b border-[#e5e2de]">
                <span class="text-xl font-bold tracking-widest text-[#064e3b] font-serif uppercase">MENU</span>
                <button id="mobile-sidebar-close" class="text-[#1c1c1a] hover:text-red-500 transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                @php
                    $rawNavbarLinks = \App\Models\SiteSetting::where('key', 'navbar_links')->value('value');
                    $navbarLinks = $rawNavbarLinks ? json_decode($rawNavbarLinks, true) : [];
                    if (!is_array($navbarLinks)) $navbarLinks = [];
                @endphp
                <ul class="flex flex-col gap-6 font-mono text-[11px] uppercase tracking-widest text-[#615e57]">
                    @foreach($navbarLinks as $link)
                        @php
                            $path = ltrim(parse_url($link['url'], PHP_URL_PATH) ?? '', '/');
                            $isActive = $path === '' ? request()->is('/') : (request()->is($path) || request()->is($path . '/*') || ($path === 'shop' && request()->is('product*')));
                        @endphp
                        <li><a href="{{ url($link['url']) }}" wire:navigate.hover class="block hover:text-[#064e3b] transition-colors {{ $isActive ? 'text-[#064e3b] font-bold' : '' }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Mini Cart Overlay removed -->

    <script>
        document.addEventListener('livewire:navigated', function() {
            // Sidebar Logic
            const toggle = document.getElementById('mobile-menu-toggle');
            const close = document.getElementById('mobile-sidebar-close');
            const sidebar = document.getElementById('mobile-sidebar');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
            
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100');
                document.body.style.overflow = 'hidden';
            }
            
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                document.body.style.overflow = '';
            }
            
            if(toggle && close && sidebar) {
                toggle.addEventListener('click', openSidebar);
                close.addEventListener('click', closeSidebar);
                backdrop.addEventListener('click', closeSidebar);
            }

            // Search Overlay Logic
            const searchToggles = document.querySelectorAll('.search-toggle-btn');
            const searchOverlay = document.getElementById('search-overlay');
            const searchClose = document.getElementById('search-close');
            const searchInput = searchOverlay ? searchOverlay.querySelector('input[type="search"]') : null;

            function toggleSearch(e) {
                e.preventDefault();
                if(searchOverlay) {
                    const isOpen = !searchOverlay.classList.contains('-translate-y-full');
                    if(isOpen) {
                        searchOverlay.classList.add('-translate-y-full', 'opacity-0', 'pointer-events-none');
                    } else {
                        searchOverlay.classList.remove('-translate-y-full', 'opacity-0', 'pointer-events-none');
                        if(searchInput) setTimeout(() => searchInput.focus(), 300);
                    }
                }
            }

            function closeSearch() {
                if(searchOverlay) {
                    searchOverlay.classList.add('-translate-y-full', 'opacity-0', 'pointer-events-none');
                }
            }

            searchToggles.forEach(btn => btn.addEventListener('click', toggleSearch));
            if(searchClose) searchClose.addEventListener('click', closeSearch);

            // Mini Cart JS Logic removed as user requested direct link to cart page

            // Desktop Profile Dropdown
            const profileToggle = document.getElementById('desktop-profile-toggle');
            const profileDropdown = document.getElementById('desktop-profile-dropdown');
            
            if(profileToggle && profileDropdown) {
                profileToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isOpen = !profileDropdown.classList.contains('opacity-0');
                    if(isOpen) {
                        profileDropdown.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
                    } else {
                        profileDropdown.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
                    }
                });

                document.addEventListener('click', function(e) {
                    if(!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                        profileDropdown.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
                    }
                });
            }
        });
    </script>


    <!-- PAGE CONTENT WRAPPER -->
    <phantom-ui :loading="!navLoaded">
        <div class="w-full min-h-[70vh]">
            {{ $slot }}
        </div>
    </phantom-ui>

    @if(!isset($header) && !request()->is('checkout') && !request()->is('cart') && !request()->is('shop*') && !request()->is('katalog*'))
        <!-- Mobile Newsletter Block (Before Footer) -->
        <div class="md:hidden bg-black text-white px-6 py-16 text-center">
            <div class="text-[9px] font-mono tracking-[0.2em] uppercase mb-4 text-[#a3a3a3]">The Inner Circle</div>
            <h2 class="text-3xl font-serif mb-4">Join our movement.</h2>
            <p class="text-[13px] font-sans text-[#d4d4d4] mb-8 max-w-[280px] mx-auto">Be the first to access limited drops and editorial stories.</p>
            <div class="border-b border-[#404040] pb-2 mb-6 text-left">
                <input type="email" placeholder="YOUR EMAIL ADDRESS" class="bg-transparent border-none text-[11px] font-mono tracking-[0.1em] text-white w-full outline-none placeholder-[#737373]">
            </div>
            <button class="bg-[#064e3b] text-white text-[10px] font-mono tracking-[0.1em] uppercase py-4 w-full">SUBSCRIBE</button>
        </div>
    @endif
    
    <!-- Custom Minimal Footer -->
    @if(!request()->is('shop*') && !request()->is('katalog*'))
    <div class="{{ (isset($header) || request()->is('checkout') || request()->is('cart')) ? 'hidden md:block' : '' }}">
        <x-global.footer />
    </div>
    @endif

    @if(!isset($header) && !request()->is('checkout') && !request()->is('cart'))
        <!-- Fixed Bottom Navigation (Mobile Only) -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 bg-[#fcf9f5] border-t border-[#e5e2de] flex justify-between px-6 py-2 z-50">
            <a href="{{ url('/') }}" wire:navigate.hover class="flex flex-col items-center px-4 py-1.5 transition-all duration-200 {{ request()->is('/') ? 'text-[#064e3b] bg-[#064e3b]/10 rounded-2xl' : 'text-[#615e57]' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[9px] font-mono {{ request()->is('/') ? 'font-bold' : '' }}">Home</span>
            </a>
            <a href="{{ url('/shop') }}" wire:navigate.hover class="flex flex-col items-center px-4 py-1.5 transition-all duration-200 {{ request()->is('shop*') || request()->is('product*') ? 'text-[#064e3b] bg-[#064e3b]/10 rounded-2xl' : 'text-[#615e57]' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[9px] font-mono {{ request()->is('shop*') || request()->is('product*') ? 'font-bold' : '' }}">Shop</span>
            </a>
            <a href="{{ url('/cart') }}" wire:navigate.hover class="cart-toggle-btn flex flex-col items-center px-4 py-1.5 transition-all duration-200 relative {{ request()->is('cart') ? 'text-[#064e3b] bg-[#064e3b]/10 rounded-2xl' : 'text-[#615e57]' }}">
                <div class="relative">
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="raabiha-cart-count-badge absolute -top-1 -right-2 bg-[#064e3b] text-white text-[9px] font-bold w-3 h-3 rounded-full flex items-center justify-center hidden">0</span>
                </div>
                <span class="text-[9px] font-mono {{ request()->is('cart') ? 'font-bold' : '' }}">Cart</span>
            </a>
            <a href="#wishlist" class="flex flex-col items-center text-[#615e57] px-4 py-1.5 transition-all duration-200">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                <span class="text-[9px] font-mono ">Wishlist</span>
            </a>
            <!-- Mobile Profile Button -->
            <button id="mobile-profile-toggle" type="button" class="flex flex-col items-center text-[#615e57] px-4 py-1.5 focus:outline-none transition-all duration-200">
                <div class="w-5 h-5 mb-1 flex items-center justify-center rounded-full overflow-hidden">
                    @auth
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=064e3b&color=fff" alt="Profile" class="w-full h-full object-cover">
                    @else
                        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    @endauth
                </div>
                <span class="text-[9px] font-mono ">Profile</span>
            </button>
        </div>
    @endif
    
    <!-- Mobile Profile Bottom Sheet -->
    <div id="mobile-profile-sheet-overlay" class="fixed inset-0 z-[70] flex flex-col justify-end pointer-events-none md:hidden">
        <!-- Backdrop -->
        <div id="mobile-profile-sheet-backdrop" class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300 pointer-events-none"></div>
        
        <!-- Sheet -->
        <div id="mobile-profile-sheet" class="relative w-full bg-[#fcf9f5] shadow-[0_-10px_40px_rgba(0,0,0,0.1)] rounded-t-3xl transform translate-y-full transition-transform duration-300 pointer-events-auto flex flex-col max-h-[85vh]">
            <div class="flex justify-center pt-4 pb-2" id="mobile-profile-sheet-handle">
                <div class="w-12 h-1.5 bg-[#e5e2de] rounded-full"></div>
            </div>
            <div class="px-6 pb-8 pt-4 overflow-y-auto">
                <h3 class="text-lg font-serif font-bold text-[#064e3b] mb-6">Profile Menu</h3>
                <div class="flex flex-col gap-2">
                    @auth
                        <div class="px-4 py-2 border-b border-[#e5e2de] mb-2 flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=064e3b&color=fff" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                            <div>
                                <span class="block text-sm font-sans font-bold text-[#1c1c1a]">{{ auth()->user()->name }}</span>
                                <span class="block text-xs font-mono text-[#615e57] truncate max-w-[200px]">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                        
                        <a href="/account" wire:navigate.hover class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                            <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="font-sans text-sm text-[#1c1c1a]">Dasbor Pelanggan</span>
                        </a>
                        
                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                                <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="font-sans text-sm text-[#1c1c1a]">Admin Panel</span>
                            </a>
                        @elseif(auth()->user()->hasRole('reseller'))
                            <a href="/reseller-dashboard" wire:navigate.hover class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                                <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="font-sans text-sm text-[#1c1c1a]">Portal Reseller</span>
                            </a>
                        @endif

                        <a href="/cart" wire:navigate.hover class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                            <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span class="font-sans text-sm text-[#1c1c1a]">Keranjang Saya</span>
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="m-0 mt-2">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-50 w-full transition-colors">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                <span class="font-sans text-sm font-medium text-red-600">Keluar (Logout)</span>
                            </button>
                        </form>
                    @else
                        <a href="/login" class="flex items-center gap-4 p-4 rounded-xl bg-[#064e3b] text-white hover:bg-opacity-90 transition-colors justify-center mt-2">
                            <span class="font-sans text-sm font-medium tracking-wide">Log In</span>
                        </a>
                        <a href="/register" class="flex items-center gap-4 p-4 rounded-xl border border-[#064e3b] text-[#064e3b] hover:bg-[#064e3b]/5 transition-colors justify-center mt-2">
                            <span class="font-sans text-sm font-medium tracking-wide">Daftar Akun</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sheetToggle = document.getElementById('mobile-profile-toggle');
            const sheetOverlay = document.getElementById('mobile-profile-sheet-overlay');
            const sheetBackdrop = document.getElementById('mobile-profile-sheet-backdrop');
            const sheetContent = document.getElementById('mobile-profile-sheet');
            const sheetHandle = document.getElementById('mobile-profile-sheet-handle');

            function openSheet() {
                if(sheetOverlay && sheetContent && sheetBackdrop) {
                    sheetOverlay.classList.remove('pointer-events-none');
                    sheetBackdrop.classList.remove('opacity-0', 'pointer-events-none');
                    sheetBackdrop.classList.add('opacity-100', 'pointer-events-auto');
                    sheetContent.classList.remove('translate-y-full');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeSheet() {
                if(sheetOverlay && sheetContent && sheetBackdrop) {
                    sheetContent.classList.add('translate-y-full');
                    sheetBackdrop.classList.remove('opacity-100', 'pointer-events-auto');
                    sheetBackdrop.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => sheetOverlay.classList.add('pointer-events-none'), 300);
                    document.body.style.overflow = '';
                }
            }

            if(sheetToggle) sheetToggle.addEventListener('click', openSheet);
            if(sheetBackdrop) sheetBackdrop.addEventListener('click', closeSheet);
            if(sheetHandle) sheetHandle.addEventListener('click', closeSheet);
        });
    </script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        let lastScrollY = window.scrollY;
        let scrollTimeout;
        const header = document.getElementById('smart-navbar');
        
        if (header) {
            window.addEventListener('scroll', () => {
                clearTimeout(scrollTimeout);
                
                if (window.scrollY > lastScrollY && window.scrollY > 150) {
                    // Scroll down: hide navbar
                    header.style.transform = 'translateY(-100%)';
                } else {
                    // Scroll up: show navbar
                    header.style.transform = 'translateY(0)';
                }
                
                lastScrollY = window.scrollY;
                
                // Show after scroll stops
                scrollTimeout = setTimeout(() => {
                    header.style.transform = 'translateY(0)';
                }, 800);
            }, { passive: true });
        }
    });
</script>
    <livewire:mini-cart />
    @php
        $scriptsFooter = \App\Models\SiteSetting::where('key', 'scripts_footer')->value('value');
    @endphp
    @if($scriptsFooter)
        {!! $scriptsFooter !!}
    @endif
    @php
        $currentPlacement = 'all';
        if (request()->is('/')) {
            $currentPlacement = 'home';
        } elseif (request()->is('shop*') || request()->is('product*') || request()->is('katalog*')) {
            $currentPlacement = 'catalog';
        }
        
        $promoBanner = \App\Models\PromoBanner::where('is_active', true)
            ->whereIn('placement', ['all', $currentPlacement])
            ->first();
    @endphp

    @if($promoBanner)
        @php
            $bannerMedia = \Awcodes\Curator\Models\Media::find($promoBanner->image);
        @endphp
        @if($bannerMedia)
            <div x-data="{ showPromo: false }" x-init="setTimeout(() => showPromo = true, 1500)" x-show="showPromo" class="fixed inset-0 z-[1000] flex items-center justify-center p-4 sm:p-6" style="display: none;">
                <div x-show="showPromo" x-transition.opacity class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPromo = false"></div>
                
                <div x-show="showPromo" x-transition.scale.origin.center.duration.300ms class="relative z-10 w-full max-w-md bg-[#fcf9f5] shadow-2xl rounded-sm overflow-hidden">
                    <button @click="showPromo = false" class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-white/80 backdrop-blur text-black rounded-full hover:bg-white transition-colors z-20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    
                    @if($promoBanner->link)
                    <a href="{{ $promoBanner->link }}" class="block">
                    @endif
                        <img src="{{ Storage::url($bannerMedia->path) }}" alt="{{ $promoBanner->title }}" class="w-full h-auto object-cover max-h-[75vh]">
                    @if($promoBanner->link)
                    </a>
                    @endif
                </div>
            </div>
        @endif
    @endif
    <!-- Lenis Smooth Scroll Initialization -->
    <script src="https://unpkg.com/lenis@1.1.2/dist/lenis.min.js"></script>
    <script>
        let lenis;
        let rafId;

        function initLenis() {
            if (lenis) {
                lenis.destroy();
                cancelAnimationFrame(rafId);
            }

            lenis = new Lenis({
                duration: 1.2,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), 
                direction: 'vertical',
                gestureDirection: 'vertical',
                smooth: true,
                mouseMultiplier: 1,
                smoothTouch: false,
                touchMultiplier: 2,
            });

            function raf(time) {
                lenis.raf(time);
                rafId = requestAnimationFrame(raf);
            }

            rafId = requestAnimationFrame(raf);
        }

        document.addEventListener('DOMContentLoaded', initLenis);

        document.addEventListener('livewire:navigated', () => {
            window.scrollTo(0, 0);
            initLenis();
        });
    </script>
</body>
</html>
