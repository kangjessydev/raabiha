<?php
/**
 * WooCommerce Custom Meta Boxes for Raabiha Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add custom meta boxes to the Product post type
 */
function raabiha_add_product_meta_boxes() {
    add_meta_box(
        'raabiha_product_details',
        __( 'Raabiha Custom Details (Accordions)', 'raabiha' ),
        'raabiha_product_details_meta_box_html',
        'product',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes_product', 'raabiha_add_product_meta_boxes' );

/**
 * HTML for the custom meta box
 */
function raabiha_product_details_meta_box_html( $post ) {
    $journal = get_post_meta( $post->ID, '_raabiha_community_journal', true );
    $shipping = get_post_meta( $post->ID, '_raabiha_shipping_care', true );

    wp_nonce_field( 'raabiha_save_product_meta', 'raabiha_product_meta_nonce' );
    ?>
    <p>
        <label for="raabiha_community_journal"><strong><?php _e( '03. Community Journal', 'raabiha' ); ?></strong></label><br />
        <textarea id="raabiha_community_journal" name="raabiha_community_journal" rows="4" style="width:100%;"><?php echo esc_textarea( $journal ); ?></textarea>
        <small><?php _e( 'Teks deskriptif jurnal atau cerita di balik koleksi produk ini.', 'raabiha' ); ?></small>
    </p>
    <hr />
    <p>
        <label for="raabiha_shipping_care"><strong><?php _e( '04. Shipping & Care', 'raabiha' ); ?></strong></label><br />
        <textarea id="raabiha_shipping_care" name="raabiha_shipping_care" rows="4" style="width:100%;"><?php echo esc_textarea( $shipping ); ?></textarea>
        <small><?php _e( 'Instruksi pengiriman dan perawatan khusus produk ini. Kosongkan jika ingin menggunakan bawaan global.', 'raabiha' ); ?></small>
    </p>
    <?php
}

/**
 * Save custom meta box data
 */
function raabiha_save_product_meta( $post_id ) {
    if ( ! isset( $_POST['raabiha_product_meta_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['raabiha_product_meta_nonce'], 'raabiha_save_product_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['raabiha_community_journal'] ) ) {
        update_post_meta( $post_id, '_raabiha_community_journal', wp_kses_post( wp_unslash( $_POST['raabiha_community_journal'] ) ) );
    }
    if ( isset( $_POST['raabiha_shipping_care'] ) ) {
        update_post_meta( $post_id, '_raabiha_shipping_care', wp_kses_post( wp_unslash( $_POST['raabiha_shipping_care'] ) ) );
    }
}
add_action( 'save_post_product', 'raabiha_save_product_meta' );
