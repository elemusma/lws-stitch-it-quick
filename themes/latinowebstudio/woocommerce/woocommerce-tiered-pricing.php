<?php

// Add custom price calculation function
function custom_tiered_pricing( $price, $product ) {
    // Get the quantity being purchased
    $quantity = isset( $_POST['quantity'] ) ? $_POST['quantity'] : 1;

    // Calculate the discount based on the quantity
    $discount = floor( $quantity / 10 ) * 5; // 5% discount for every 10 items

    // Apply the discount
    $price -= ( $price * $discount / 100 );

    return $price;
}
add_filter( 'woocommerce_product_get_price', 'custom_tiered_pricing', 10, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'custom_tiered_pricing', 10, 2 );

?>