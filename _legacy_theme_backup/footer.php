    <?php if ( ! is_page( 'blog' ) && ! is_page( 'blog-detail' ) ) : ?>
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
    <?php endif; ?>

    <!-- Custom Minimal Footer -->
    <footer class="bg-[#fcf9f5] md:bg-[#262626] text-[#1c1c1a] md:text-[#d4d4d4] pt-12 md:pt-20 pb-24 md:pb-8 px-6 md:px-16 mt-auto">
        <!-- Desktop Grid (Hidden on Mobile) -->
        <div class="hidden md:grid max-w-[1400px] mx-auto grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1.5fr] gap-8 md:gap-12 mb-16">
            <div>
                <div class="font-serif text-3xl font-semibold tracking-widest text-[#f5f5f5] mb-6">RAABIHA</div>
                <p class="font-inter text-[13px] leading-relaxed text-[#a3a3a3] max-w-[320px] mb-8">
                    <?php 
                    $about = get_option('raabiha_footer_about');
                    if (empty($about)) $about = "Architectural modesty for the modern intellectual.\nBridging high-fashion editorial with raw urban energy.";
                    echo nl2br(esc_html($about)); 
                    ?>
                </p>
                <div class="flex gap-6">
                    <?php $ig = get_option('raabiha_social_ig'); if(empty($ig)) $ig = '#'; ?>
                    <a href="<?php echo esc_url($ig); ?>" target="_blank" class="text-[#a3a3a3] hover:text-white transition-colors text-[11px] font-medium uppercase tracking-[0.1em]">Instagram</a>
                    
                    <?php $tiktok = get_option('raabiha_social_tiktok'); if(empty($tiktok)) $tiktok = '#'; ?>
                    <a href="<?php echo esc_url($tiktok); ?>" target="_blank" class="text-[#a3a3a3] hover:text-white transition-colors text-[11px] font-medium uppercase tracking-[0.1em]">TikTok</a>
                    
                    <?php $pinterest = get_option('raabiha_social_pinterest'); if(empty($pinterest)) $pinterest = '#'; ?>
                    <a href="<?php echo esc_url($pinterest); ?>" target="_blank" class="text-[#a3a3a3] hover:text-white transition-colors text-[11px] font-medium uppercase tracking-[0.1em]">Pinterest</a>
                </div>
            </div>
            <div>
                <h4 class="font-inter text-[10px] tracking-widest uppercase text-[#d4d4d4] mb-6">Shop</h4>
                <ul class="flex flex-col gap-4 font-inter text-[13px] text-[#a3a3a3]">
                    <li><a href="#" class="hover:text-white transition-colors">New Arrivals</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Gamis</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Outerwear</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Sets</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Hijabs</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-inter text-[10px] tracking-widest uppercase text-[#d4d4d4] mb-6">Brand</h4>
                <ul class="flex flex-col gap-4 font-inter text-[13px] text-[#a3a3a3]">
                    <li><a href="#" class="hover:text-white transition-colors">Our Story</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Journal</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Reseller</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Sustainability</a></li>
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
            <div class="font-serif text-3xl font-semibold tracking-widest text-black mb-6">RAABIHA</div>
            <div class="flex gap-6 mb-8 text-[9px] font-mono tracking-widest text-[#615e57] uppercase">
                <a href="#" class="hover:text-black">Privacy</a>
                <a href="#" class="hover:text-black">Terms</a>
                <a href="#" class="hover:text-black">Shipping</a>
            </div>
            <div class="text-[9px] font-mono tracking-widest text-[#615e57] uppercase">&copy; <?php echo date('Y'); ?> RAABIHA. MODESTY.</div>
        </div>

        <!-- Desktop Footer Bottom -->
        <div class="hidden md:flex max-w-[1400px] mx-auto flex-col md:flex-row justify-between pt-8 border-t border-[#404040] font-inter text-[9px] text-[#737373] uppercase tracking-widest">
            <div>&copy; <?php echo date('Y'); ?> RAABIHA. ARCHITECTURAL MODESTY.</div>
            <div class="flex gap-8 mt-4 md:mt-0">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

    <!-- Fixed Bottom Navigation (Mobile Only) -->
    <?php
    $is_home_active = is_front_page();
    $is_shop_active = function_exists('is_shop') && (is_shop() || is_product_category() || is_product());
    $is_wishlist_active = is_page('wishlist');
    $is_profile_active = function_exists('is_account_page') && is_account_page();
    $is_single_product = function_exists('is_product') && is_product();
    ?>
    <?php if ( ! $is_single_product ) : ?>
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-[#fcf9f5] border-t border-[#e5e2de] flex justify-between px-6 py-2 z-50">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex flex-col items-center <?php echo $is_home_active ? 'text-[#064e3b] bg-[#064e3b]/10 px-4 py-1.5 rounded-2xl' : 'text-[#615e57] px-4 py-1.5'; ?> transition-all duration-200">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?php echo $is_home_active ? '2' : '1.5'; ?>" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[9px] font-mono <?php echo $is_home_active ? 'font-bold' : ''; ?>">Home</span>
        </a>
        <a href="<?php echo function_exists('woocommerce_get_page_id') ? esc_url( get_permalink( woocommerce_get_page_id( 'shop' ) ) ) : home_url('/shop/'); ?>" class="flex flex-col items-center <?php echo $is_shop_active ? 'text-[#064e3b] bg-[#064e3b]/10 px-4 py-1.5 rounded-2xl' : 'text-[#615e57] px-4 py-1.5'; ?> transition-all duration-200">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?php echo $is_shop_active ? '2' : '1.5'; ?>" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="text-[9px] font-mono <?php echo $is_shop_active ? 'font-bold' : ''; ?>">Shop</span>
        </a>
        <?php $is_cart_active = function_exists('is_cart') && is_cart(); ?>
        <a href="<?php echo function_exists('wc_get_cart_url') ? esc_url(wc_get_cart_url()) : '#'; ?>" class="cart-toggle-btn flex flex-col items-center <?php echo $is_cart_active ? 'text-[#064e3b] bg-[#064e3b]/10 px-4 py-1.5 rounded-2xl' : 'text-[#615e57] px-4 py-1.5'; ?> transition-all duration-200 relative">
            <div class="relative">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?php echo $is_cart_active ? '2' : '1.5'; ?>" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <?php if ( function_exists('WC') && WC()->cart ) : ?>
                    <span class="raabiha-cart-count-badge absolute -top-1 -right-2 bg-[#064e3b] text-white text-[9px] font-bold w-3 h-3 rounded-full flex items-center justify-center <?php echo (WC()->cart->get_cart_contents_count() > 0) ? '' : 'hidden'; ?>">
                        <?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?>
                    </span>
                <?php endif; ?>
            </div>
            <span class="text-[9px] font-mono <?php echo $is_cart_active ? 'font-bold' : ''; ?>">Cart</span>
        </a>
        <a href="<?php echo esc_url( home_url('/wishlist/') ); ?>" class="flex flex-col items-center <?php echo $is_wishlist_active ? 'text-[#064e3b] bg-[#064e3b]/10 px-4 py-1.5 rounded-2xl' : 'text-[#615e57] px-4 py-1.5'; ?> transition-all duration-200">
            <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?php echo $is_wishlist_active ? '2' : '1.5'; ?>" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            <span class="text-[9px] font-mono <?php echo $is_wishlist_active ? 'font-bold' : ''; ?>">Wishlist</span>
        </a>
        <!-- Mobile Profile Button -->
        <button id="mobile-profile-toggle" type="button" class="flex flex-col items-center <?php echo $is_profile_active ? 'text-[#064e3b] bg-[#064e3b]/10 px-4 py-1.5 rounded-2xl' : 'text-[#615e57] px-4 py-1.5'; ?> focus:outline-none transition-all duration-200">
            <div class="w-5 h-5 mb-1 flex items-center justify-center rounded-full overflow-hidden">
                <?php if ( is_user_logged_in() ) : 
                    $avatar_url = get_avatar_url( get_current_user_id(), array('size' => 64, 'default' => 'mp') );
                    $dummy_base64 = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2QxZDVkYiI+PHBhdGggZD0iTTI0IDI0SDBDMCAxNy4zNzI1IDUuMzczMjUgMTIgMTIgMTJDMTguNjI2OCAxMiAyNCAxNy4zNzI1IDI0IDI0Wk0xMiAxMUM4Ljk2MjQzIDExIDYuNSA4LjUzNzU3IDYuNSA1LjVDNi41IDIuNDYyNDMgOC45NjI0MyAwIDEyIDBDMTUuMDM3NiAwIDE3LjUgMi40NjI0MyAxNy41IDUuNUMxNy41IDguNTM3NTcgMTUuMDM3NiAxMSAxMiAxMVoiLz48L3N2Zz4=';
                ?>
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="Profile" class="w-full h-full object-cover bg-gray-200" onerror="this.onerror=null;this.src='<?php echo $dummy_base64; ?>';">
                <?php else : ?>
                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?php echo $is_profile_active ? '2' : '1.5'; ?>" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <?php endif; ?>
            </div>
            <span class="text-[9px] font-mono <?php echo $is_profile_active ? 'font-bold' : ''; ?>">Profile</span>
        </button>
    </div>
    <?php endif; ?>

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
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : '#'; ?>" class="flex items-center gap-4 p-4 rounded-xl hover:bg-[#f0ede9] text-[#1c1c1a] transition-colors">
                            <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="font-sans text-sm font-medium">My Account</span>
                        </a>
                        
                        <?php 
                        $current_user = wp_get_current_user();
                        if ( ! in_array( 'customer', (array) $current_user->roles ) ) : ?>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="flex items-center gap-4 p-4 rounded-xl hover:bg-[#f0ede9] text-[#1c1c1a] transition-colors">
                                <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span class="font-sans text-sm font-medium">Dashboard</span>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url(home_url('/wishlist/')); ?>" class="flex items-center gap-4 p-4 rounded-xl hover:bg-[#f0ede9] text-[#1c1c1a] transition-colors">
                            <svg class="w-5 h-5 text-[#615e57]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span class="font-sans text-sm font-medium">Wishlist</span>
                        </a>
                        
                        <div class="h-px bg-[#e5e2de] my-2"></div>
                        
                        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="flex items-center gap-4 p-4 rounded-xl hover:bg-[#fef2f2] text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="font-sans text-sm font-medium">Logout</span>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : wp_login_url(); ?>" class="flex items-center gap-4 p-4 rounded-xl bg-[#064e3b] text-white hover:bg-opacity-90 transition-colors justify-center mt-2">
                            <span class="font-sans text-sm font-medium tracking-wide">Login / Register</span>
                        </a>
                    <?php endif; ?>
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

    <?php wp_footer(); ?>
</body>
</html>
