<?php 

wp_enqueue_style('shop-loop-items', get_theme_file_uri('/css/sections/shop-loop-items.css'));

add_action('woocommerce_before_shop_loop_item','custom_before', 5);
add_action('woocommerce_after_shop_loop_item','custom_after', 15);

function custom_before() {
    echo '<div>';
}

function custom_after() {
    echo '</div>';
}

?>