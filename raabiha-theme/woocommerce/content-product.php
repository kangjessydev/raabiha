<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
if ( ! $image_url ) {
    $image_url = wc_placeholder_img_src();
}

// Format price (strip HTML tags if simple, or keep as is)
$price_html = $product->get_price_html();
$price_text = strip_tags( $price_html );
if( $product->is_type('simple') ) {
    $price_text = strip_tags( wc_price( $product->get_price() ) );
}

$categories = wc_get_product_category_list( get_the_ID(), ', ' );
$is_featured = $product->is_featured();
$is_on_sale = $product->is_on_sale();

$badge = '';
if ( $is_on_sale ) {
    $badge = '<span class="absolute top-4 left-4 z-10 bg-[#1c1c1a] text-white px-2 py-1 text-[8px] font-mono font-bold uppercase tracking-widest">SALE</span>';
} elseif ( $is_featured ) {
    $badge = '<span class="absolute top-4 left-4 z-10 bg-[#d9a596] text-[#1c1c1a] px-2 py-1 text-[8px] font-mono font-bold uppercase tracking-widest">BESTSELLER</span>';
} else {
    $badge = '<span class="absolute top-4 left-4 z-10 bg-[#e0d6cd] text-[#1c1c1a] px-2 py-1 text-[8px] font-mono font-bold uppercase tracking-widest">NEW</span>';
}
?>

<div class="group cursor-pointer">
    <a href="<?php the_permalink(); ?>" class="block">
        <div class="relative w-full aspect-[4/5] bg-[#ebebeb] mb-4 overflow-hidden">
            <?php echo $badge; ?>
            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 <?php echo ( get_the_ID() % 2 == 0 ) ? 'grayscale' : ''; ?>" style="height: 100% !important;">
        </div>
        <p class="text-[#615e57] text-[8px] font-mono uppercase tracking-widest mb-1 truncate">
            <?php echo !empty($categories) ? strip_tags($categories) : 'ESSENTIALS'; ?>
        </p>
        <h3 class="text-[#1c1c1a] text-[15px] font-serif font-bold tracking-tight mb-1">
            <?php echo esc_html( ucwords( strtolower( get_the_title() ) ) ); ?>
        </h3>
        <p class="text-[#615e57] text-[11px] tracking-wide">
            <?php echo esc_html( $price_text ); ?>
        </p>
    </a>
</div>
