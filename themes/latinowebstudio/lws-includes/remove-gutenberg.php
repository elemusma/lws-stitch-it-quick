<?php

// removes gutenberg styles from all pages but the blog posts
function smartwp_remove_wp_block_library_css(){

    if(!is_single()) {
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
    }
    } 
    add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

?>