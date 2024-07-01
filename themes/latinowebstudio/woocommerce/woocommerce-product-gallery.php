<?php 

remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20); // Removes the main product image

add_action('woocommerce_before_single_product_summary', 'addCustomProductGallerySlick', 20);

function addCustomProductGallerySlick() {
    // Enqueue external styles
    // wp_enqueue_style('slick-css', 'https://rawgit.com/kenwheeler/slick/master/slick/slick.css');
    // wp_enqueue_style('evil-icons-css', 'https://cdn.jsdelivr.net/evil-icons/1.9.0/evil-icons.min.css');
    // wp_enqueue_style('slick-carousel-css', get_theme_file_uri('/slick-carousel/slick.css'));
    // wp_enqueue_style('lightbox-css', get_theme_file_uri('/lightbox/lightbox.min.css'));

    echo '<link rel="stylesheet" href="https://rawgit.com/kenwheeler/slick/master/slick/slick.css">';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/evil-icons/1.9.0/evil-icons.min.css">';
    echo '<script src="https://cdn.jsdelivr.net/evil-icons/1.9.0/evil-icons.min.js"></script>';

    
    echo '<link rel="stylesheet" href="/wp-content/themes/latinowebstudio/slick-carousel/slick.css">';
    wp_enqueue_style('lightbox-css', get_theme_file_uri('/lightbox/lightbox.min.css'));

    global $product;
    $attachment_ids = $product->get_gallery_image_ids();

    if ($attachment_ids && $product->get_image_id()) {
        echo '<div class="slick-slider-products" style="float:left;width:48%;">';
        echo '<div class="layout">';
        echo '<ul class="slider">';
        foreach ($attachment_ids as $attachment_id) {
            echo '<li>';
            // Get the URL of the full-sized image
            $image_src = wp_get_attachment_image_src($attachment_id, 'full'); // 'full' retrieves the full-sized image
            if ($image_src) {
                echo '<a href="' . wp_get_attachment_image_url($attachment_id, 'full') . '" data-lightbox="image-set">';
                echo wp_get_attachment_image($attachment_id, 'full', '', array(
                    'class' => 'w-100 h-auto skip-lazy',
                    'style' => 'object-fit:contain;max-height:350px;',
                ));
                echo '</a>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
    }

    // echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>';
    // echo '<script src="https://rawgit.com/kenwheeler/slick/master/slick/slick.min.js"></script>';
    // echo '<script src="/wp-content/themes/latinowebstudio/slick-carousel/slick.js"></script>';
    wp_enqueue_script('slick-cloudflare-jquery-min','//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js');
    wp_enqueue_script('rawgit-slick','//rawgit.com/kenwheeler/slick/master/slick/slick.min.js');
    wp_enqueue_script('rawgit-slick',get_theme_file_uri('/slick-carousel/slick.js'));
    // wp_enqueue_script('jquery'); // Enqueue WordPress's native jQuery
    wp_enqueue_script('lightbox-min-js', get_theme_file_uri('/lightbox/lightbox.min.js'));
    wp_enqueue_script('lightbox-js', get_theme_file_uri('/lightbox/lightbox.js'));
    wp_enqueue_script('products-js', get_theme_file_uri('/js/products.js'));

    // Enqueue scripts with dependencies
    // wp_enqueue_script('jquery'); // Enqueue WordPress's native jQuery
    // wp_enqueue_script('evil-icons-js', 'https://cdn.jsdelivr.net/evil-icons/1.9.0/evil-icons.min.js', array('jquery'), null, true);
    // wp_enqueue_script('slick-js', 'https://rawgit.com/kenwheeler/slick/master/slick/slick.min.js', array('jquery'), null, true);
    // wp_enqueue_script('slick-carousel-js', get_theme_file_uri('/slick-carousel/slick.js'), array('jquery'), null, true);
    // wp_enqueue_script('lightbox-min-js', get_theme_file_uri('/lightbox/lightbox.min.js'), array('jquery'), null, true);
    // wp_enqueue_script('products-js', get_theme_file_uri('/js/products.js'), array('jquery'), null, true);
}


?>