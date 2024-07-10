<?php

if ( class_exists('WC_Mix_and_Match') ) {

	add_action( 'ph_ups_before_get_items_from_order', 'ph_add_mix_and_match_compat_filters' ); 
	add_action( 'ph_ups_after_get_items_from_order', 'ph_remove_mix_and_match_compat_filters' );
 }

 function ph_add_mix_and_match_compat_filters() {
 	add_filter( 'woocommerce_order_get_items', array( WC_Mix_and_Match()->order, 'get_order_items' ), 10, 2 ); 
 	add_filter( 'woocommerce_order_item_product', array( WC_Mix_and_Match()->order, 'get_product_from_item' ), 10, 2 );
 }

 function ph_remove_mix_and_match_compat_filters() {
 	remove_filter( 'woocommerce_order_get_items', array( WC_Mix_and_Match()->order, 'get_order_items' ), 10, 2 ); 
 	remove_filter( 'woocommerce_order_item_product', array( WC_Mix_and_Match()->order, 'get_product_from_item' ), 10, 2 );
 }