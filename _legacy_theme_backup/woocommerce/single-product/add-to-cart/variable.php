<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart mt-8 mb-24 md:!mb-0 w-full" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
    <?php do_action( 'woocommerce_before_variations_form' ); ?>

    <?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
        <p class="stock out-of-stock text-[#e3342f] text-[10px] font-mono font-bold tracking-widest uppercase mb-4">
            <?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?>
        </p>
    <?php else : ?>
        <!-- Mobile Selected Variation Summary Trigger (Like Marketplace) -->
        <div class="md:hidden border border-[#e5e2de] rounded-lg p-3 mb-6 flex justify-between items-center bg-[#fcf9f5] raabiha-open-sheet-btn">
            <div>
                <div class="text-[10px] font-mono tracking-widest text-[#615e57] uppercase mb-1">Varian</div>
                <div class="text-[12px] font-sans font-medium text-[#1c1c1a] raabiha-summary-text">Pilih Varian</div>
            </div>
            <svg class="w-5 h-5 text-[#1c1c1a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
        </div>

        <!-- Backdrop for Bottom Sheet -->
        <div id="raabiha-variations-backdrop" class="fixed inset-0 bg-black/50 opacity-0 transition-opacity duration-300 pointer-events-none z-[55] md:hidden"></div>

        <!-- Variations Container (Bottom Sheet on Mobile, Static on Desktop) -->
        <div id="raabiha-variations-sheet" class="variations md:mb-8 fixed md:static inset-x-0 bottom-0 md:bottom-auto pb-[80px] md:pb-0 bg-white md:bg-transparent z-[60] md:z-auto p-6 md:p-0 rounded-t-3xl md:rounded-none shadow-[0_-10px_40px_rgba(0,0,0,0.1)] md:shadow-none transform translate-y-full md:translate-y-0 transition-transform duration-300 pointer-events-auto max-h-[80vh] overflow-y-auto md:max-h-none md:overflow-visible">
            
            <!-- Mobile Sheet Handle & Header -->
            <div class="md:hidden flex flex-col items-center mb-6">
                <div class="w-12 h-1.5 bg-[#e5e2de] rounded-full mb-4"></div>
                <h3 class="text-lg font-serif font-bold text-[#1c1c1a] w-full text-left">Pilih Varian</h3>
            </div>

            <?php foreach ( $attributes as $attribute_name => $options ) : 
                $is_color = ( false !== stripos( $attribute_name, 'color' ) || false !== stripos( $attribute_name, 'warna' ) );
            ?>
                <div class="mb-6 raabiha-variation-row" data-attribute="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
                    <div class="flex justify-between items-center mb-3">
                        <label class="text-[#1c1c1a] text-[10px] font-mono font-bold tracking-widest uppercase flex items-center" for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
                            <?php echo wc_attribute_label( $attribute_name ); ?>
                            <?php if ( $is_color ) : ?>
                                <span class="raabiha-selected-val ml-1" style="display: none;">: <span class="font-normal text-[#615e57]"></span></span>
                            <?php endif; ?>
                        </label>
                        <?php if ( ! $is_color ) : ?>
                            <a href="#" class="text-[#615e57] hover:text-[#1c1c1a] text-[9px] font-mono uppercase tracking-widest transition-colors underline decoration-1 underline-offset-4">PANDUAN UKURAN</a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Hidden Select for WooCommerce Core -->
                    <div class="hidden">
                        <?php
                            wc_dropdown_variation_attribute_options(
                                array(
                                    'options'   => $options,
                                    'attribute' => $attribute_name,
                                    'product'   => $product,
                                )
                            );
                        ?>
                    </div>

                    <!-- Custom UI -->
                    <div class="flex flex-wrap gap-2 raabiha-custom-options">
                        <?php 
                        if ( ! empty( $options ) ) {
                            foreach ( $options as $option ) {
                                $checked = '';
                                if ( isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) && $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] === $option ) {
                                    $checked = 'active';
                                }
                                
                                if ( $is_color ) {
                                    // Custom color swatches mapping
                                    $color_hex = '#1c1c1a'; // Default
                                    $opt_lower = strtolower($option);
                                    if(strpos($opt_lower, 'charcoal') !== false) $color_hex = '#3f3f46';
                                    elseif(strpos($opt_lower, 'sand') !== false) $color_hex = '#d2c2a3';
                                    elseif(strpos($opt_lower, 'green') !== false) $color_hex = '#166534';
                                    elseif(strpos($opt_lower, 'blue') !== false) $color_hex = '#1e3a8a';
                                    elseif(strpos($opt_lower, 'red') !== false) $color_hex = '#991b1b';
                                    elseif(strpos($opt_lower, 'white') !== false) $color_hex = '#ffffff';

                                    echo '<button type="button" data-value="' . esc_attr( $option ) . '" class="raabiha-swatch ' . esc_attr( $checked ) . ' w-8 h-8 rounded-full border-2 border-[#e5e2de] focus:outline-none focus:border-[#1c1c1a] hover:border-[#1c1c1a] transition-all flex items-center justify-center p-0.5" aria-label="' . esc_attr( $option ) . '">';
                                    echo '<span class="block w-full h-full rounded-full" style="background-color: ' . esc_attr( $color_hex ) . ';"></span>';
                                    echo '</button>';
                                } else {
                                    // Custom buttons (Size, etc)
                                    echo '<button type="button" data-value="' . esc_attr( $option ) . '" class="raabiha-swatch ' . esc_attr( $checked ) . ' flex-1 min-w-[4rem] border border-[#e5e2de] py-3 text-[#1c1c1a] text-[10px] font-mono font-bold uppercase tracking-widest hover:border-[#1c1c1a] focus:outline-none transition-colors">' . esc_html( $option ) . '</button>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="mt-4 pb-4 md:pb-0">
                <?php echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations hidden text-[#615e57] hover:text-[#1c1c1a] text-[9px] font-mono uppercase tracking-widest underline decoration-1 underline-offset-4" href="#" aria-label="' . esc_attr__( 'Clear options', 'woocommerce' ) . '">' . esc_html__( 'Clear Selection', 'woocommerce' ) . '</a>' ) ) : ''; ?>
            </div>
        </div>

        <div class="single_variation_wrap">
            <?php
                /**
                 * Hook: woocommerce_before_single_variation.
                 */
                do_action( 'woocommerce_before_single_variation' );

                /**
                 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
                 *
                 * @since 2.4.0
                 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
                 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
                 */
                do_action( 'woocommerce_single_variation' );

                /**
                 * Hook: woocommerce_after_single_variation.
                 */
                do_action( 'woocommerce_after_single_variation' );
            ?>
        </div>
    <?php endif; ?>

    <?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
?>

<style>
/* Force variation wrap to always show (override WooCommerce slideUp) */
.woocommerce-variation.single_variation {
    display: none !important; /* Hide the native price/availability text rendered by WooCommerce, we update main price instead */
}
.woocommerce-variation-add-to-cart {
    display: block !important;
}
.single_variation_wrap {
    display: block !important;
}

/* Active state for custom swatches */
.raabiha-swatch.active {
    border-color: #1c1c1a !important;
}
.raabiha-swatch:not(.rounded-full).active {
    background-color: #1c1c1a;
    color: #ffffff;
}

/* Disabled state for add to cart button (Desktop Only) */
@media (min-width: 768px) {
    .woocommerce-variation-add-to-cart .single_add_to_cart_button.disabled {
        opacity: 0.8 !important;
        cursor: not-allowed !important;
        background-color: #e5e7eb !important; /* Light gray background */
        color: #9ca3af !important; /* Medium gray text */
        pointer-events: none; /* Prevents clicks */
    }
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync custom swatches with hidden select fields
    const variationForm = document.querySelector('.variations_form');
    if (!variationForm) return;

    const rows = variationForm.querySelectorAll('.raabiha-variation-row');
    
    // Function to sync UI with selects
    function syncSwatches() {
        rows.forEach(row => {
            const select = row.querySelector('select');
            const swatches = row.querySelectorAll('.raabiha-swatch');
            const val = select.value;
            const selectedValDisplay = row.querySelector('.raabiha-selected-val');
            
            swatches.forEach(s => {
                if (val && s.getAttribute('data-value') === val) {
                    s.classList.add('active');
                    if (selectedValDisplay) {
                        selectedValDisplay.style.display = 'inline';
                        selectedValDisplay.querySelector('span').textContent = val;
                    }
                } else {
                    s.classList.remove('active');
                }
            });
            
            if (!val && selectedValDisplay) {
                selectedValDisplay.style.display = 'none';
                selectedValDisplay.querySelector('span').textContent = '';
            }
        });
    }
    
    rows.forEach(row => {
        const select = row.querySelector('select');
        const swatches = row.querySelectorAll('.raabiha-swatch');
        
        swatches.forEach(swatch => {
            swatch.addEventListener('click', function(e) {
                e.preventDefault();
                
                const val = this.getAttribute('data-value');
                const isCurrentlyActive = this.classList.contains('active');
                
                // If already active, WooCommerce allows unselecting. Let's toggle it.
                if (isCurrentlyActive) {
                    select.value = '';
                } else {
                    select.value = val;
                }
                
                // Trigger change event for WooCommerce
                if (typeof jQuery !== 'undefined') {
                    jQuery(select).trigger('change');
                } else {
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                }
                
                // Sync the UI immediately
                syncSwatches();
            });
        });
        
        // Also sync if the select changes natively (e.g. by WooCommerce JS)
        if (typeof jQuery !== 'undefined') {
            jQuery(select).on('change', syncSwatches);
        } else {
            select.addEventListener('change', syncSwatches);
        }
    });

    if (typeof jQuery !== 'undefined') {
        // --- Dynamic Main Price Update & Button State ---
        const mainPrice = document.getElementById('main-product-price');
        const $addToCartBtn = jQuery('.single_add_to_cart_button');
        const $buyNowBtn = jQuery('.single_buy_now_button');
        
        // --- Bottom Sheet Logic ---
        const sheet = document.getElementById('raabiha-variations-sheet');
        const backdrop = document.getElementById('raabiha-variations-backdrop');
        const openBtn = document.querySelector('.raabiha-open-sheet-btn');
        const summaryText = document.querySelector('.raabiha-summary-text');
        
        function openSheet() {
            if (sheet && backdrop && window.innerWidth < 768) {
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                sheet.classList.remove('translate-y-full');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeSheet() {
            if (sheet && backdrop && window.innerWidth < 768) {
                sheet.classList.add('translate-y-full');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                document.body.style.overflow = '';
            }
        }
        
        if (openBtn) openBtn.addEventListener('click', openSheet);
        if (backdrop) backdrop.addEventListener('click', closeSheet);
        
        // Update Summary Text when selection changes
        function updateSummary() {
            if (!summaryText) return;
            const selectedVals = [];
            rows.forEach(row => {
                const select = row.querySelector('select');
                if (select && select.value) {
                    selectedVals.push(select.value);
                }
            });
            if (selectedVals.length > 0) {
                summaryText.textContent = selectedVals.join(', ');
                summaryText.classList.add('text-[#064e3b]');
            } else {
                summaryText.textContent = 'Pilih Varian';
                summaryText.classList.remove('text-[#064e3b]');
            }
        }
        jQuery(variationForm).on('change', 'select', updateSummary);
        
        if (mainPrice) {
            const originalPriceHtml = mainPrice.innerHTML;
            
            jQuery(variationForm).on('show_variation', function(event, variation) {
                if (variation.price_html) {
                    mainPrice.innerHTML = variation.price_html;
                }
                $addToCartBtn.removeClass('disabled');
                $buyNowBtn.removeClass('disabled');
            });
            
            jQuery(variationForm).on('hide_variation', function() {
                mainPrice.innerHTML = originalPriceHtml;
                $addToCartBtn.addClass('disabled');
                $buyNowBtn.addClass('disabled');
            });
        }

        // Track which button was clicked
        let actionType = 'cart';
        let $clickedButton = null;
        
        // Use native capture phase to intercept clicks BEFORE WooCommerce's delegated handlers run
        document.addEventListener('click', function(e) {
            let target = e.target.closest('.single_add_to_cart_button, .single_buy_now_button');
            if (!target) return;
            
            $clickedButton = jQuery(target);
            const isBuyNow = target.classList.contains('single_buy_now_button');
            actionType = isBuyNow ? 'buy_now' : 'cart';
            jQuery('#raabiha_action_type').val(actionType);
            
            // Safe check: If no variation is selected (variation_id is empty or 0) and we are on mobile
            const variationId = jQuery('input[name="variation_id"]').val();
            if ((!variationId || variationId === '0' || variationId === '') && window.innerWidth < 768) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                openSheet();
            }
        }, true); // true = capture phase

        // --- Custom AJAX Add to Cart ---
        jQuery(document).on('submit', 'form.variations_form', function(e) {
            e.preventDefault();
            
            const $form = jQuery(this);
            const $button = $clickedButton || (actionType === 'buy_now' ? $form.find('.single_buy_now_button:visible') : $form.find('.single_add_to_cart_button:visible'));
            
            // Safe fallback just in case multiple visible
            const $targetBtn = $button.length > 1 ? $button.first() : $button;
            
            // Check if variations are valid/selected
            if ($button.is('.disabled')) {
                if (window.innerWidth < 768) {
                    openSheet();
                } else {
                    jQuery('html, body').animate({
                        scrollTop: jQuery('.variations').offset().top - 100
                    }, 500);
                    jQuery('.raabiha-variation-row select:invalid, .raabiha-variation-row').addClass('animate-pulse');
                    setTimeout(() => jQuery('.raabiha-variation-row').removeClass('animate-pulse'), 1000);
                }
                return false;
            }
            
            // UX: Button Animation
            const originalText = $targetBtn.html();
            const spinnerSvg = '<svg class="animate-spin h-5 w-5 mx-auto text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            $targetBtn.html(spinnerSvg).css({
                'opacity': '0.8',
                'pointer-events': 'none'
            });
            
            // Close sheet on submit
            closeSheet();
            
            // Prepare Data
            const formData = new FormData($form[0]);
            const variationId = $form.find('input[name="variation_id"]').val();
            
            // WooCommerce's add_to_cart AJAX endpoint expects the variation_id to be passed as product_id for variable products
            if (variationId && variationId !== '0' && variationId !== '') {
                formData.set('product_id', variationId);
            }
            
            // CRITICAL FIX: Remove 'add-to-cart' from the payload.
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
                    jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $targetBtn]);
                    
                    // Clear default WooCommerce notices that might be injected by fragments
                    setTimeout(() => {
                        jQuery('.woocommerce-notices-wrapper').empty();
                        jQuery('.woocommerce-message').remove();
                        jQuery('.woocommerce-error').remove();
                    }, 10);
                    
                    // Reset quantity to minimum
                    const qtyInput = $form.find('input.qty');
                    if (qtyInput.length > 0) {
                        qtyInput.val(qtyInput.attr('min') || 1);
                    }
                    
                    // Simulate clicking "Clear Selection" to reset swatches and variations
                    const resetBtn = $form.find('.reset_variations');
                    if (resetBtn.length > 0) {
                        resetBtn.trigger('click');
                    }
                    
                    // --- Flying to Cart Animation ---
                    // Find the visible cart icon (handles mobile vs desktop layouts)
                    let cartIcon = null;
                    document.querySelectorAll('.cart-toggle-btn, .raabiha-mobile-cart-btn').forEach(icon => {
                        // offsetParent is null if the element or its parent is display: none
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
                        
                        // Small timeout to allow the browser to render the initial position
                        setTimeout(() => {
                            flyingImg.style.top = cartRect.top + 'px';
                            flyingImg.style.left = cartRect.left + 'px';
                            flyingImg.style.width = '20px';
                            flyingImg.style.height = '20px';
                            flyingImg.style.opacity = '0.5';
                            flyingImg.style.borderRadius = '50%';
                        }, 20);
                        
                        // Clean up
                        setTimeout(() => {
                            flyingImg.remove();
                        }, 800);
                    }
                    
                    // Success UX
                    $targetBtn.html(originalText).css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                },
                error: function() {
                    $targetBtn.html(originalText).css({
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
