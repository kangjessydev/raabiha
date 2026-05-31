<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
    return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

    <?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

    <form class="cart flex flex-wrap items-center mt-8 mb-24 md:!mb-0 w-full" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
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

        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
    </form>

    <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
        // Track which button was clicked
        let actionType = 'cart';
        
        jQuery(document).on('click', '.single_add_to_cart_button', function() {
            actionType = 'cart';
            jQuery('#raabiha_action_type').val('cart');
        });
        
        jQuery(document).on('click', '.single_buy_now_button', function() {
            actionType = 'buy_now';
            jQuery('#raabiha_action_type').val('buy_now');
        });

        // --- Custom AJAX Add to Cart ---
        jQuery(document).on('submit', 'form.cart', function(e) {
            e.preventDefault();
            
            const $form = jQuery(this);
            // Get the button that was clicked based on actionType
            const $buttons = actionType === 'buy_now' ? $form.find('.single_buy_now_button:visible') : $form.find('.single_add_to_cart_button:visible');
            const $button = $buttons.length > 1 ? $buttons.first() : $buttons;
            
            if ($button.is('.disabled')) return false;
            
            // UX: Button Animation
            const originalText = $button.html(); // using html to preserve price clone if any
            const spinnerSvg = '<svg class="animate-spin h-5 w-5 mx-auto text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            $button.html(spinnerSvg).css({
                'opacity': '0.8',
                'pointer-events': 'none'
            });
            
            // Prepare Data
            const formData = new FormData($form[0]);
            
            // Fix double-add issue by removing default 'add-to-cart' trigger
            // Instead we append the product ID directly as product_id
            const productId = formData.get('add-to-cart') || $form.find('button[name="add-to-cart"]').val() || <?php echo absint($product->get_id()); ?>;
            formData.set('product_id', productId);
            formData.delete('add-to-cart');
            
            const data = new URLSearchParams(formData).toString();
            
            // Endpoint fallback
            let ajaxUrl = '/?wc-ajax=add_to_cart';
            if (typeof wc_add_to_cart_params !== 'undefined') {
                ajaxUrl = wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' );
            }
            
            jQuery.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: data,
                success: function(response) {
                    if (response.error && response.product_url) {
                        window.location = response.product_url;
                        return;
                    }
                    
                    if (actionType === 'buy_now') {
                        // Redirect to checkout immediately
                        window.location = '<?php echo esc_url(wc_get_checkout_url()); ?>';
                        return;
                    }
                    
                    // Trigger WooCommerce cart update fragments (Mini Cart etc)
                    jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                    
                    // Clear notices
                    setTimeout(() => {
                        jQuery('.woocommerce-notices-wrapper').empty();
                        jQuery('.woocommerce-message').remove();
                        jQuery('.woocommerce-error').remove();
                    }, 10);
                    
                    // Reset quantity
                    const qtyInput = $form.find('input.qty');
                    if (qtyInput.length > 0) {
                        qtyInput.val(qtyInput.attr('min') || 1);
                    }
                    
                    // --- Flying to Cart Animation ---
                    let cartIcon = null;
                    document.querySelectorAll('.cart-toggle-btn').forEach(icon => {
                        if (icon.offsetParent !== null) {
                            cartIcon = icon;
                        }
                    });
                    
                    const productImg = document.querySelector('.woocommerce-product-gallery__image img');
                    
                    if (cartIcon && productImg) {
                        const imgRect = productImg.getBoundingClientRect();
                        const cartRect = cartIcon.getBoundingClientRect();
                        
                        const flyingImg = productImg.cloneNode();
                        flyingImg.style.position = 'fixed';
                        flyingImg.style.top = imgRect.top + 'px';
                        flyingImg.style.left = imgRect.left + 'px';
                        flyingImg.style.width = imgRect.width + 'px';
                        flyingImg.style.height = imgRect.height + 'px';
                        flyingImg.style.objectFit = 'cover';
                        flyingImg.style.borderRadius = '8px';
                        flyingImg.style.zIndex = '999999';
                        flyingImg.style.transition = 'all 0.8s cubic-bezier(0.25, 1, 0.5, 1)';
                        flyingImg.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
                        
                        document.body.appendChild(flyingImg);
                        
                        setTimeout(() => {
                            flyingImg.style.top = cartRect.top + 'px';
                            flyingImg.style.left = cartRect.left + 'px';
                            flyingImg.style.width = '20px';
                            flyingImg.style.height = '20px';
                            flyingImg.style.opacity = '0.5';
                            flyingImg.style.borderRadius = '50%';
                        }, 20);
                        
                        setTimeout(() => {
                            flyingImg.remove();
                        }, 800);
                    }
                    
                    // Success UX
                    $button.html(originalText).css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                },
                error: function() {
                    $button.html(originalText).css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                }
            });
            
            return false;
        });
    }
});
</script>
