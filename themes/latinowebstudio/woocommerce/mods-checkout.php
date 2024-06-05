<?php

wp_enqueue_style('checkout-custom', get_theme_file_uri(('/css/sections/checkout.css')));

add_action('woocommerce_checkout_before_customer_details','custom_before_customer_details', 1);

function custom_before_customer_details() {
    echo '<div class="checkout-form-inner d-lg-flex">';
    echo '<div class="customer-details" style="max-width:55%;flex:0 0 100%;">';
}

add_action('woocommerce_checkout_after_customer_details','custom_after_customer_details', 10);
function custom_after_customer_details() {
    echo '</div>';
}

add_action('woocommerce_checkout_before_order_review_heading','custom_before_order_review_heading', 10);
function custom_before_order_review_heading() {
    echo '<div class="lws order-review-details">';
}

add_action('woocommerce_checkout_after_order_review','custom_checkout_after_order_review', 10);
function custom_checkout_after_order_review() {
    echo '</div>';
    echo '</div>';
}

?>