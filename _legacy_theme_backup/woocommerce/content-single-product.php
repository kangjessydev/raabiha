<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-24 mb-32', $product ); ?>>

    <!-- Left Column (Product Gallery) -->
    <div class="lg:col-span-7 relative">
        <!-- Badges Container (Flexbox to prevent overlap) -->
        <div class="absolute top-6 left-6 z-40 flex flex-col gap-2 items-start pointer-events-none">
            <div class="bg-[#1c1c1a] text-white text-[9px] font-mono font-bold tracking-widest uppercase px-3 py-1 shadow-sm">
                NEW ARRIVAL
            </div>
            <?php if ( $product->is_on_sale() ) : ?>
            <div class="bg-[#09493B] text-white text-[9px] font-mono font-bold tracking-widest uppercase px-3 py-1 shadow-sm">
                SALE!
            </div>
            <?php endif; ?>
        </div>
        <!-- Mobile Wishlist Button (Over Image) -->
        <button class="lg:hidden absolute top-4 right-4 z-40 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-[#1c1c1a] shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </button>
        <div class="sticky top-32">
            <?php
            // Remove WooCommerce default absolute sale flash to prevent duplication/overlap
            remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
            
            /**
             * Hook: woocommerce_before_single_product_summary.
             *
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
            ?>
        </div>
    </div>

    <!-- Right Column (Product Info) -->
    <div class="lg:col-span-5">
        <div class="summary entry-summary">
            
            <!-- Breadcrumb -->
            <div class="mb-8 hidden lg:block">
                <?php 
                woocommerce_breadcrumb( array(
                    'wrap_before' => '<nav class="text-[#615e57] text-[9px] font-mono uppercase tracking-widest inline-block">',
                    'wrap_after'  => '</nav>',
                    'delimiter'   => ' &nbsp;/&nbsp; ',
                ) ); 
                ?>
            </div>

            <!-- Title -->
            <div class="lg:hidden text-[#615e57] text-[9px] font-mono uppercase tracking-widest mb-2 mt-4">NEW COLLECTION</div>
            <h1 class="text-[#1c1c1a] text-3xl lg:text-5xl font-serif font-bold tracking-tight mb-2 mt-2 lg:mt-0">
                <?php echo esc_html( ucwords( strtolower( get_the_title() ) ) ); ?>
            </h1>
            
            <!-- Price -->
            <div id="main-product-price" class="text-[#615e57] text-2xl md:text-3xl font-serif mb-10">
                <?php echo $product->get_price_html(); ?>
            </div>

            <?php
            // We want to hide default meta (categories, tags, sku) if they are hooked here.
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
            // Remove sharing if present
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
            
            /**
             * Hook: woocommerce_single_product_summary.
             *
             * @hooked woocommerce_template_single_title - 5 (Removed manually above, we rendered it)
             * @hooked woocommerce_template_single_rating - 10 (Optional)
             * @hooked woocommerce_template_single_price - 10 (Removed manually above)
             * @hooked woocommerce_template_single_excerpt - 20 (We will move this to Accordion)
             * @hooked woocommerce_template_single_add_to_cart - 30
             * @hooked woocommerce_template_single_meta - 40
             * @hooked woocommerce_template_single_sharing - 50
             * @hooked WC_Structured_Data::generate_product_data() - 60
             */
            
            // Unhook things we already rendered or moved
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
            
            do_action( 'woocommerce_single_product_summary' );
            ?>
            
            <!-- Wishlist & Trust Badges (Custom) -->
            <button class="hidden lg:flex w-full mt-2 border border-[#1c1c1a] h-14 text-[#1c1c1a] text-[10px] font-mono font-bold tracking-[0.2em] uppercase hover:bg-[#f2efe8] transition-colors justify-center items-center gap-2">
                WHISHLIST PRODUCT
            </button>

            <div class="flex flex-col md:flex-row gap-6 mt-8 pt-8 border-t border-[#e5e2de] text-[9px] text-[#1c1c1a] font-mono tracking-widest uppercase">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    FREE SHIPPING ON ORDERS OVER $50
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
    <!-- Accordions -->
    <div class="border-y border-[#e5e2de] divide-y divide-[#e5e2de]">
        
        <!-- 01. Product Description -->
        <div class="group py-6 product-accordion">
            <button class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                DESKRIPSI
                <svg class="w-4 h-4 transition-transform duration-300 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="mt-6 text-[#1c1c1a] text-[14px] leading-relaxed font-sans accordion-content">
                <?php 
                if ( has_excerpt() ) {
                    the_excerpt();
                } else {
                    the_content();
                }
                ?>
            </div>
        </div>
        
        <!-- 02. Reviews -->
        <div class="group py-6 product-accordion">
            <button class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                ULASAN (<?php echo $product->get_review_count(); ?>)
                <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="mt-6 text-[#1c1c1a] text-[14px] leading-relaxed font-sans hidden accordion-content">
                <?php comments_template(); ?>
            </div>
        </div>

        <!-- 03. Community Journal (Custom Field) -->
        <?php $journal = get_post_meta( get_the_ID(), '_raabiha_community_journal', true ); ?>
        <?php if ( $journal ) : ?>
        <div class="group py-6 product-accordion">
            <button class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                COMMUNITY JOURNAL
                <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="mt-6 text-[#1c1c1a] text-[14px] leading-relaxed font-sans hidden accordion-content">
                <?php echo wpautop( esc_html( $journal ) ); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 04. Shipping & Care (Custom Field) -->
        <?php 
        $shipping_care = get_post_meta( get_the_ID(), '_raabiha_shipping_care', true ); 
        if ( empty($shipping_care) ) {
            $shipping_care = "Standard shipping takes 3-5 business days.\nDry clean only. Do not tumble dry.";
        }
        ?>
        <div class="group py-6 product-accordion">
            <button class="w-full flex justify-between items-center text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase focus:outline-none">
                PENGIRIMAN & PERAWATAN
                <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="mt-6 text-[#1c1c1a] text-[14px] leading-relaxed font-sans hidden accordion-content">
                <?php echo wpautop( esc_html( $shipping_care ) ); ?>
            </div>
        </div>

    </div>
</div>

<?php
/**
 * Hook: woocommerce_after_single_product_summary.
 *
 * @hooked woocommerce_output_product_data_tabs - 10 (Removed)
 * @hooked woocommerce_upsell_display - 15
 * @hooked woocommerce_output_related_products - 20
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
do_action( 'woocommerce_after_single_product_summary' );
?>

<?php do_action( 'woocommerce_after_single_product' ); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
