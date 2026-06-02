<?php
/**
 * Raabiha Theme Shortcodes
 * 
 * Defines UI components that can be used inside Gutenberg blocks.
 */

// 1. Hero Section Shortcode
// [raabiha_hero title="Architectural Modesty" drop="DROP 02 // 2024" btn_text="Explore The Drop" image_url="..."]
add_shortcode('raabiha_hero', function($atts) {
    $atts = shortcode_atts([
        'title' => 'Architectural<br>Modesty',
        'drop' => 'DROP 02 // 2024',
        'btn_text' => 'Explore The Drop',
        'image_url' => get_template_directory_uri() . '/assets/images/hero_model_1779445106838.png',
        'desc' => 'A dialogue between structural precision and urban fluidity. Redefining the modest silhouette for the next generation.'
    ], $atts);

    ob_start();
    ?>
    <section class="relative w-full h-[85vh] md:h-[calc(100vh-80px)] overflow-hidden flex items-end md:items-stretch md:grid md:grid-cols-2">
        <!-- Desktop Text Column -->
        <div class="hidden md:flex flex-col justify-center px-8 py-16 lg:pl-24 lg:pr-16 text-center md:text-left bg-[#fcf9f5]">
            <div class="inline-block bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.15em] mb-6 uppercase w-fit"><?php echo esc_html($atts['drop']); ?></div>
            <h1 class="text-5xl lg:text-[4.5rem] leading-[1.05] tracking-[-0.02em] uppercase font-serif mb-6"><?php echo wp_kses_post($atts['title']); ?></h1>
            <p class="text-[15px] leading-relaxed text-[#525252] max-w-[400px] mb-12 mx-auto lg:mx-0"><?php echo esc_html($atts['desc']); ?></p>
            <a href="#" class="inline-block bg-[#064e3b] hover:bg-[#022c22] text-white text-[11px] font-semibold tracking-[0.15em] uppercase py-4 px-10 transition-colors w-fit mx-auto lg:mx-0">
                <?php echo esc_html($atts['btn_text']); ?>
            </a>
        </div>
        
        <!-- Background Image -->
        <div class="absolute inset-0 md:relative md:inset-auto md:h-full md:w-full z-0">
            <img src="<?php echo esc_url($atts['image_url']); ?>" alt="Hero" class="w-full h-full object-cover">
            <!-- Mobile Dark Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent md:hidden"></div>
        </div>

        <!-- Mobile Text Overlay -->
        <div class="relative z-10 w-full px-6 pb-12 flex flex-col items-center text-center md:hidden">
            <div class="text-[9px] font-mono tracking-[0.2em] text-white mb-4 uppercase">SEASONAL <?php echo esc_html($atts['drop']); ?></div>
            <h1 class="text-4xl leading-[1.15] font-serif italic text-white mb-8">The Urban Sanctuary<br>Collection</h1>
            <a href="#" class="inline-block bg-[#064e3b] text-white text-[10px] font-mono tracking-[0.1em] uppercase py-4 px-8 w-full max-w-[280px]">
                <?php echo esc_html($atts['btn_text']); ?>
            </a>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

// 2. Marquee Shortcode
// [raabiha_marquee texts="New Collection, Modest Fashion, Free Shipping, Reseller Open"]
add_shortcode('raabiha_marquee', function($atts) {
    $atts = shortcode_atts([
        'texts' => 'New Collection, Modest Fashion, Free Shipping, Reseller Open'
    ], $atts);

    $items = explode(',', $atts['texts']);

    ob_start();
    ?>
    <style>
        @keyframes marqueeScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marqueeScroll 20s linear infinite;
        }
    </style>
    <div class="relative z-10 bg-[#262626] text-[#d4d4d4] py-3 overflow-hidden flex whitespace-nowrap">
        <div class="flex gap-16 animate-marquee">
            <?php for($i=0; $i<4; $i++): // repeat to fill screen ?>
                <?php foreach($items as $item): ?>
                    <div class="flex items-center gap-16 text-[11px] font-medium tracking-[0.15em] uppercase">
                        <span><?php echo esc_html(trim($item)); ?></span>
                        <div class="w-[3px] h-[3px] bg-[#a3a3a3] rounded-full"></div>
                    </div>
                <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

// 3. WooCommerce Products Grid Shortcode
// [raabiha_products title="New Arrivals" limit="4" category=""]
add_shortcode('raabiha_products', function($atts) {
    $atts = shortcode_atts([
        'title' => 'New Arrivals',
        'limit' => 4,
        'category' => ''
    ], $atts);

    ob_start();
    ?>
    <section class="max-w-[1400px] mx-auto px-6 lg:px-16 py-24">
        <div class="flex justify-between items-end mb-12 md:justify-center md:mb-16">
            <div class="md:hidden">
                <h2 class="text-3xl font-serif italic m-0">New Arrivals</h2>
                <div class="text-[9px] font-mono tracking-widest text-[#615e57] uppercase mt-2">Latest Modest Innovations</div>
            </div>
            <h2 class="hidden md:block text-3xl lg:text-[2rem] tracking-[0.05em] font-serif"><?php echo esc_html($atts['title']); ?></h2>
            <a href="#" class="md:hidden text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a] border-b border-[#c4c7c7] pb-0.5">View All</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            if ( class_exists( 'WooCommerce' ) ) {
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => intval($atts['limit']),
                    'status' => 'publish',
                );
                if (!empty($atts['category'])) {
                    $args['product_cat'] = sanitize_text_field($atts['category']);
                }
                
                $loop = new WP_Query( $args );
                if ( $loop->have_posts() ) {
                    while ( $loop->have_posts() ) : $loop->the_post();
                        global $product;
                        ?>
                        <a href="<?php the_permalink(); ?>" class="group block">
                            <div class="aspect-[4/5] bg-[#e5e5e5] mb-4 overflow-hidden relative">
                                <?php if ( $product->is_on_sale() ) : ?>
                                    <div class="absolute top-3 right-3 bg-[#1a1a1a] text-white text-[9px] px-2 py-1 uppercase tracking-widest z-10">Sale</div>
                                <?php endif; ?>
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'large', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500' ) ); ?>
                                <?php else : ?>
                                    <!-- Fallback Image if no product image -->
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/prod_outer_1779445195829.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <?php endif; ?>
                                
                                <!-- Mobile Floating Cart Icon -->
                                <button class="md:hidden absolute bottom-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md z-10 text-black">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                </button>
                            </div>
                            <h3 class="text-[11px] font-semibold tracking-[0.1em] uppercase mb-1"><?php the_title(); ?></h3>
                            <div class="text-[13px] text-[#525252]"><?php echo $product->get_price_html(); ?></div>
                        </a>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                } else {
                    echo '<p class="col-span-full text-center text-sm text-gray-500">Tidak ada produk ditemukan.</p>';
                }
            } else {
                echo '<p class="col-span-full text-center text-sm text-gray-500">WooCommerce belum diaktifkan.</p>';
            }
            ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

// 4. Lookbook Section Shortcode
// [raabiha_lookbook title="Urban Sanctuary" id="04" desc="..." image_url="..."]
add_shortcode('raabiha_lookbook', function($atts) {
    $atts = shortcode_atts([
        'title' => 'Urban<br>Sanctuary',
        'id' => '04',
        'desc' => 'Discover the pieces that define the modern modest woman. Our SS24 collection blends utility with tradition, creating a sanctuary of style within the urban grid.',
        'image_url' => get_template_directory_uri() . '/assets/images/lookbook_hero_1779445259756.png'
    ], $atts);

    ob_start();
    ?>
    <section class="bg-[#f0f0ed] px-6 lg:px-16 py-24">
        <div class="max-w-[1400px] mx-auto md:grid md:grid-cols-[1.2fr_1fr] gap-12 lg:gap-24 items-center">
            <!-- Image (Mobile overlay vs Desktop side-by-side) -->
            <div class="relative w-full h-[65vh] md:h-auto">
                <img src="<?php echo esc_url($atts['image_url']); ?>" alt="Lookbook" class="w-full h-full md:aspect-[4/3] object-cover">
                <!-- Mobile gradient overlay -->
                <div class="absolute inset-0 bg-black/60 md:hidden"></div>
                
                <!-- Mobile Text Overlay -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-8 md:hidden z-10 text-white">
                    <h2 class="text-4xl font-serif mb-4">Urban<br>Sanctuary</h2>
                    <p class="text-[13px] font-sans text-gray-200 mb-8 max-w-[250px]">Exploring the intersection of modern city life and spiritual quietude.</p>
                    <a href="#" class="border border-white text-white px-8 py-3 text-[9px] font-mono tracking-widest uppercase">Read Journal</a>
                </div>
            </div>
            
            <!-- Desktop Text Column -->
            <div class="hidden md:block">
                <div class="text-[10px] text-[#d97777] tracking-[0.15em] mb-4">LOOKBOOK // <?php echo esc_html($atts['id']); ?></div>
                <h2 class="text-5xl leading-[1.1] font-serif mb-6"><?php echo wp_kses_post($atts['title']); ?></h2>
                <p class="text-[14px] leading-relaxed text-[#525252] mb-12"><?php echo esc_html($atts['desc']); ?></p>
                
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-4 border-b border-[#e5e5e5]">
                        <div class="flex items-center gap-4">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_hijab_1779445168360.png" class="w-12 h-12 object-cover">
                            <div>
                                <div class="text-[10px] font-semibold tracking-[0.1em] uppercase mb-1">Silk Twill Hijab</div>
                                <div class="text-[12px] text-[#525252]">Rp 349.000</div>
                            </div>
                        </div>
                        <svg class="w-4 h-4 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div class="flex items-center justify-between pb-4 border-b border-[#e5e5e5]">
                        <div class="flex items-center gap-4">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_outer_1779445136836.png" class="w-12 h-12 object-cover">
                            <div>
                                <div class="text-[10px] font-semibold tracking-[0.1em] uppercase mb-1">City Structure Coat</div>
                                <div class="text-[12px] text-[#525252]">Rp 1.150.000</div>
                            </div>
                        </div>
                        <svg class="w-4 h-4 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
});

// 5. Categories Grid Shortcode
// [raabiha_categories title="Categories"]
add_shortcode('raabiha_categories', function($atts) {
    $atts = shortcode_atts([
        'title' => 'Categories'
    ], $atts);

    ob_start();
    ?>
    <section class="max-w-[1400px] mx-auto px-6 lg:px-16 py-24 pb-0">
        <div class="flex justify-between items-end mb-12">
            <h2 class="text-3xl lg:text-[2rem] tracking-[0.05em] font-serif m-0"><?php echo esc_html($atts['title']); ?></h2>
            <a href="#" class="text-[10px] font-semibold tracking-[0.1em] uppercase text-[#525252] border-b border-[#525252] pb-0.5 hover:text-[#1a1a1a]">View All</a>
        </div>
        <!-- Mobile Section Title -->
        <div class="md:hidden text-[9px] font-mono tracking-widest text-[#615e57] uppercase mb-6 px-2">Browse Categories</div>
        
        <!-- Desktop Grid (Hidden on Mobile) -->
        <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="#" class="relative aspect-[3/4] bg-[#e5e5e5] overflow-hidden group">
                <div class="absolute top-4 left-4 bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.1em] uppercase z-10">Gamis</div>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_gamis_1779445122345.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>
            <a href="#" class="relative aspect-[3/4] bg-[#e5e5e5] overflow-hidden group">
                <div class="absolute top-4 left-4 bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.1em] uppercase z-10">Outer</div>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_outer_1779445136836.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>
            <a href="#" class="relative aspect-[3/4] bg-[#e5e5e5] overflow-hidden group">
                <div class="absolute top-4 left-4 bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.1em] uppercase z-10">Set</div>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_set_1779445153624.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>
            <a href="#" class="relative aspect-[3/4] bg-[#e5e5e5] overflow-hidden group">
                <div class="absolute top-4 left-4 bg-[#fce7e7] text-[#a14040] px-3 py-1 text-[10px] font-semibold tracking-[0.1em] uppercase z-10">Hijab</div>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_hijab_1779445168360.png" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>
        </div>

        <!-- Mobile Circular Grid -->
        <div class="md:hidden grid grid-cols-4 gap-4 px-2">
            <a href="#" class="flex flex-col items-center group">
                <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border border-[#e5e2de] shadow-sm">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_gamis_1779445122345.png" class="w-full h-full object-cover">
                </div>
                <span class="text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a]">Gamis</span>
            </a>
            <a href="#" class="flex flex-col items-center group">
                <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border border-[#e5e2de] shadow-sm">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_outer_1779445136836.png" class="w-full h-full object-cover">
                </div>
                <span class="text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a]">Outer</span>
            </a>
            <a href="#" class="flex flex-col items-center group">
                <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border border-[#e5e2de] shadow-sm">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_set_1779445153624.png" class="w-full h-full object-cover">
                </div>
                <span class="text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a]">Set</span>
            </a>
            <a href="#" class="flex flex-col items-center group">
                <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border border-[#e5e2de] shadow-sm">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/cat_hijab_1779445168360.png" class="w-full h-full object-cover">
                </div>
                <span class="text-[9px] font-mono tracking-[0.1em] uppercase text-[#1c1c1a]">Hijab</span>
            </a>
        </div>
    </section>
    <?php
    return ob_get_clean();
});
