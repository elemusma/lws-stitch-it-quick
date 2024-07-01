<?php

/**
 * Custom WooCommerce product sorting option: Alphabetical
 */
function custom_woocommerce_catalog_orderby( $sortby ) {
    $sortby['alphabetical'] = 'Sort by Name: A to Z';
    return $sortby;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby' );

/**
 * Custom WooCommerce product sorting query for alphabetical order
 */
function custom_woocommerce_get_catalog_ordering_args( $args ) {
    $orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );

    if ( 'alphabetical' === $orderby_value ) {
        $args['orderby'] = 'title';
        $args['order']   = 'asc';
    }

    return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_args' );

?>