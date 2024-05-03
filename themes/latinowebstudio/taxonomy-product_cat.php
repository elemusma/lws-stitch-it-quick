<?php 

/**
 * The Template for displaying products in a product category. Simply includes the archive template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/taxonomy-product-cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     4.7.0
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

echo '<section id="" class="content-area" style="padding:50px 0px;">';
    echo '<div id="" class="container">';
    echo '<div id="" class="row">';
    echo '<div id="" class="col-12">';

        
        if (have_posts()) :

            echo '<header class="page-header">';
                // do_action('woocommerce_before_main_content');

                echo '<h1 class="">';
                woocommerce_page_title();
                echo '</h1>';
                do_action('woocommerce_taxonomy_archive_description');
            echo '</header>'; // <!-- .page-header -->


            /**
             * Hook: woocommerce_archive_description.
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action('woocommerce_archive_description');
            
            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
                while (have_posts()) :
                    the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action('woocommerce_shop_loop');

                    wc_get_template_part('content', 'product');
                endwhile;
            }

            woocommerce_product_loop_end();

            /**
             * Hook: woocommerce_after_shop_loop.
             *
             * @hooked woocommerce_pagination - 10
             */
            do_action('woocommerce_after_shop_loop');

        else :
            /**
             * Hook: woocommerce_no_products_found.
             *
             * @hooked wc_no_products_found - 10
             */
            do_action('woocommerce_no_products_found');
        endif;

    echo '</div>'; //<!-- col -->
    echo '</div>'; //<!-- row -->
    echo '</div>'; //<!-- container -->
echo '</section>'; // <!-- #primary -->


get_footer();