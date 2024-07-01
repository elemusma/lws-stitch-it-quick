<?php

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );

add_action('woocommerce_single_product_summary', 'custom_page_title', 5);

function custom_page_title() {
    echo '<h1 class="h5">' . get_the_title() . '</h1>';
}

?>