<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    
    <!-- Topbar Promo Marquee -->
    <?php $promo_text = get_option('raabiha_header_promo_text', 'FREE SHIPPING ON ORDERS OVER RP 500.000'); ?>
    <?php if ( ! empty( $promo_text ) ) : ?>
    <div class="bg-neutral-900 text-neutral-200 text-[10px] tracking-[0.2em] uppercase py-2 overflow-hidden whitespace-nowrap">
        <div class="inline-block animate-[marquee_20s_linear_infinite]">
            <span class="mr-8"><?php echo esc_html( $promo_text ); ?></span>
            <span class="mr-8"><?php echo esc_html( $promo_text ); ?></span>
            <span class="mr-8"><?php echo esc_html( $promo_text ); ?></span>
            <span class="mr-8"><?php echo esc_html( $promo_text ); ?></span>
        </div>
    </div>
    <style>
    @keyframes marquee {
        0% { transform: translateX(0%); }
        100% { transform: translateX(-50%); }
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
    <?php endif; ?>

    <?php if ( function_exists('is_checkout') && is_checkout() ) : ?>
    <style>
    /* ======================================================= */
    /* RAABIHA CUSTOM CHECKOUT TYPOGRAPHY & COLORS             */
    /* ======================================================= */
    
    /* Global Font & Text Color */
    .woocommerce-checkout {
        font-family: 'Hanken Grotesk', sans-serif !important;
        color: #1c1c1a !important;
        font-size: 14px !important;
    }
    
    /* Headings (Billing Details, Your Order) */
    .woocommerce-checkout h3 {
        font-family: 'Playfair Display', serif !important;
        color: #064e3b !important;
        font-size: 24px !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #e5e2de !important;
        padding-bottom: 10px !important;
        margin-bottom: 20px !important;
    }
    
    /* Labels */
    .woocommerce-checkout label {
        font-family: 'Hanken Grotesk', sans-serif !important;
        color: #3f3f46 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }
    
    /* Input Fields */
    .woocommerce-checkout input[type="text"],
    .woocommerce-checkout input[type="email"],
    .woocommerce-checkout input[type="tel"],
    .woocommerce-checkout textarea {
        font-family: 'Hanken Grotesk', sans-serif !important;
        font-size: 14px !important;
        color: #1c1c1a !important;
        border: 1px solid #d4d4d8 !important;
        padding: 10px 12px !important;
        border-radius: 4px !important;
    }
    .woocommerce-checkout input:focus,
    .woocommerce-checkout textarea:focus {
        border-color: #064e3b !important;
        box-shadow: 0 0 0 1px #064e3b !important;
    }
    
    /* Order Summary Text */
    .woocommerce-checkout table.shop_table th,
    .woocommerce-checkout table.shop_table td {
        font-family: 'Hanken Grotesk', sans-serif !important;
        font-size: 14px !important;
        color: #1c1c1a !important;
    }
    
    /* Place Order Button */
    .woocommerce-checkout button#place_order {
        background-color: #09493B !important;
        color: #ffffff !important;
        font-family: 'JetBrains Mono', monospace !important;
        font-size: 14px !important;
        font-weight: bold !important;
        letter-spacing: 1px !important;
        text-transform: uppercase !important;
        padding: 16px 24px !important;
        border-radius: 4px !important;
        border: none !important;
        transition: background-color 0.3s ease !important;
        width: 100% !important;
        margin-top: 20px !important;
    }
    .woocommerce-checkout button#place_order:hover {
        background-color: #07362c !important;
    }
    </style>
    <?php endif; ?>

    <?php
    $is_single_product = function_exists('is_product') && is_product();
    $header_classes = $is_single_product 
        ? "sticky md:sticky top-0 z-50 transition-all duration-300 md:bg-[#fcf9f5] md:border-b md:border-[#e5e2de]" 
        : "sticky top-0 z-50 bg-[#fcf9f5] border-b border-[#e5e2de] transition-all duration-300";
    ?>
    <header class="<?php echo esc_attr( $header_classes ); ?>">
        
        <!-- ==================== DESKTOP LAYOUT ==================== -->
        <div class="hidden md:flex items-center justify-between px-12 py-5 relative z-50 bg-[#fcf9f5]">
            <!-- Desktop Logo -->
            <a href="<?php echo esc_url( home_url() ); ?>" class="block hover:opacity-80 transition-opacity">
                <!-- Gunakan gambar statis logo-desktop.png -->
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-desktop.png" alt="Raabiha" class="h-10 w-auto object-contain" onerror="this.outerHTML='<span class=\'text-2xl font-bold tracking-widest text-[#064e3b] font-serif uppercase\'>RAABIHA</span>'">
            </a>
            
            <!-- Nav Menu -->
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => 'nav',
                    'container_class'=> 'flex gap-8',
                    'menu_class'     => 'flex gap-8 m-0 p-0 list-none',
                    'fallback_cb'    => false,
                    'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'walker'         => new Raabiha_Nav_Walker()
                ) );
            } else {
            ?>
            <nav class="flex gap-8">
                <a href="<?php echo esc_url( home_url() ); ?>" class="text-[11px] font-mono tracking-widest uppercase text-[#615e57] hover:text-[#1c1c1a] transition-colors <?php echo is_front_page() ? 'text-[#1c1c1a] border-b border-[#1c1c1a] pb-0.5' : ''; ?>">Beranda</a>
                <a href="<?php echo esc_url( home_url('/about') ); ?>" class="text-[11px] font-mono tracking-widest uppercase text-[#615e57] hover:text-[#1c1c1a] transition-colors <?php echo is_page('about') ? 'text-[#1c1c1a] border-b border-[#1c1c1a] pb-0.5' : ''; ?>">Tentang Kami</a>
                <a href="#" class="text-[11px] font-mono tracking-widest uppercase text-[#615e57] hover:text-[#1c1c1a] transition-colors">Lokasi & Kontak</a>
                <a href="#" class="text-[11px] font-mono tracking-widest uppercase text-[#615e57] hover:text-[#1c1c1a] transition-colors">Galeri</a>
                <a href="#" class="text-[11px] font-mono tracking-widest uppercase text-[#615e57] hover:text-[#1c1c1a] transition-colors">Blog</a>
                <a href="<?php echo function_exists('woocommerce_get_page_id') ? esc_url( get_permalink( woocommerce_get_page_id( 'shop' ) ) ) : '#'; ?>" class="text-[11px] font-mono tracking-widest uppercase text-[#615e57] hover:text-[#1c1c1a] transition-colors <?php echo (function_exists('is_shop') && is_shop()) ? 'text-[#1c1c1a] border-b border-[#1c1c1a] pb-0.5' : ''; ?>">Katalog</a>
            </nav>
            <?php } ?>
            
            <div class="flex items-center gap-5">
                <!-- Search -->
                <button class="search-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
                <!-- Cart -->
                <a href="<?php echo function_exists('wc_get_cart_url') ? esc_url(wc_get_cart_url()) : '#'; ?>" class="cart-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <?php if ( function_exists('WC') && WC()->cart ) : ?>
                        <span class="raabiha-cart-count-badge absolute -top-2 -right-2 bg-[#064e3b] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center <?php echo (WC()->cart->get_cart_contents_count() > 0) ? '' : 'hidden'; ?>">
                            <?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <!-- Account/Dashboard -->
                <div class="relative">
                    <button id="desktop-profile-toggle" class="text-[#1c1c1a] hover:text-[#064e3b] transition-colors flex items-center justify-center w-6 h-6 rounded-full overflow-hidden focus:outline-none">
                        <?php if ( is_user_logged_in() ) : 
                            $avatar_url = get_avatar_url( get_current_user_id(), array('size' => 64, 'default' => 'mp') );
                            // Facebook-style dummy silhouette
                            $dummy_base64 = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2QxZDVkYiI+PHBhdGggZD0iTTI0IDI0SDBDMCAxNy4zNzI1IDUuMzczMjUgMTIgMTIgMTJDMTguNjI2OCAxMiAyNCAxNy4zNzI1IDI0IDI0Wk0xMiAxMUM4Ljk2MjQzIDExIDYuNSA4LjUzNzU3IDYuNSA1LjVDNi41IDIuNDYyNDMgOC45NjI0MyAwIDEyIDBDMTUuMDM3NiAwIDE3LjUgMi40NjI0MyAxNy41IDUuNUMxNy41IDguNTM3NTcgMTUuMDM3NiAxMSAxMiAxMVoiLz48L3N2Zz4=';
                        ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="Profile" class="w-full h-full object-cover bg-gray-200" onerror="this.onerror=null;this.src='<?php echo $dummy_base64; ?>';">
                        <?php else : ?>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown -->
                    <div id="desktop-profile-dropdown" class="absolute right-0 mt-3 w-48 bg-[#fcf9f5] border border-[#e5e2de] shadow-lg rounded-sm py-2 opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-200 z-50">
                        <?php if ( is_user_logged_in() ) : ?>
                            <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : '#'; ?>" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">My Account</a>
                            
                            <?php 
                            $current_user = wp_get_current_user();
                            if ( ! in_array( 'customer', (array) $current_user->roles ) ) : ?>
                                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Dashboard</a>
                            <?php endif; ?>
                            
                            <a href="<?php echo esc_url(home_url('/wishlist/')); ?>" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Wishlist</a>
                            <div class="border-t border-[#e5e2de] my-1"></div>
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="block px-4 py-2 text-sm font-sans text-red-600 hover:bg-[#f0ede9] transition-colors">Logout</a>
                        <?php else : ?>
                            <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : wp_login_url(); ?>" class="block px-4 py-2 text-sm font-sans text-[#1c1c1a] hover:bg-[#f0ede9] hover:text-[#064e3b] transition-colors">Login / Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MOBILE LAYOUT ==================== -->
        <?php if ( $is_single_product ) : ?>
            <div class="md:hidden flex items-center justify-between px-4 py-4 relative z-50 bg-white/90 backdrop-blur-md shadow-sm border-b border-[#e5e2de]">
                <!-- Back Arrow -->
                <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url( wc_get_page_permalink( 'shop' ) ) : '#'; ?>" class="w-10 h-10 flex items-center justify-center rounded-full text-[#1c1c1a] hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                
                <!-- Center Title (Optional) -->
                <div class="flex-1 text-center font-serif text-sm font-bold tracking-widest uppercase truncate px-2 text-[#1c1c1a]">
                    <?php echo esc_html( get_the_title() ); ?>
                </div>

                <!-- Share Icon -->
                <button class="w-10 h-10 flex items-center justify-center rounded-full text-[#1c1c1a] hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                </button>
            </div>
        <?php else : ?>
            <div class="md:hidden flex items-center justify-between px-6 py-4 relative z-50 bg-[#fcf9f5]">
                <!-- Hamburger Menu (Left) -->
                <button id="mobile-menu-toggle" class="text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <!-- Mobile Logo (Center) -->
                <a href="<?php echo esc_url( home_url() ); ?>" class="block hover:opacity-80 transition-opacity absolute left-1/2 -translate-x-1/2">
                    <!-- Gunakan gambar statis logo-mobile.png -->
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-mobile.png" alt="Raabiha" class="h-8 w-auto object-contain" onerror="this.outerHTML='<span class=\'text-xl font-bold tracking-widest text-[#064e3b] font-serif uppercase\'>R</span>'">
                </a>

                <!-- Search & Cart (Right) -->
                <div class="flex items-center gap-4">
                    <!-- Search -->
                    <button class="search-toggle-btn text-[#1c1c1a] hover:text-[#064e3b] transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <!-- Cart Moved to Bottom Nav -->
                </div>
            </div>
        <?php endif; ?>

        <!-- Global Search Overlay (Slide down) -->
        <div id="search-overlay" class="absolute top-full left-0 right-0 bg-[#fcf9f5] border-b border-[#e5e2de] shadow-sm transform -translate-y-full opacity-0 pointer-events-none transition-all duration-300 z-40">
            <div class="max-w-[1400px] mx-auto px-6 md:px-12 py-4">
                <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-4">
                    <input type="hidden" name="post_type" value="product" />
                    <svg class="w-5 h-5 text-[#a3a3a3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="s" placeholder="Cari gamis, outerwear, hijab..." class="w-full bg-transparent border-none text-[13px] font-sans text-[#1c1c1a] placeholder-[#a3a3a3] outline-none focus:ring-0">
                    <button type="button" id="search-close" class="text-[#1c1c1a] hover:text-red-500 transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

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
                    <li><a href="<?php echo esc_url( home_url() ); ?>" class="block hover:text-[#064e3b] transition-colors <?php echo is_front_page() ? 'text-[#064e3b] font-bold' : ''; ?>">Beranda</a></li>
                    <li><a href="<?php echo esc_url( home_url('/about') ); ?>" class="block hover:text-[#064e3b] transition-colors <?php echo is_page('about') ? 'text-[#064e3b] font-bold' : ''; ?>">Tentang Kami</a></li>
                    <li><a href="<?php echo esc_url( home_url('/contact') ); ?>" class="block hover:text-[#064e3b] transition-colors <?php echo is_page('contact') ? 'text-[#064e3b] font-bold' : ''; ?>">Lokasi & Kontak</a></li>
                    <li><a href="<?php echo esc_url( home_url('/gallery') ); ?>" class="block hover:text-[#064e3b] transition-colors <?php echo is_page('gallery') ? 'text-[#064e3b] font-bold' : ''; ?>">Galeri</a></li>
                    <li><a href="<?php echo esc_url( home_url('/blog') ); ?>" class="block hover:text-[#064e3b] transition-colors <?php echo (is_page('blog') || is_page('blog-detail')) ? 'text-[#064e3b] font-bold' : ''; ?>">Blog</a></li>
                    <li><a href="<?php echo function_exists('woocommerce_get_page_id') ? esc_url( get_permalink( woocommerce_get_page_id( 'shop' ) ) ) : '#'; ?>" class="block hover:text-[#064e3b] transition-colors <?php echo (function_exists('is_shop') && is_shop()) ? 'text-[#064e3b] font-bold' : ''; ?>">Katalog</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Mini Cart Overlay removed -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
