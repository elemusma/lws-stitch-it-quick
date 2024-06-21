<?php

// Function to display "Starting from" before the lowest price if there's a price range
function custom_display_lowest_price( $price, $product ) {
    $price = "";
    if ( $product->is_type( 'variable' ) ) {
        $available_variations = $product->get_available_variations();
        
        if ( $available_variations ) {
            $min_price = $product->get_variation_price( 'min', true );
            $max_price = $product->get_variation_price( 'max', true );
    
            if ( $min_price !== $max_price ) {
                // Display price range
                $price .= '<span class="woocommerce-Price-amount amount">';
                // $price .= '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span>';
                // $price .= '<span class="from">' . wc_price( $min_price );
                // $price .= '</span> - ';
                $price .= '<span class="to">' . wc_price( $max_price ) . '</span>';
                $price .= '</span>';
            } else {
                // Display single price if min and max are the same
                $price .= '<span class="woocommerce-Price-amount amount">';
                // $price .= '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span>';
                $price .= wc_price( $min_price );
                $price .= '</span>';
            }
        }
    }
    
    return $price;
}
add_filter( 'woocommerce_variable_sale_price_html', 'custom_display_lowest_price', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'custom_display_lowest_price', 10, 2 );

?>