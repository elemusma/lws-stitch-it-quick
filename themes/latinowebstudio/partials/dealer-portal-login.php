<?php

// Get the product categories
$categories = get_the_terms(get_the_ID(), 'product_cat');
$categoryName = "";
$categoryURL = "";

if ($categories && !is_wp_error($categories)) {
    // Get the first category
    $main_category = reset($categories);

    // Get the main category name
    $main_category_name = $main_category->name;

    // Get the main category URL
    $categoryURL = get_term_link($main_category);

    // Display the main category name with link
    // echo '<div class="main-category"><a href="' . esc_url($categoryURL) . '">' . $main_category_name . '</a></div>';
    $categoryName = $main_category_name;
}

if(is_product() || is_page()) {
    $main_title = get_the_title();
// } elseif (is_page()) {
//     $main_title = get_the_title();
} else {
    $main_title = single_cat_title('', false);
}

echo '<section class="body" style="padding:50px 0px;">';
echo '<div class="container">';
echo '<div class="row justify-content-center">';

echo '<div class="col-12 order-1">';

echo '<h1>Login to continue</h1>';
echo '<p>To view the ' . $main_title . ', you need to be logged in as a ' . $categoryName . ' customer.</p>';

echo do_shortcode('[woocommerce_my_account]');
// echo '<a href="/my-account/">Login here</a>';

echo '</div>';

echo '</div>';

echo '</div>';
echo '</section>';

?>