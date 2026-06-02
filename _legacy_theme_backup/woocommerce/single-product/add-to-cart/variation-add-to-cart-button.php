<?php
/**
 * Single variation cart button
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.5.2
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button w-full">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

    <div class="flex items-center w-full">
        <div class="hidden lg:block mr-4">
            <?php
            do_action( 'woocommerce_before_add_to_cart_quantity' );

            woocommerce_quantity_input(
                array(
                    'min_value'   => $product->get_min_purchase_quantity(),
                    'max_value'   => $product->get_max_purchase_quantity(),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                )
            );

            do_action( 'woocommerce_after_add_to_cart_quantity' );
            ?>
        </div>

        <!-- DESKTOP Add to Cart Button -->
        <button type="submit" name="add_to_cart_action" value="cart" class="hidden md:flex single_add_to_cart_button flex-1 h-14 bg-[#09493B] text-white hover:bg-[#07362c] flex-col items-center justify-center border-none transition-colors focus:outline-none">
            <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">TAMBAHKAN KE KERANJANG</span>
        </button>
    </div>

    <!-- MOBILE Sticky Action Bar -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white flex z-[90] border-t border-[#e5e2de] w-full h-[60px] pb-safe">
        <!-- WA Button -->
        <?php 
        $wa_number = get_option('raabiha_social_wa', '6281234567890');
        $wa_link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $wa_number) . '?text=' . rawurlencode('Halo, saya tertarik dengan produk ' . get_the_title());
        ?>
        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="w-[60px] shrink-0 h-full flex flex-col items-center justify-center border-r border-[#e5e2de] text-[#1c1c1a] hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="text-[8px] font-mono tracking-widest uppercase">Chat</span>
        </a>
        
        <!-- Cart Icon -->
        <button type="submit" name="add_to_cart_action" value="cart" class="single_add_to_cart_button w-[60px] shrink-0 h-full flex flex-col items-center justify-center border-r border-[#e5e2de] text-[#1c1c1a] hover:bg-gray-50 transition-colors focus:outline-none">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span class="text-[8px] font-mono tracking-widest uppercase">Cart</span>
        </button>
        
        <!-- Buy Now Button -->
        <button type="submit" name="add_to_cart_action" value="buy_now" class="single_buy_now_button flex-1 h-full bg-[#09493B] text-white flex flex-col items-center justify-center hover:bg-[#07362c] transition-colors focus:outline-none">
            <span class="text-[10px] font-mono font-bold tracking-[0.2em] uppercase">BELI SEKARANG</span>
        </button>
    </div>
    
    <input type="hidden" name="raabiha_action_type" id="raabiha_action_type" value="cart">
    </div>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
