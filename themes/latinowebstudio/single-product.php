<?php 

get_header();

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

$posts_page_id = get_option( 'page_for_posts' );
$posts_page_title = get_the_title( $posts_page_id );
echo '<section class="bg-light" style="">';
echo '<div class="container">';
echo '<div class="row align-items-center">';

echo '<div class="col-lg-6 col-md-12">';
echo '<h1 class="h5" style="padding:1rem 0rem;line-height:1.5;">' . $posts_page_title . '</h1>';
echo '</div>';
echo '<div class="col-lg-6 col-md-12">';
echo '<span class="" style="padding:1rem 0rem;line-height:1.5;font-size:80%;"><a href="/shop/">Shop</a> » <a href="' . $categoryURL . '">' . $categoryName . '</a> » ' . get_the_title() . '</span>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';

echo '<section class="body" style="padding:50px 0px;">';
echo '<div class="container">';
echo '<div class="row justify-content-center">';

echo '<div class="col-12 order-1">';

if ( have_posts() ) : while ( have_posts() ) : the_post();
// Output product price
// echo '<p>Price: ' . wc_price(get_post_meta(get_the_ID(), '_regular_price', true)) . '</p>';
        
// // Output product short description
// echo '<div class="short-description">' . apply_filters('woocommerce_short_description', get_the_excerpt()) . '</div>';

// // Output product image
// echo '<div class="product-image">' . woocommerce_get_product_thumbnail() . '</div>';

// Output main content
the_content();
endwhile; else:
echo '<p>Sorry, no posts matched your criteria.</p>';
endif;
echo '</div>';
// get_template_part('partials/sidebar');
// <div class="col-lg-3">
// </div>
echo '</div>';
echo '<div class="row justify-content-center pt-5">';
echo '<div class="col-md-6" id="previous">';
echo '<small>Previous</small>';
echo '<h3 class="h5">' . get_previous_post_link() . '</h3>';
echo '</div>';
echo '<div class="col-md-6 text-right" id="next">';
echo '<small>Next</small>';
echo '<h3 class="h5">' . get_next_post_link() . '</h3>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
get_footer(); ?>