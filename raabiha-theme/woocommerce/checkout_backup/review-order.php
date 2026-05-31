<?php
/**
 * Review order table
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table w-full raabiha-order-summary bg-[#fcf9f5] border border-[#e5e2de] block p-6 lg:p-8">
    <caption class="text-2xl font-serif font-bold text-[#1c1c1a] mb-6 border-b border-[#e5e2de] pb-4 text-left w-full block" style="caption-side: top;">Order Summary</caption>
    
    <tbody class="block w-full raabiha-order-items mb-8 space-y-6">
        <?php
        do_action( 'woocommerce_review_order_before_cart_contents' );

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('thumbnail', array('class' => 'w-full h-full object-cover')), $cart_item, $cart_item_key );
                ?>
                <tr class="flex gap-4 items-start w-full border-none <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                    <td class="shrink-0 w-16 h-20 bg-[#f5f5f5] p-0 border-none block">
                        <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </td>
                    <td class="flex-1 min-w-0 flex flex-col justify-between h-20 p-0 border-none block w-full text-left">
                        <div>
                            <div class="text-sm font-sans font-medium text-[#1c1c1a] leading-tight mb-1">
                                <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                            </div>
                            <div class="text-[9px] font-mono tracking-widest uppercase text-[#615e57]">
                                <?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </div>
                        <div class="flex justify-between items-end mt-auto w-full">
                            <div class="text-[9px] font-mono tracking-widest text-[#615e57] uppercase">
                                QTY: <?php echo sprintf( '%02d', $cart_item['quantity'] ); ?>
                            </div>
                            <div class="text-sm font-sans font-bold text-[#1c1c1a]">
                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }

        do_action( 'woocommerce_review_order_after_cart_contents' );
        ?>
    </tbody>

    <tfoot class="block w-full raabiha-order-totals space-y-4 mb-8 pt-4">
        <tr class="flex justify-between items-center text-sm font-sans w-full border-none cart-subtotal">
            <th class="p-0 border-none text-left text-[#615e57] font-normal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
            <td class="p-0 border-none text-right font-bold text-[#1c1c1a]"><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>

        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
            <tr class="flex justify-between items-center text-sm font-sans text-[#09493B] w-full border-none cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                <th class="p-0 border-none text-left font-normal">Voucher (<?php echo esc_html( strtoupper($code) ); ?>)</th>
                <td class="p-0 border-none text-right font-bold"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
            <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
            <?php wc_cart_totals_shipping_html(); ?>
            <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
        <?php endif; ?>

        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
            <tr class="flex justify-between items-center text-sm font-sans text-[#615e57] w-full border-none fee">
                <th class="p-0 border-none text-left font-normal"><?php echo esc_html( $fee->name ); ?></th>
                <td class="p-0 border-none text-right font-bold text-[#1c1c1a]"><?php wc_cart_totals_fee_html( $fee ); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
            <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                    <tr class="flex justify-between items-center text-sm font-sans text-[#615e57] w-full border-none tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                        <th class="p-0 border-none text-left font-normal"><?php echo esc_html( $tax->label ); ?></th>
                        <td class="p-0 border-none text-right font-bold text-[#1c1c1a]"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="flex justify-between items-center text-sm font-sans text-[#615e57] w-full border-none tax-total">
                    <th class="p-0 border-none text-left font-normal"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                    <td class="p-0 border-none text-right font-bold text-[#1c1c1a]"><?php wc_cart_totals_taxes_total_html(); ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
        <tr class="flex justify-between items-center mb-8 border-t border-x-0 border-b-0 border-solid border-[#e5e2de] pt-6 w-full order-total">
            <th class="p-0 border-none text-left text-2xl font-serif font-bold text-[#1c1c1a]">Total</th>
            <td class="p-0 border-none text-right text-2xl font-serif font-bold text-[#1c1c1a]">
                <?php wc_cart_totals_order_total_html(); ?>
            </td>
        </tr>
        <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

        <tr class="block w-full border-none p-0 mt-4">
            <td colspan="2" class="p-0 border-none block w-full">
                <!-- Promo Code (Visual Only) -->
                <div class="raabiha-order-promo flex border border-[#e5e2de] bg-transparent w-full">
                    <input type="text" id="raabiha-fake-coupon-input" placeholder="Promo Code" class="w-full bg-transparent border-none px-4 py-3 text-sm font-sans text-[#615e57] focus:ring-0 outline-none">
                    <button type="button" id="raabiha-fake-coupon-btn" class="px-4 text-[9px] font-mono font-bold tracking-widest text-[#09493B] uppercase hover:text-black">APPLY</button>
                </div>
            </td>
        </tr>
    </tfoot>

    <style>
    /* Styling to make native shipping options look decent in this view */
    .raabiha-order-summary tr.shipping {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
        border: none !important;
        margin-bottom: 0 !important;
    }
    .raabiha-order-summary tr.shipping th {
        font-weight: normal;
        color: #615e57;
        padding: 0;
        border: none;
        text-align: left;
        width: 30%;
        font-size: 0.875rem;
    }
    .raabiha-order-summary tr.shipping td {
        text-align: right;
        color: #1c1c1a;
        padding: 0;
        border: none;
        width: 70%;
        font-size: 0.875rem;
    }
    .raabiha-order-summary ul#shipping_method {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .raabiha-order-summary ul#shipping_method li {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.5rem;
    }
    .raabiha-order-summary ul#shipping_method li:last-child {
        margin-bottom: 0;
    }
    .raabiha-order-summary ul#shipping_method li input[type="radio"] {
        margin: 0;
        accent-color: #09493B;
    }
    .raabiha-order-summary ul#shipping_method li label {
        margin: 0;
        font-weight: bold;
        color: #1c1c1a;
        cursor: pointer;
    }
    .raabiha-order-summary .woocommerce-shipping-destination {
        margin-top: 0.5rem;
        margin-bottom: 0;
        font-size: 0.75rem;
        color: #615e57;
    }
    .raabiha-order-summary .shipping-calculator-button {
        color: #09493B;
        text-decoration: underline;
        font-size: 0.75rem;
        display: inline-block;
        margin-top: 0.25rem;
    }
    </style>
</table>
