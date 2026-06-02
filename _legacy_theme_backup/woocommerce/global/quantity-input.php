<?php
/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 */

defined( 'ABSPATH' ) || exit;

/* translators: %s: Quantity. */
$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'woocommerce' );
?>
<div class="quantity flex items-center border border-[#1c1c1a] bg-transparent mr-4 h-14">
    <?php
    /**
     * Hook to output something before the quantity input field.
     *
     * @since 7.2.0
     */
    do_action( 'woocommerce_before_quantity_input_field' );
    ?>
    <button type="button" class="minus w-14 h-full flex justify-center items-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none cursor-pointer">
        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
    </button>
    <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $label ); ?></label>
    <input
        type="number"
        <?php echo $readonly ? 'readonly="readonly"' : ''; ?>
        id="<?php echo esc_attr( $input_id ); ?>"
        class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?> w-12 h-full text-center text-[11px] font-mono font-bold tracking-widest text-[#1c1c1a] bg-transparent border-none p-0 focus:ring-0 appearance-none m-0"
        name="<?php echo esc_attr( $input_name ); ?>"
        value="<?php echo esc_attr( $input_value ); ?>"
        title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
        size="4"
        min="<?php echo esc_attr( $min_value ); ?>"
        max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
        <?php if ( ! $readonly ) : ?>
            step="<?php echo esc_attr( $step ); ?>"
            placeholder="<?php echo esc_attr( $placeholder ); ?>"
            inputmode="<?php echo esc_attr( $inputmode ); ?>"
            autocomplete="<?php echo esc_attr( isset( $autocomplete ) ? $autocomplete : 'on' ); ?>"
        <?php endif; ?>
    />
    <button type="button" class="plus w-14 h-full flex justify-center items-center text-[#1c1c1a] hover:bg-[#f2efe8] transition-colors focus:outline-none cursor-pointer">
        <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    </button>
    <?php
    /**
     * Hook to output something after the quantity input field
     *
     * @since 3.6.0
     */
    do_action( 'woocommerce_after_quantity_input_field' );
    ?>
</div>

<script>
// Lightweight script for plus/minus buttons since Woo core doesn't include the JS for custom buttons by default
if (typeof raabihaQtyInit === 'undefined') {
    var raabihaQtyInit = true;
    document.addEventListener('click', function(e) {
        if (e.target.closest('.quantity .plus') || e.target.closest('.quantity .minus')) {
            const btn = e.target.closest('button');
            const qtyContainer = btn.closest('.quantity');
            const input = qtyContainer.querySelector('input.qty');
            
            if (!input) return;
            
            const current = parseFloat(input.value) || 0;
            const max = parseFloat(input.max) || Infinity;
            const min = parseFloat(input.min) || 0;
            const step = parseFloat(input.step) || 1;
            
            if (btn.classList.contains('plus')) {
                if (current < max) {
                    input.value = current + step;
                }
            } else {
                if (current > min) {
                    input.value = current - step;
                }
            }
            
            // Trigger change event for WooCommerce to detect
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
            
            // Also trigger jQuery change if jQuery exists
            if (typeof jQuery !== 'undefined') {
                jQuery(input).trigger('change');
            }
        }
    });
}
</script>
