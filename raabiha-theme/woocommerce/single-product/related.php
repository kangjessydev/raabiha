<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( $related_products ) : ?>

    <section class="related products mt-32">
        <div class="flex justify-between items-baseline border-b border-[#e5e2de] pb-4 mb-8">
            <h2 class="text-[#1c1c1a] text-2xl lg:text-3xl font-serif font-bold tracking-tight uppercase">
                <?php echo esc_html( apply_filters( 'woocommerce_product_related_products_heading', __( 'PRODUK SERUPA', 'woocommerce' ) ) ); ?>
            </h2>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="text-[#615e57] hover:text-[#1c1c1a] text-[9px] font-mono uppercase tracking-widest transition-colors">
                VIEW ALL COLLECTION
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 lg:gap-x-6 gap-y-12">
            <?php foreach ( $related_products as $related_product ) : ?>

                <?php
                $post_object = get_post( $related_product->get_id() );
                setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

                wc_get_template_part( 'content', 'product' );
                ?>

            <?php endforeach; ?>
        </div>

    </section>
    <?php
endif;

wp_reset_postdata();
