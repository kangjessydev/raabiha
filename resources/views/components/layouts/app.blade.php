@props(['title' => null, 'description' => null, 'image' => null, 'header' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />

    @php
        $defaultTitle = \App\Models\SiteSetting::where('key', 'site_name')->value('value') ?? 'Raabiha Olshop';
        $defaultDesc = \App\Models\SiteSetting::where('key', 'site_description')->value('value') ?? 'Modest fashion with modern silhouette and premium quality.';
        
        $finalTitle = isset($title) ? $title . ' - ' . $defaultTitle : $defaultTitle;
        $finalDesc = isset($description) ? $description : $defaultDesc;
    @endphp

    <title>{{ $finalTitle }}</title>
    <meta name="description" content="{{ $finalDesc }}">
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


<link rel='dns-prefetch' href='//fonts.googleapis.com' />
<style id="wp-img-auto-sizes-contain-inline-css">
img:is([sizes=auto i],[sizes^="auto," i]){contain-intrinsic-size:3000px 1500px}
/*# sourceURL=wp-img-auto-sizes-contain-inline-css */
</style>
<style id="wp-block-library-inline-css">
:root{--wp-block-synced-color:#7a00df;--wp-block-synced-color--rgb:122,0,223;--wp-bound-block-color:var(--wp-block-synced-color);--wp-editor-canvas-background:#ddd;--wp-admin-theme-color:#007cba;--wp-admin-theme-color--rgb:0,124,186;--wp-admin-theme-color-darker-10:#006ba1;--wp-admin-theme-color-darker-10--rgb:0,107,160.5;--wp-admin-theme-color-darker-20:#005a87;--wp-admin-theme-color-darker-20--rgb:0,90,135;--wp-admin-border-width-focus:2px}@@media (min-resolution:192dpi){:root{--wp-admin-border-width-focus:1.5px}}.wp-element-button{cursor:pointer}:root .has-very-light-gray-background-color{background-color:#eee}:root .has-very-dark-gray-background-color{background-color:#313131}:root .has-very-light-gray-color{color:#eee}:root .has-very-dark-gray-color{color:#313131}:root .has-vivid-green-cyan-to-vivid-cyan-blue-gradient-background{background:linear-gradient(135deg,#00d084,#0693e3)}:root .has-purple-crush-gradient-background{background:linear-gradient(135deg,#34e2e4,#4721fb 50%,#ab1dfe)}:root .has-hazy-dawn-gradient-background{background:linear-gradient(135deg,#faaca8,#dad0ec)}:root .has-subdued-olive-gradient-background{background:linear-gradient(135deg,#fafae1,#67a671)}:root .has-atomic-cream-gradient-background{background:linear-gradient(135deg,#fdd79a,#004a59)}:root .has-nightshade-gradient-background{background:linear-gradient(135deg,#330968,#31cdcf)}:root .has-midnight-gradient-background{background:linear-gradient(135deg,#020381,#2874fc)}:root{--wp--preset--font-size--normal:16px;--wp--preset--font-size--huge:42px}.has-regular-font-size{font-size:1em}.has-larger-font-size{font-size:2.625em}.has-normal-font-size{font-size:var(--wp--preset--font-size--normal)}.has-huge-font-size{font-size:var(--wp--preset--font-size--huge)}:root .has-text-align-center{text-align:center}:root .has-text-align-left{text-align:left}:root .has-text-align-right{text-align:right}.has-fit-text{white-space:nowrap!important}#end-resizable-editor-section{display:none}.aligncenter{clear:both}.items-justified-left{justify-content:flex-start}.items-justified-center{justify-content:center}.items-justified-right{justify-content:flex-end}.items-justified-space-between{justify-content:space-between}.screen-reader-text{word-wrap:normal!important;border:0;clip-path:inset(50%);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute;width:1px}.screen-reader-text:focus{background-color:#ddd;clip-path:none;color:#444;display:block;font-size:1em;height:auto;left:5px;line-height:normal;padding:15px 23px 14px;text-decoration:none;top:5px;width:auto;z-index:100000}html :where(.has-border-color){border-style:solid}html :where([style*=border-color]){border-style:solid}html :where([style*=border-top-color]){border-top-style:solid}html :where([style*=border-right-color]){border-right-style:solid}html :where([style*=border-bottom-color]){border-bottom-style:solid}html :where([style*=border-left-color]){border-left-style:solid}html :where([style*=border-width]){border-style:solid}html :where([style*=border-top-width]){border-top-style:solid}html :where([style*=border-right-width]){border-right-style:solid}html :where([style*=border-bottom-width]){border-bottom-style:solid}html :where([style*=border-left-width]){border-left-style:solid}html :where(img[class*=wp-image-]){height:auto;max-width:100%}:where(figure){margin:0 0 1em}html :where(.is-position-sticky){--wp-admin--admin-bar--position-offset:var(--wp-admin--admin-bar--height,0px)}@@media screen and (max-width:600px){html :where(.is-position-sticky){--wp-admin--admin-bar--position-offset:0px}}

/*# sourceURL=/wp-includes/css/dist/block-library/common.min.css */
</style>
<style id="classic-theme-styles-inline-css">
/*! This file is auto-generated */
.wp-block-button__link{color:#fff;background-color:#32373c;border-radius:9999px;box-shadow:none;text-decoration:none;padding:calc(.667em + 2px) calc(1.333em + 2px);font-size:1.125em}.wp-block-file__button{background:#32373c;color:#fff;text-decoration:none}
/*# sourceURL=/wp-includes/css/classic-themes.min.css */
</style>

<link rel='stylesheet' id='wc-blocks-style-css' href="{{ asset('wp-content/plugins/woocommerce/assets/client/blocks/wc-blocks.css') }}" media='all' />

<style id="global-styles-inline-css">
:root{--wp--preset--aspect-ratio--square: 1;--wp--preset--aspect-ratio--4-3: 4/3;--wp--preset--aspect-ratio--3-4: 3/4;--wp--preset--aspect-ratio--3-2: 3/2;--wp--preset--aspect-ratio--2-3: 2/3;--wp--preset--aspect-ratio--16-9: 16/9;--wp--preset--aspect-ratio--9-16: 9/16;--wp--preset--color--black: #000000;--wp--preset--color--cyan-bluish-gray: #abb8c3;--wp--preset--color--white: #ffffff;--wp--preset--color--pale-pink: #f78da7;--wp--preset--color--vivid-red: #cf2e2e;--wp--preset--color--luminous-vivid-orange: #ff6900;--wp--preset--color--luminous-vivid-amber: #fcb900;--wp--preset--color--light-green-cyan: #7bdcb5;--wp--preset--color--vivid-green-cyan: #00d084;--wp--preset--color--pale-cyan-blue: #8ed1fc;--wp--preset--color--vivid-cyan-blue: #0693e3;--wp--preset--color--vivid-purple: #9b51e0;--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple: linear-gradient(135deg,rgb(6,147,227) 0%,rgb(155,81,224) 100%);--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan: linear-gradient(135deg,rgb(122,220,180) 0%,rgb(0,208,130) 100%);--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange: linear-gradient(135deg,rgb(252,185,0) 0%,rgb(255,105,0) 100%);--wp--preset--gradient--luminous-vivid-orange-to-vivid-red: linear-gradient(135deg,rgb(255,105,0) 0%,rgb(207,46,46) 100%);--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray: linear-gradient(135deg,rgb(238,238,238) 0%,rgb(169,184,195) 100%);--wp--preset--gradient--cool-to-warm-spectrum: linear-gradient(135deg,rgb(74,234,220) 0%,rgb(151,120,209) 20%,rgb(207,42,186) 40%,rgb(238,44,130) 60%,rgb(251,105,98) 80%,rgb(254,248,76) 100%);--wp--preset--gradient--blush-light-purple: linear-gradient(135deg,rgb(255,206,236) 0%,rgb(152,150,240) 100%);--wp--preset--gradient--blush-bordeaux: linear-gradient(135deg,rgb(254,205,165) 0%,rgb(254,45,45) 50%,rgb(107,0,62) 100%);--wp--preset--gradient--luminous-dusk: linear-gradient(135deg,rgb(255,203,112) 0%,rgb(199,81,192) 50%,rgb(65,88,208) 100%);--wp--preset--gradient--pale-ocean: linear-gradient(135deg,rgb(255,245,203) 0%,rgb(182,227,212) 50%,rgb(51,167,181) 100%);--wp--preset--gradient--electric-grass: linear-gradient(135deg,rgb(202,248,128) 0%,rgb(113,206,126) 100%);--wp--preset--gradient--midnight: linear-gradient(135deg,rgb(2,3,129) 0%,rgb(40,116,252) 100%);--wp--preset--font-size--small: 13px;--wp--preset--font-size--medium: 20px;--wp--preset--font-size--large: 36px;--wp--preset--font-size--x-large: 42px;--wp--preset--spacing--20: 0.44rem;--wp--preset--spacing--30: 0.67rem;--wp--preset--spacing--40: 1rem;--wp--preset--spacing--50: 1.5rem;--wp--preset--spacing--60: 2.25rem;--wp--preset--spacing--70: 3.38rem;--wp--preset--spacing--80: 5.06rem;--wp--preset--shadow--natural: 6px 6px 9px rgba(0, 0, 0, 0.2);--wp--preset--shadow--deep: 12px 12px 50px rgba(0, 0, 0, 0.4);--wp--preset--shadow--sharp: 6px 6px 0px rgba(0, 0, 0, 0.2);--wp--preset--shadow--outlined: 6px 6px 0px -3px rgb(255, 255, 255), 6px 6px rgb(0, 0, 0);--wp--preset--shadow--crisp: 6px 6px 0px rgb(0, 0, 0);}:where(body) { margin: 0; }:where(.is-layout-flex){gap: 0.5em;}:where(.is-layout-grid){gap: 0.5em;}body .is-layout-flex{display: flex;}.is-layout-flex{flex-wrap: wrap;align-items: center;}.is-layout-flex > :is(*, div){margin: 0;}body .is-layout-grid{display: grid;}.is-layout-grid > :is(*, div){margin: 0;}body{padding-top: 0px;padding-right: 0px;padding-bottom: 0px;padding-left: 0px;}:root :where(.wp-element-button, .wp-block-button__link){background-color: #32373c;border-width: 0;color: #fff;font-family: inherit;font-size: inherit;font-style: inherit;font-weight: inherit;letter-spacing: inherit;line-height: inherit;padding-top: calc(0.667em + 2px);padding-right: calc(1.333em + 2px);padding-bottom: calc(0.667em + 2px);padding-left: calc(1.333em + 2px);text-decoration: none;text-transform: inherit;}.has-black-color{color: var(--wp--preset--color--black) !important;}.has-cyan-bluish-gray-color{color: var(--wp--preset--color--cyan-bluish-gray) !important;}.has-white-color{color: var(--wp--preset--color--white) !important;}.has-pale-pink-color{color: var(--wp--preset--color--pale-pink) !important;}.has-vivid-red-color{color: var(--wp--preset--color--vivid-red) !important;}.has-luminous-vivid-orange-color{color: var(--wp--preset--color--luminous-vivid-orange) !important;}.has-luminous-vivid-amber-color{color: var(--wp--preset--color--luminous-vivid-amber) !important;}.has-light-green-cyan-color{color: var(--wp--preset--color--light-green-cyan) !important;}.has-vivid-green-cyan-color{color: var(--wp--preset--color--vivid-green-cyan) !important;}.has-pale-cyan-blue-color{color: var(--wp--preset--color--pale-cyan-blue) !important;}.has-vivid-cyan-blue-color{color: var(--wp--preset--color--vivid-cyan-blue) !important;}.has-vivid-purple-color{color: var(--wp--preset--color--vivid-purple) !important;}.has-black-background-color{background-color: var(--wp--preset--color--black) !important;}.has-cyan-bluish-gray-background-color{background-color: var(--wp--preset--color--cyan-bluish-gray) !important;}.has-white-background-color{background-color: var(--wp--preset--color--white) !important;}.has-pale-pink-background-color{background-color: var(--wp--preset--color--pale-pink) !important;}.has-vivid-red-background-color{background-color: var(--wp--preset--color--vivid-red) !important;}.has-luminous-vivid-orange-background-color{background-color: var(--wp--preset--color--luminous-vivid-orange) !important;}.has-luminous-vivid-amber-background-color{background-color: var(--wp--preset--color--luminous-vivid-amber) !important;}.has-light-green-cyan-background-color{background-color: var(--wp--preset--color--light-green-cyan) !important;}.has-vivid-green-cyan-background-color{background-color: var(--wp--preset--color--vivid-green-cyan) !important;}.has-pale-cyan-blue-background-color{background-color: var(--wp--preset--color--pale-cyan-blue) !important;}.has-vivid-cyan-blue-background-color{background-color: var(--wp--preset--color--vivid-cyan-blue) !important;}.has-vivid-purple-background-color{background-color: var(--wp--preset--color--vivid-purple) !important;}.has-black-border-color{border-color: var(--wp--preset--color--black) !important;}.has-cyan-bluish-gray-border-color{border-color: var(--wp--preset--color--cyan-bluish-gray) !important;}.has-white-border-color{border-color: var(--wp--preset--color--white) !important;}.has-pale-pink-border-color{border-color: var(--wp--preset--color--pale-pink) !important;}.has-vivid-red-border-color{border-color: var(--wp--preset--color--vivid-red) !important;}.has-luminous-vivid-orange-border-color{border-color: var(--wp--preset--color--luminous-vivid-orange) !important;}.has-luminous-vivid-amber-border-color{border-color: var(--wp--preset--color--luminous-vivid-amber) !important;}.has-light-green-cyan-border-color{border-color: var(--wp--preset--color--light-green-cyan) !important;}.has-vivid-green-cyan-border-color{border-color: var(--wp--preset--color--vivid-green-cyan) !important;}.has-pale-cyan-blue-border-color{border-color: var(--wp--preset--color--pale-cyan-blue) !important;}.has-vivid-cyan-blue-border-color{border-color: var(--wp--preset--color--vivid-cyan-blue) !important;}.has-vivid-purple-border-color{border-color: var(--wp--preset--color--vivid-purple) !important;}.has-vivid-cyan-blue-to-vivid-purple-gradient-background{background: var(--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple) !important;}.has-light-green-cyan-to-vivid-green-cyan-gradient-background{background: var(--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan) !important;}.has-luminous-vivid-amber-to-luminous-vivid-orange-gradient-background{background: var(--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange) !important;}.has-luminous-vivid-orange-to-vivid-red-gradient-background{background: var(--wp--preset--gradient--luminous-vivid-orange-to-vivid-red) !important;}.has-very-light-gray-to-cyan-bluish-gray-gradient-background{background: var(--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray) !important;}.has-cool-to-warm-spectrum-gradient-background{background: var(--wp--preset--gradient--cool-to-warm-spectrum) !important;}.has-blush-light-purple-gradient-background{background: var(--wp--preset--gradient--blush-light-purple) !important;}.has-blush-bordeaux-gradient-background{background: var(--wp--preset--gradient--blush-bordeaux) !important;}.has-luminous-dusk-gradient-background{background: var(--wp--preset--gradient--luminous-dusk) !important;}.has-pale-ocean-gradient-background{background: var(--wp--preset--gradient--pale-ocean) !important;}.has-electric-grass-gradient-background{background: var(--wp--preset--gradient--electric-grass) !important;}.has-midnight-gradient-background{background: var(--wp--preset--gradient--midnight) !important;}.has-small-font-size{font-size: var(--wp--preset--font-size--small) !important;}.has-medium-font-size{font-size: var(--wp--preset--font-size--medium) !important;}.has-large-font-size{font-size: var(--wp--preset--font-size--large) !important;}.has-x-large-font-size{font-size: var(--wp--preset--font-size--x-large) !important;}
/*# sourceURL=global-styles-inline-css */
</style>

<link rel='stylesheet' id='woocommerce-layout-css' href="{{ asset('wp-content/plugins/woocommerce/assets/css/woocommerce-layout.css') }}" media='all' />
<link rel='stylesheet' id='woocommerce-smallscreen-css' href="{{ asset('wp-content/plugins/woocommerce/assets/css/woocommerce-smallscreen.css') }}" media='only screen and (max-width: 768px)' />
<link rel='stylesheet' id='woocommerce-general-css' href="{{ asset('wp-content/plugins/woocommerce/assets/css/woocommerce.css') }}" media='all' />
<style id="woocommerce-inline-inline-css">
.woocommerce form .form-row .required { visibility: visible; }
/*# sourceURL=woocommerce-inline-inline-css */
</style>
<link rel='stylesheet' id='raabiha-fonts-css' href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@@400;500;600;700;800&#038;family=Hanken+Grotesk:wght@@300;400;500;600;700&#038;family=JetBrains+Mono:wght@@400;500;700&#038;display=swap' media='all' />
<link rel='stylesheet' id='raabiha-dashboard-css-0-css' href="{{ asset('assets/css/main-CPG7ZTQy.css') }}" media='all' />
<link rel='stylesheet' id='raabiha-dashboard-fonts-css' href='https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@@300;400;500;600;700&#038;family=JetBrains+Mono:wght@@400;500;700&#038;display=swap' media='all' />
<script id="jquery-core-js" src="{{ asset('wp-includes/js/jquery/jquery.min.js') }}"></script>
<script id="jquery-migrate-js" src="{{ asset('wp-includes/js/jquery/jquery-migrate.min.js') }}"></script>
<script data-wp-strategy="defer" defer id="wc-jquery-blockui-js" src="{{ asset('wp-content/plugins/woocommerce/assets/js/jquery-blockui/jquery.blockUI.min.js') }}"></script>
<script id="wc-add-to-cart-js-extra">
var wc_add_to_cart_params = {"ajax_url":"#","wc_ajax_url":"/raabiha/?wc-ajax=%%endpoint%%","i18n_view_cart":"View cart","cart_url":"cart.html","is_cart":"","cart_redirect_after_add":"no"};
//# sourceURL=wc-add-to-cart-js-extra
</script>
<script data-wp-strategy="defer" defer id="wc-add-to-cart-js" src="{{ asset('wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart.min.js') }}"></script>
<script data-wp-strategy="defer" defer id="wc-js-cookie-js" src="{{ asset('wp-content/plugins/woocommerce/assets/js/js-cookie/js.cookie.min.js') }}"></script>
<script id="woocommerce-js-extra">
var woocommerce_params = {"ajax_url":"#","wc_ajax_url":"/raabiha/?wc-ajax=%%endpoint%%","i18n_password_show":"Show password","i18n_password_hide":"Hide password"};
//# sourceURL=woocommerce-js-extra
</script>
<script data-wp-strategy="defer" defer id="woocommerce-js" src="{{ asset('wp-content/plugins/woocommerce/assets/js/frontend/woocommerce.min.js') }}"></script>
	<noscript><style>.woocommerce-product-gallery{ opacity: 1 !important; }</style></noscript>
	
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
</head>
<body class="home blog wp-theme-raabiha-theme theme-raabiha-theme woocommerce-no-js" x-data="{ navLoaded: false }">
        
    <!-- Topbar Promo Marquee -->
    <div class="{{ isset($header) ? 'hidden md:block' : '' }} bg-neutral-900 text-neutral-200 text-[10px] tracking-[0.2em] uppercase py-2 overflow-hidden whitespace-nowrap">
        <div class="inline-block animate-[marquee_20s_linear_infinite]">
            <span class="mr-8">FREE SHIPPING ON ORDERS OVER 500K</span>
            <span class="mr-8">FREE SHIPPING ON ORDERS OVER 500K</span>
            <span class="mr-8">FREE SHIPPING ON ORDERS OVER 500K</span>
            <span class="mr-8">FREE SHIPPING ON ORDERS OVER 500K</span>
        </div>
    </div>
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
    <div id="mobile-sidebar" class="fixed inset-0 z-[60] transform -translate-x-full transition-transform duration-300 md:hidden">
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
                <ul class="flex flex-col gap-6 font-mono text-[11px] uppercase tracking-widest text-[#615e57]">
                    <li><a href="{{ url('/') }}" wire:navigate class="block hover:text-[#064e3b] transition-colors {{ request()->is('/') ? 'text-[#064e3b] font-bold' : '' }}">Beranda</a></li>
                    <li><a href="{{ url('/about') }}" wire:navigate class="block hover:text-[#064e3b] transition-colors {{ request()->is('about') ? 'text-[#064e3b] font-bold' : '' }}">Tentang Kami</a></li>
                    <li><a href="{{ url('/contact') }}" wire:navigate class="block hover:text-[#064e3b] transition-colors {{ request()->is('contact') ? 'text-[#064e3b] font-bold' : '' }}">Lokasi & Kontak</a></li>
                    <li><a href="{{ url('/gallery') }}" wire:navigate class="block hover:text-[#064e3b] transition-colors {{ request()->is('gallery') ? 'text-[#064e3b] font-bold' : '' }}">Galeri</a></li>
                    <li><a href="{{ url('/blog') }}" wire:navigate class="block hover:text-[#064e3b] transition-colors {{ request()->is('blog*') ? 'text-[#064e3b] font-bold' : '' }}">Blog</a></li>
                    <li><a href="{{ url('/shop') }}" wire:navigate class="block hover:text-[#064e3b] transition-colors {{ request()->is('shop*') || request()->is('product*') ? 'text-[#064e3b] font-bold' : '' }}">Katalog</a></li>
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
    <div x-show="!navLoaded" class="animate-pulse pt-[120px] pb-20 px-6 md:px-12 max-w-[1400px] mx-auto w-full min-h-[70vh]">
        <div class="h-10 w-48 bg-[#e5e2de] rounded mb-12"></div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="h-80 bg-[#e5e2de] rounded"></div>
            <div class="h-80 bg-[#e5e2de] rounded"></div>
            <div class="h-80 bg-[#e5e2de] rounded"></div>
            <div class="h-80 bg-[#e5e2de] rounded"></div>
        </div>
    </div>
    
    <div x-show="navLoaded" style="display: none;" x-transition.opacity.duration.300ms>
        {{ $slot }}
    </div>

    @if(!isset($header) && !request()->is('checkout') && !request()->is('cart'))
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
    <div class="{{ (isset($header) || request()->is('checkout') || request()->is('cart')) ? 'hidden md:block' : '' }}">
        <x-global.footer />
    </div>

    @if(!isset($header) && !request()->is('checkout') && !request()->is('cart'))
        <!-- Fixed Bottom Navigation (Mobile Only) -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 bg-[#fcf9f5] border-t border-[#e5e2de] flex justify-between px-6 py-2 z-50">
            <a href="{{ url('/') }}" wire:navigate class="flex flex-col items-center px-4 py-1.5 transition-all duration-200 {{ request()->is('/') ? 'text-[#064e3b] bg-[#064e3b]/10 rounded-2xl' : 'text-[#615e57]' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[9px] font-mono {{ request()->is('/') ? 'font-bold' : '' }}">Home</span>
            </a>
            <a href="{{ url('/shop') }}" wire:navigate class="flex flex-col items-center px-4 py-1.5 transition-all duration-200 {{ request()->is('shop*') || request()->is('product*') ? 'text-[#064e3b] bg-[#064e3b]/10 rounded-2xl' : 'text-[#615e57]' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[9px] font-mono {{ request()->is('shop*') || request()->is('product*') ? 'font-bold' : '' }}">Shop</span>
            </a>
            <a href="{{ url('/cart') }}" wire:navigate class="cart-toggle-btn flex flex-col items-center px-4 py-1.5 transition-all duration-200 relative {{ request()->is('cart') ? 'text-[#064e3b] bg-[#064e3b]/10 rounded-2xl' : 'text-[#615e57]' }}">
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
                        
                        <a href="/account" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                            <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="font-sans text-sm text-[#1c1c1a]">Dasbor Pelanggan</span>
                        </a>
                        
                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                                <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="font-sans text-sm text-[#1c1c1a]">Admin Panel</span>
                            </a>
                        @elseif(auth()->user()->hasRole('reseller'))
                            <a href="/reseller-dashboard" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
                                <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="font-sans text-sm text-[#1c1c1a]">Portal Reseller</span>
                            </a>
                        @endif

                        <a href="/cart" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-[#f0ede9] transition-colors">
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

    		<script type="text/javascript">
		jQuery(document).ready(function($) {
    $(document).on('change', 'input[name="radio-control-wc-payment-method-options"]', function() {
        const { extensionCartUpdate } = wc.blocksCheckout;

extensionCartUpdate( {
	namespace: 'checkout-blocks-tripay',
	data: {
		payment_method: document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked').value
	},
} );
    });
});

		</script>
		<script type="speculationrules">
{"prefetch":[{"source":"document","where":{"and":[{"href_matches":"/raabiha/*"},{"not":{"href_matches":["/raabiha/wp-*.php","/raabiha/wp-admin/*","/raabiha/wp-content/uploads/*","/raabiha/wp-content/*","/raabiha/wp-content/plugins/*","/raabiha/wp-content/themes/raabiha-theme/*","/raabiha/*\\?(.+)"]}},{"not":{"selector_matches":"a[rel~=\"nofollow\"]"}},{"not":{"selector_matches":".no-prefetch, .no-prefetch a"}}]},"eagerness":"conservative"}]}
</script>
	<script>
		(function () {
			var c = document.body.className;
			c = c.replace(/woocommerce-no-js/, 'woocommerce-js');
			document.body.className = c;
		})();
	</script>
	<script id="sourcebuster-js-js" src="{{ asset('wp-content/plugins/woocommerce/assets/js/sourcebuster/sourcebuster.min.js') }}"></script>
<script id="wc-order-attribution-js-extra">
var wc_order_attribution = {"params":{"lifetime":1.0000000000000000818030539140313095458623138256371021270751953125e-5,"session":30,"base64":false,"ajaxurl":"index.htmlwp-admin/admin-ajax.php","prefix":"wc_order_attribution_","allowTracking":true},"fields":{"source_type":"current.typ","referrer":"current_add.rf","utm_campaign":"current.cmp","utm_source":"current.src","utm_medium":"current.mdm","utm_content":"current.cnt","utm_id":"current.id","utm_term":"current.trm","utm_source_platform":"current.plt","utm_creative_format":"current.fmt","utm_marketing_tactic":"current.tct","session_entry":"current_add.ep","session_start_time":"current_add.fd","session_pages":"session.pgs","session_count":"udata.vst","user_agent":"udata.uag"}};
//# sourceURL=wc-order-attribution-js-extra
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
</body>
</html>
