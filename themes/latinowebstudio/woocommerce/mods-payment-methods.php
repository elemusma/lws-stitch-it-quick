<?php

function custom_payment_gateways( $available_gateways ) {
    // Get the current user
    $current_user = wp_get_current_user();

    // Check if the user has the 'gates_enterprise' role
    if ( in_array( 'client_gates_enterprises', (array) $current_user->roles ) ) {
        // If the user has the 'gates_enterprise' role, only allow Cash on Delivery
        if ( isset( $available_gateways['cod'] ) ) {
            $available_gateways = array( 'cod' => $available_gateways['cod'] );
        }
    } else {
        // If the user does not have the 'gates_enterprise' role, remove Cash on Delivery
        if ( isset( $available_gateways['cod'] ) ) {
            unset( $available_gateways['cod'] );
        }
    }

    // Return the available gateways
    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'custom_payment_gateways' );

?>