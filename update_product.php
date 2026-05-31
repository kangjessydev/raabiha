<?php
require_once( 'wp-load.php' );
$product_id = 168;

// Set product type to variable
wp_set_object_terms( $product_id, 'variable', 'product_type' );

// Define attributes (local attributes for simplicity)
$attributes = array(
    'size' => array(
        'name' => 'Size',
        'value' => 'M | L',
        'position' => 0,
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 0
    ),
    'color' => array(
        'name' => 'Color',
        'value' => 'Charcoal | Sand | Green',
        'position' => 1,
        'is_visible' => 1,
        'is_variation' => 1,
        'is_taxonomy' => 0
    )
);
update_post_meta( $product_id, '_product_attributes', $attributes );

// Create variations
$variations_data = [
    ['size' => 'M', 'color' => 'Charcoal'],
    ['size' => 'L', 'color' => 'Charcoal'],
    ['size' => 'M', 'color' => 'Sand'],
    ['size' => 'M', 'color' => 'Green'],
];

foreach ($variations_data as $data) {
    $variation = new WC_Product_Variation();
    $variation->set_parent_id( $product_id );
    $variation->set_attributes( $data );
    $variation->set_regular_price( '950000' );
    $variation->set_status( 'publish' );
    $variation->save();
}

echo "Updated product 168 to variable with variations.\n";
