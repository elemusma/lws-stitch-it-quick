<?php get_header();

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

get_footer(); 
?>