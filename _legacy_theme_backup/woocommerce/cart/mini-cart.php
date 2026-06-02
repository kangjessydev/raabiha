<?php
/**
 * Custom Mini-cart for Raabiha Theme
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget flex flex-col gap-6 m-0 p-0 list-none <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<li class="woocommerce-mini-cart-item flex items-start gap-4 pb-6 border-b border-[#e5e2de] relative <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
					
                    <!-- Product Image -->
                    <div class="w-16 h-20 shrink-0 bg-[#f2efe8] rounded overflow-hidden">
                        <?php if ( empty( $product_permalink ) ) : ?>
                            <?php echo str_replace('class="', 'class="w-full h-full object-cover ', $thumbnail); ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $product_permalink ); ?>" class="block w-full h-full">
                                <?php echo str_replace('class="', 'class="w-full h-full object-cover ', $thumbnail); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Product Details -->
                    <div class="flex-1 flex flex-col pt-1 pr-6">
                        <!-- Title -->
                        <div class="text-[12px] font-bold text-[#1c1c1a] leading-tight mb-1">
                            <?php if ( empty( $product_permalink ) ) : ?>
                                <?php echo wp_kses_post( $product_name ); ?>
                            <?php else : ?>
                                <a href="<?php echo esc_url( $product_permalink ); ?>" class="text-[#1c1c1a] hover:text-[#064e3b] transition-colors">
                                    <?php echo wp_kses_post( $product_name ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Variations -->
                        <div class="text-[11px] text-[#615e57] mb-2 raabiha-mini-cart-variation">
                            <?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                        
                        <!-- Price & Quantity -->
                        <div class="text-[11px] font-mono font-bold text-[#1c1c1a]">
                            <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ), $cart_item, $cart_item_key ); ?>
                        </div>
                    </div>

                    <!-- Remove Button -->
                    <div class="absolute top-1 right-0">
                        <?php
                        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            'woocommerce_cart_item_remove_link',
                            sprintf(
                                '<a role="button" href="%s" class="remove remove_from_cart_button text-[#9ca3af] hover:text-red-500 hover:bg-red-50 w-6 h-6 rounded-full flex items-center justify-center transition-colors text-lg font-light leading-none" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">&times;</a>',
                                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                                esc_attr( $product_id ),
                                esc_attr( $cart_item_key ),
                                esc_attr( $_product->get_sku() ),
                                esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) )
                            ),
                            $cart_item_key
                        );
                        ?>
                    </div>

				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<div class="woocommerce-mini-cart__total total mt-6 mb-4 flex justify-between items-center">
		<?php
		do_action( 'woocommerce_widget_shopping_cart_total' );
		?>
	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<div class="woocommerce-mini-cart__buttons buttons flex flex-col gap-3">
        <?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?>
    </div>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message text-center text-[#615e57] font-mono text-[11px] uppercase tracking-widest mt-10">Keranjang Anda Kosong.</p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
