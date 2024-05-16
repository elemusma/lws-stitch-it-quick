<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


get_header( 'shop' );

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
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		if($categoryName == 'Gates' ) { // shows Gates product to Gates customers
			if(currentUser() && in_array( currentUserGates(), currentUser()->roles )) {
		do_action( 'woocommerce_before_main_content' );

	while ( have_posts() ) :
			the_post();
			
			wc_get_template_part( 'content', 'single-product' );
		
	endwhile; // end of the loop.
		
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	} else { // if Gates product and not logged in as Gates
		echo get_template_part('partials/dealer-portal-login');
	}
	} else { // shows products for the public
		do_action( 'woocommerce_before_main_content' );

	while ( have_posts() ) :
			the_post();
			
			wc_get_template_part( 'content', 'single-product' );
		
	endwhile; // end of the loop.
		
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	}
	?>

	<?php
		/**
		 * woocommerce_sidebar hook.
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>

<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
