<?php
/**
 * Cart Page Override
 *
 * This template overrides the default WooCommerce cart to perfectly match
 * the Raabiha Editorial Brutalism & Architectural Modesty design system.
 * 
 * Note: Currently static UI as requested by CTO.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>

<div class="max-w-[1440px] mx-auto px-6 md:px-[64px] py-12 md:py-24">
    <!-- Header -->
    <div class="mb-16">
        <h1 class="font-serif text-[32px] md:text-[48px] font-bold text-[#1c1c1a] tracking-tight uppercase">Keranjang</h1>
        <div class="font-mono text-[9px] font-medium tracking-[0.2em] text-[#615e57] uppercase mt-2">
            <?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?> Produk Terpilih
        </div>
    </div>

    <!-- Main Grid -->
    <?php if ( ! WC()->cart->is_empty() ) : ?>
    <form class="woocommerce-cart-form grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-12 lg:gap-16 items-start" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
    <?php else : ?>
    <div class="grid grid-cols-1 gap-12 lg:gap-16 items-start">
    <?php endif; ?>
        
        <!-- Left Column: Product List -->
        <div class="flex flex-col">
            <div class="woocommerce-notices-wrapper w-full mb-8">
                <?php 
                // Clear all success notices (like "Cart updated" or "Coupon applied") 
                // since our UI already provides visual feedback for these actions.
                if ( WC()->session ) {
                    $notices = WC()->session->get( 'wc_notices', array() );
                    if ( isset( $notices['success'] ) ) {
                        unset( $notices['success'] );
                        WC()->session->set( 'wc_notices', $notices );
                    }
                }
                wc_print_notices(); 
                ?>
            </div>
            <?php
            if ( WC()->cart->is_empty() ) :
                echo '<p class="font-sans text-[14px] text-[#615e57]">' . esc_html__( 'Keranjang Anda saat ini kosong.', 'woocommerce' ) . '</p>';
                echo '<a href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '" class="inline-block mt-4 bg-[#064e3b] text-white py-3 px-6 font-mono text-[10px] font-bold uppercase tracking-[0.2em]">Kembali Belanja</a>';
            else :
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        
                        // Image
                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', [ 'class' => 'w-full h-full object-cover' ] ), $cart_item, $cart_item_key );
                        if ( empty( $thumbnail ) ) {
                            $thumbnail = sprintf('<img src="%s" alt="Placeholder" class="w-full h-full object-cover">', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ));
                        }

                        // Product Name
                        $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

                        // SKU
                        $sku = $_product->get_sku();
                        if ( ! $sku ) {
                            $sku = '-';
                        }

                        // Subtotal
                        $subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
            ?>
            <!-- Item -->
            <div class="cart-item-row flex flex-row sm:flex-row gap-3 sm:gap-8 pb-6 sm:pb-12 mb-6 sm:mb-12 border-b border-[#e5e2de] sm:border-[rgba(23,24,24,0.1)]" data-price="<?php echo esc_attr( wc_get_price_to_display( $_product, array( 'qty' => 1 ) ) ); ?>">
                <!-- Image -->
                <div class="w-[80px] h-[80px] sm:w-[280px] sm:h-[280px] bg-[#f0ede9] shrink-0">
                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <!-- Info -->
                <div class="flex flex-col flex-1 py-0 sm:py-2 min-w-0">
                    <div class="flex justify-between items-start gap-2 sm:gap-4 w-full">
                        <h3 class="font-serif text-[14px] sm:text-[24px] font-semibold text-[#1c1c1a] leading-tight uppercase sm:normal-case line-clamp-2 sm:line-clamp-none pr-1">
                            <?php 
                            if ( ! $product_permalink ) {
                                echo wp_kses_post( $product_name );
                            } else {
                                echo sprintf( '<a href="%s" class="hover:text-[#615e57] transition-colors">%s</a>', esc_url( $product_permalink ), wp_kses_post( $product_name ) );
                            }
                            ?>
                        </h3>
                        <div class="item-subtotal-val font-sans text-[14px] sm:text-[18px] font-medium sm:font-normal text-[#1c1c1a] whitespace-nowrap shrink-0">
                            <?php echo $subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    </div>
                    
                    <!-- Mobile Variations -->
                    <div class="flex sm:hidden font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-1 mb-auto flex-wrap gap-1 truncate w-full">
                        <?php 
                        $item_details = array();
                        if ( $sku && $sku !== '-' ) {
                            $item_details[] = 'SKU: ' . esc_html( $sku );
                        }
                        if ( $_product->is_type( 'variation' ) ) {
                            $attributes = $_product->get_variation_attributes();
                            foreach ( $attributes as $attribute_name => $attribute_value ) {
                                $value = $_product->get_attribute( str_replace( 'attribute_', '', $attribute_name ) );
                                if ( ! $value ) {
                                    $value = $attribute_value;
                                }
                                $item_details[] = esc_html( $value );
                            }
                        }
                        echo implode( ' <span class="mx-1">/</span> ', $item_details );
                        ?>
                    </div>
                    
                    <!-- Desktop Variations -->
                    <div class="hidden sm:block font-mono text-[9px] uppercase tracking-[0.1em] text-[#615e57] mt-2 mb-8">SKU: <?php echo esc_html( $sku ); ?></div>
                    <div class="hidden sm:grid grid-cols-[80px_1fr] gap-y-4 mb-auto">
                        <?php 
                        if ( $_product->is_type( 'variation' ) ) {
                            $attributes = $_product->get_variation_attributes();
                            foreach ( $attributes as $attribute_name => $attribute_value ) {
                                $label = wc_attribute_label( str_replace( 'attribute_', '', $attribute_name ), $_product );
                                $value = $_product->get_attribute( str_replace( 'attribute_', '', $attribute_name ) );
                                if ( ! $value ) {
                                    $value = $attribute_value;
                                }
                                echo '<div class="font-mono text-[10px] font-semibold tracking-[0.1em] text-[#1c1c1a] uppercase">' . esc_html( $label ) . '</div>';
                                echo '<div class="font-sans text-[14px] text-[#1c1c1a] uppercase">' . esc_html( $value ) . '</div>';
                            }
                        }
                        ?>
                    </div>
                    
                    <div class="flex justify-between items-center mt-3 sm:mt-8 w-full">
                        <div class="border border-[#e5e2de] flex items-stretch h-[36px] sm:h-[48px] qty-container">
                            <button type="button" class="qty-btn minus w-10 sm:w-12 h-full flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] hover:bg-black/5 transition-colors focus:outline-none">
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                            </button>
                            <input 
                                type="number" 
                                name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" 
                                value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" 
                                min="0" 
                                class="qty-input h-full w-8 sm:w-12 bg-transparent text-center font-mono text-[12px] sm:text-[14px] text-[#1c1c1a] focus:outline-none appearance-none p-0 m-0 border-0" 
                                style="-moz-appearance: textfield;"
                            >
                            <button type="button" class="qty-btn plus w-10 sm:w-12 h-full flex items-center justify-center text-[#615e57] hover:text-[#1c1c1a] hover:bg-black/5 transition-colors focus:outline-none">
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <?php
                        // Mobile Remove Link
                        echo sprintf(
                            '<a href="%s" class="block sm:hidden font-mono text-[10px] font-bold tracking-[0.1em] text-[#ba1a1a] uppercase hover:text-[#1c1c1a] transition-colors" aria-label="%s" data-product_id="%s" data-product_sku="%s">REMOVE</a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            esc_html__( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        );
                        // Desktop Remove Link
                        echo sprintf(
                            '<a href="%s" class="hidden sm:flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] hover:text-[#1c1c1a] transition-colors group" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg><span>Hapus</span></a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            esc_html__( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        );
                        ?>
                    </div>
                </div>
            </div>
            <?php
                    }
                }
            endif;
            ?>
            
            <?php if ( ! WC()->cart->is_empty() ) : ?>
            <!-- Bottom Trust Banner -->
            <!-- Bottom Trust Banner -->
            <div class="flex sm:hidden bg-[#fcf9f5] border-l-2 border-[#064e3b] p-4 mt-0 mb-12 items-start gap-4">
                <div class="text-[#064e3b] shrink-0 mt-0.5">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12c0 4.418 3.582 8 8 8s8-3.582 8-8c0-4.418-3.582-8-8-8-4.418 0-8 3.582-8 8zm0 0l8-8" /></svg>
                </div>
                <div>
                    <p class="font-sans text-[13px] text-[#615e57] leading-relaxed">Pesanan Anda mendukung <span class="font-bold text-[#1c1c1a]">Keahlian Arsitektural Berkelanjutan</span> dan murni menggunakan kemasan sutra 100% biodegradable.</p>
                </div>
            </div>
            
            <div class="hidden sm:flex bg-[#f6f3ef] p-6 mt-4 flex-col sm:flex-row gap-6 items-start sm:items-center">
                <div class="text-[#064e3b] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div>
                    <h4 class="font-sans text-[14px] font-semibold text-[#1c1c1a] mb-1">Keahlian Arsitektural Berkelanjutan</h4>
                    <p class="font-sans text-[14px] text-[#615e57]">Pesanan Anda mendukung komunitas pengrajin dan murni menggunakan kemasan sutra 100% biodegradable.</p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right Column: Order Summary -->
        <?php if ( ! WC()->cart->is_empty() ) : ?>
        <div class="bg-transparent lg:bg-[#f0ede9] p-0 lg:p-10 sticky top-[120px]">
            <h2 class="font-mono text-[10px] font-bold tracking-[0.2em] text-[#1c1c1a] uppercase mb-8 hidden lg:block">Ringkasan Pesanan</h2>
            
            <div class="flex flex-col gap-0 lg:gap-6 font-sans text-[14px] text-[#1c1c1a]">
                <!-- Subtotal Row -->
                <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal">Subtotal</span>
                    <span class="cart-subtotal-val"><?php wc_cart_totals_subtotal_html(); ?></span>
                </div>
                
                <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                    <div class="flex justify-between items-center text-[#064e3b] py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                        <span class="font-mono text-[10px] uppercase tracking-[0.1em] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal lg:text-[#064e3b]">Voucher: <?php echo esc_html( $code ); ?></span>
                        <div class="flex gap-4 items-center">
                            <span class="font-sans text-[14px] font-medium lg:font-normal">-<?php 
                                $discount_amount = WC()->cart->get_coupon_discount_amount( $code, WC()->cart->display_cart_ex_tax );
                                echo wc_price( $discount_amount ); 
                            ?></span>
                            <a href="<?php echo esc_url( add_query_arg( 'remove_coupon', wp_unslash( $code ), wc_get_cart_url() ) ); ?>" class="remove-coupon-btn text-[#1c1c1a] hover:text-[#ba1a1a] transition-colors" title="Hapus Voucher"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                    <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                        <div class="flex justify-between py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                            <span class="font-mono text-[10px] uppercase tracking-[0.1em] text-[#615e57] lg:text-[#1c1c1a] lg:font-sans lg:text-[14px] lg:normal-case lg:tracking-normal"><?php echo esc_html( $tax->label ); ?></span>
                            <span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="h-px bg-[rgba(23,24,24,0.1)] my-8 hidden lg:block"></div>
            
            <?php 
            $has_vouchers = !empty(WC()->cart->get_applied_coupons());
            $voucher_btn_class = $has_vouchers ? 'lg:border-[#064e3b] lg:bg-[#eae8e4] text-[#064e3b]' : 'lg:border-[#1c1c1a] lg:hover:bg-[#f6f3ef] text-[#1c1c1a]';
            $voucher_btn_text = $has_vouchers ? 'VOUCHER TELAH DIPILIH' : 'PILIH VOUCHER';
            ?>
            <!-- Promo Code -->
            <div class="mb-10 py-4 border-b border-[#e5e2de] lg:border-none lg:py-0">
                <label class="font-mono text-[9px] font-semibold tracking-[0.2em] text-[#615e57] uppercase mb-4 hidden lg:block">Kode Promo / Voucher</label>
                <button type="button" id="open-voucher-modal" class="w-full lg:border lg:py-3 lg:px-4 flex justify-between items-center text-left transition-colors bg-transparent <?php echo esc_attr($voucher_btn_class); ?>">
                    <span class="font-mono text-[10px] uppercase tracking-[0.1em] font-bold flex items-center gap-2">
                        <?php if ($has_vouchers): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php endif; ?>
                        <?php echo esc_html($voucher_btn_text); ?>
                    </span>
                    <?php if ($has_vouchers): ?>
                        <span class="font-mono text-[9px] uppercase tracking-[0.1em] underline decoration-[#064e3b]/30 underline-offset-2">Ubah</span>
                    <?php else: ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <?php endif; ?>
                </button>
            </div>
            
            <!-- Total -->
            <div class="flex justify-between items-end mb-8 py-6 lg:py-0">
                <span class="font-mono text-[12px] lg:font-serif lg:text-[24px] font-bold lg:font-semibold tracking-[0.1em] lg:tracking-normal text-[#1c1c1a] uppercase"><span class="lg:hidden">ESTIMATED </span>TOTAL</span>
                <span class="cart-total-val font-serif text-[24px] lg:text-[32px] font-semibold text-[#1c1c1a] leading-none"><?php wc_cart_totals_order_total_html(); ?></span>
            </div>
            
            <!-- Button -->
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="flex justify-center items-center gap-2 w-full bg-[#064e3b] hover:bg-[#043326] text-white py-5 px-6 font-mono text-[10px] md:text-[12px] lg:text-[10px] font-bold tracking-[0.2em] uppercase text-center transition-colors">
                <span class="block lg:hidden">PROCEED TO CHECKOUT</span>
                <span class="hidden lg:block">LANJUT KE PEMBAYARAN</span>
                <svg class="block lg:hidden" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            
            <!-- Trust Badges -->
            <div class="mt-8 hidden lg:flex flex-col items-center gap-4">
                <div class="flex items-center gap-2 text-[#615e57]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span class="font-mono text-[9px] uppercase tracking-[0.1em]">Pembayaran Aman Terjamin</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Visa</div>
                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Amex</div>
                    <div class="border border-[#c4c7c7] px-2 py-0.5 font-mono text-[8px] uppercase tracking-wider text-[#615e57]">Apple Pay</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ( ! WC()->cart->is_empty() ) : ?>
            <input type="hidden" name="update_cart" value="Update cart">
            <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

        <!-- Voucher Modal -->
        <div id="voucher-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-4 transition-opacity duration-300 opacity-0">
            <div class="bg-[#fcf9f5] w-full max-w-md border border-[#1c1c1a] shadow-[4px_4px_0px_#1c1c1a] transform translate-y-4 transition-transform duration-300 modal-content">
                <div class="flex justify-between items-center border-b border-[#1c1c1a] p-4">
                    <h3 class="font-serif text-[20px] font-semibold text-[#1c1c1a]">Voucher Tersedia</h3>
                    <button type="button" id="close-voucher-modal" class="text-[#615e57] hover:text-[#ba1a1a] transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 flex flex-col gap-4 max-h-[60vh] overflow-y-auto">
                    <?php
                    $applied_coupons = WC()->cart->get_applied_coupons();
                    
                    $args = array(
                        'posts_per_page'   => -1,
                        'post_type'        => 'shop_coupon',
                        'post_status'      => 'publish',
                    );
                    $coupons = get_posts( $args );
                    if ( $coupons ) {
                        foreach ( $coupons as $coupon_post ) {
                            $coupon = new WC_Coupon( $coupon_post->post_name );
                            $code = $coupon->get_code();
                            $desc = $coupon_post->post_excerpt; 
                            $amount = $coupon->get_amount();
                            $type = $coupon->get_discount_type();
                            
                            $is_applied = in_array( strtolower( $code ), $applied_coupons, true );
                            
                            $is_eligible = true;
                            $ineligibility_reason = '';
                            
                            if ( ! $is_applied ) {
                                try {
                                    if ( ! $coupon->is_valid() ) {
                                        $is_eligible = false;
                                        $ineligibility_reason = wp_strip_all_tags( $coupon->get_error_message() );
                                    }
                                } catch ( Exception $e ) {
                                    $is_eligible = false;
                                    $ineligibility_reason = $e->getMessage();
                                }
                            }
                            
                            $min_spend = $coupon->get_minimum_amount();
                            $expiry = $coupon->get_date_expires();
                            
                            $conditions = array();
                            if ( $min_spend > 0 ) {
                                $conditions[] = 'Min. belanja Rp' . number_format($min_spend, 0, ',', '.');
                            }
                            if ( $expiry ) {
                                $conditions[] = 'Berlaku s/d ' . wc_format_datetime( $expiry, 'd M Y' );
                            }
                            $conditions_text = !empty($conditions) ? implode(' • ', $conditions) : '';
                            
                            $discount_text = '';
                            if ( $type === 'percent' ) {
                                $discount_text = 'Diskon ' . $amount . '%';
                            } elseif ( $type === 'fixed_cart' || $type === 'fixed_product' ) {
                                $discount_text = 'Diskon Rp' . number_format($amount, 0, ',', '.');
                            } else {
                                $discount_text = 'Spesial Promo';
                            }
                            
                            $container_classes = '';
                            if ( $is_applied ) {
                                $container_classes = 'border-[#064e3b] bg-[#eae8e4] cursor-default';
                            } elseif ( ! $is_eligible ) {
                                $container_classes = 'border-[#c4c7c7] bg-[#fcf9f5] opacity-50 cursor-not-allowed grayscale';
                            } else {
                                $container_classes = 'border-[#c4c7c7] cursor-pointer hover:border-[#1c1c1a] hover:bg-[#eae8e4] voucher-item';
                            }
                                
                            $badge_classes = $is_applied
                                ? 'bg-[#064e3b] border-[#064e3b] text-white flex items-center gap-1.5'
                                : 'border-[#c4c7c7] bg-white text-[#615e57]';
                            ?>
                            <div class="transition-colors group border p-4 <?php echo esc_attr( $container_classes ); ?>" <?php echo ( $is_applied || ! $is_eligible ) ? '' : 'data-code="' . esc_attr( $code ) . '"'; ?>>
                                <div class="flex justify-between items-center mb-1">
                                    <div class="font-serif text-[18px] font-semibold <?php echo $is_applied ? 'text-[#064e3b]' : 'text-[#1c1c1a] group-hover:text-[#064e3b]'; ?> transition-colors">
                                        <?php echo esc_html( $discount_text ); ?>
                                    </div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.1em] border px-2 py-1 <?php echo esc_attr( $badge_classes ); ?>">
                                        <?php if ( $is_applied ) : ?>
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <?php endif; ?>
                                        <?php echo esc_html( $code ); ?>
                                    </div>
                                </div>
                                <div class="font-sans text-[12px] <?php echo $is_applied ? 'text-[#064e3b]' : 'text-[#615e57]'; ?>">
                                    <?php echo esc_html( $desc ? $desc : 'Gunakan voucher ini pada saat checkout.' ); ?>
                                </div>
                                <?php if ( $conditions_text ) : ?>
                                <div class="font-sans text-[10px] mt-2 pt-2 border-t <?php echo $is_applied ? 'border-[#064e3b]/20 text-[#064e3b]/80' : 'border-[#c4c7c7] text-[#615e57]/80'; ?>">
                                    <?php echo esc_html( $conditions_text ); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ( ! $is_eligible && $ineligibility_reason ) : ?>
                                <div class="font-sans text-[10px] font-semibold text-[#ba1a1a] mt-2">
                                    ❌ <?php echo esc_html( wp_strip_all_tags( $ineligibility_reason ) ); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="text-center font-sans text-[14px] text-[#615e57] py-8">Belum ada voucher tersedia.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        </form>
        <script>
        jQuery(document).ready(function($) {
            let updateTimeout = null;
            let abortController = null;

            function formatMoney(amount) {
                return 'Rp' + parseFloat(amount).toLocaleString('id-ID');
            }

            $(document).on('click', '.qty-btn', function(e) {
                e.preventDefault();
                let $btn = $(this);
                let $input = $btn.siblings('.qty-input');
                let val = parseInt($input.val()) || 0;
                
                if ($btn.hasClass('plus')) {
                    $input.val(val + 1);
                } else if ($btn.hasClass('minus') && val > 0) {
                    $input.val(val - 1);
                }
                
                $input.trigger('change');
            });

            $(document).on('change', '.qty-input', function() {
                performOptimisticUpdate();
                scheduleCartUpdate();
            });

            function performOptimisticUpdate() {
                let cartSubtotal = 0;
                let totalItems = 0;
                
                $('.cart-item-row').each(function() {
                    let $row = $(this);
                    let qty = parseInt($row.find('.qty-input').val()) || 0;
                    let price = parseFloat($row.attr('data-price')) || 0;
                    
                    totalItems += qty;
                    let itemSubtotal = qty * price;
                    cartSubtotal += itemSubtotal;
                    
                    $row.find('.item-subtotal-val').html(formatMoney(itemSubtotal));
                });
                
                $('.cart-subtotal-val').html(formatMoney(cartSubtotal));
                // We DO NOT optimistically update cart-total-val directly because coupons/taxes are complex to calculate locally.
                // Instead, we add a gentle pulse animation to let the user know the true total is being recalculated.
                $('.cart-total-val').addClass('animate-pulse text-[#615e57]');

                // Optimistic update for navbar cart badges
                $('.raabiha-cart-count-badge').each(function() {
                    if (totalItems > 0) {
                        $(this).removeClass('hidden').text(totalItems);
                    } else {
                        $(this).addClass('hidden').text(0);
                    }
                });
            }

            function scheduleCartUpdate() {
                if (updateTimeout) {
                    clearTimeout(updateTimeout);
                }

                updateTimeout = setTimeout(function() {
                    let $form = $('.woocommerce-cart-form');
                    if (!$form.length) return;
                    
                    if (abortController) {
                        abortController.abort();
                    }
                    abortController = new AbortController();

                    let formData = new FormData($form[0]);
                    formData.append('update_cart', 'Update cart');
                    let searchParams = new URLSearchParams(formData);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: searchParams,
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        signal: abortController.signal
                    })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newCartForm = doc.querySelector('.woocommerce-cart-form');
                        
                        if (newCartForm) {
                            $form.replaceWith(newCartForm);
                            
                            // Make sure badge syncs with server truth
                            const newBadge = doc.querySelector('.raabiha-cart-count-badge');
                            if (newBadge) {
                                $('.raabiha-cart-count-badge').each(function() {
                                    $(this).replaceWith(newBadge.cloneNode(true));
                                });
                            }
                            
                            $(document.body).trigger('wc_fragment_refresh');
                        }
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            console.error('Update failed', err);
                        }
                    });
                }, 250); // Reduced debounce to 250ms for faster server sync
            }

            // Voucher Modal Handlers
            $(document).on('click', '#open-voucher-modal', function() {
                $('#voucher-modal').removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');
                setTimeout(() => {
                    $('#voucher-modal').removeClass('opacity-0').addClass('opacity-100');
                    $('#voucher-modal .modal-content').removeClass('translate-y-4').addClass('translate-y-0');
                }, 10);
            });
            
            $(document).on('click', '#close-voucher-modal, #voucher-modal', function(e) {
                if(e.target === this || this.id === 'close-voucher-modal') {
                    $('#voucher-modal').removeClass('opacity-100').addClass('opacity-0');
                    $('#voucher-modal .modal-content').removeClass('translate-y-0').addClass('translate-y-4');
                    $('body').removeClass('overflow-hidden');
                    setTimeout(() => {
                        $('#voucher-modal').removeClass('flex').addClass('hidden');
                    }, 300);
                }
            });

            $(document).on('click', '.voucher-item', function() {
                let code = $(this).attr('data-code');
                $('#close-voucher-modal').click();
                
                $('#open-voucher-modal span').text('Menerapkan...');
                
                let $form = $('.woocommerce-cart-form');
                if (!$form.length) return;
                
                let formData = new FormData($form[0]);
                formData.set('coupon_code', code);
                formData.set('apply_coupon', 'Apply coupon');
                formData.delete('update_cart'); 
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: new URLSearchParams(formData),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCartForm = doc.querySelector('.woocommerce-cart-form');
                    if (newCartForm) {
                        $form.replaceWith(newCartForm);
                        const newBadge = doc.querySelector('.raabiha-cart-count-badge');
                        if (newBadge) {
                            $('.raabiha-cart-count-badge').each(function() {
                                $(this).replaceWith(newBadge.cloneNode(true));
                            });
                        }
                        $(document.body).trigger('wc_fragment_refresh');
                    }
                });
            });

            // Remove Coupon AJAX
            $(document).on('click', '.remove-coupon-btn', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let $form = $('.woocommerce-cart-form');
                
                $(this).addClass('opacity-50 pointer-events-none');
                $('.cart-total-val').addClass('animate-pulse text-[#615e57]');
                
                fetch(url)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCartForm = doc.querySelector('.woocommerce-cart-form');
                    if (newCartForm) {
                        $form.replaceWith(newCartForm);
                        $(document.body).trigger('wc_fragment_refresh');
                    }
                });
            });
        });
        </script>
        <style>
        /* Remove arrows for number inputs */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
          -webkit-appearance: none; 
          margin: 0; 
        }
        </style>
        <?php else : ?>
        </div>
        <?php endif; ?>
        
    <!-- Main Grid End -->
</div>

<?php get_footer(); ?>
