<?php
echo '<!DOCTYPE html>';
echo '<html ';
language_attributes();
echo '>';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

echo codeHeader();

wp_head(); 

echo '</head>';
echo '<body '; 
body_class(); 
echo '>';
echo codeBody();


// $role_slug = 'client_gates_enterprises'; // Replace with the role slug you want to check

// if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
    // echo '<header class="position-relative box-shadow bg-accent w-100" style="top:0;left:0;z-index:10;margin-top:32px;">';
    // } else {
echo '<section class="secondary-nav" style="padding:3px 0px;">';
echo '<div class="container">';

echo '<div class="row">';

echo '<div class="col-md-6">';
echo get_template_part('partials/si');
echo '</div>';

echo '<div class="col-md-6">';
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;

echo '<div class="d-flex flex-wrap justify-content-md-end justify-content-center">';

if ( in_array( 'client_gates_enterprises', $user_roles ) ) {
wp_nav_menu(array(
    'menu' => 'Portfolio - Gates',
    'menu_class'=>'menu list-unstyled d-flex justify-content-md-end justify-content-center m-0 text-black menu-secondary-nav-top'
));
} else {
wp_nav_menu(array(
    'menu' => 'Portfolio - Public',
    'menu_class'=>'menu list-unstyled d-flex justify-content-md-end justify-content-center m-0 text-black menu-secondary-nav-top'
));
}

wp_nav_menu(array(
    'menu' => 'Secondary Nav Top',
    'menu_class'=>'menu list-unstyled d-flex justify-content-md-end justify-content-center m-0 text-black menu-secondary-nav-top'
));

// echo '<div class="w-100 login-logout">';


// // Generate the logout URL
// $logout_url = wp_logout_url(home_url());

// echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width:15px;height:13px;fill:black;"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>';

// if(is_user_logged_in()) {
//     // Echo the logout button
//     echo '<a href="' . esc_url($logout_url) . '" class="text-black">Logout</a>';
// } else {
//     echo '<a href="/my-account/" class="text-black">Login</a>';
// }


// echo '</div>';

echo '</div>';

echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';

echo '<div class="blank-space" style=""></div>';
echo '<header class="position-relative box-shadow bg-accent w-100" style="top:0;left:0;z-index:100;padding-top:7.5px;">';
// echo '</header>';
// }

echo '<div class="nav">';
echo '<div class="container">';

echo '<div class="row">';

echo '<div class="col-lg-1 col-md-4 col-3 text-center">';

echo '<div class="d-md-none" style="height:10px;"></div>';

echo '<a href="' . home_url() . '">';
echo '<div style="width:75px;" id="logoMain">';
echo logoSVG();
echo '</div>';
echo '</a>';
echo '</div>';

echo '<div class="col-lg-11 col-6 text-center mobile-hidden">';

echo get_template_part('partials/dealer-menu');

echo '<div class="position-relative text-right d-inline-block d-flex align-items-center justify-content-end nav-icons" style="padding-left:10px;" id="">';

// echo '<div class="nav-icons-inner">';

echo '<a class="openModalBtn position-relative d-inline-block" style="padding-right:18px;" data-modal-id="searchMenu"  id="search-icon">';
echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 117.11 117.1" class="" style="height:13px;width:13px;pointer-events:none;"><defs><style>.cls-1.search{fill:white;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><g id="ehXgD7.tif"><path class="cls-1 search" d="M75.63,68l7.84,7.74a13.28,13.28,0,0,0-7.55,7.47L68.3,75.44C57,83.7,44.91,86.32,31.77,83.07A41,41,0,0,1,7.57,66.28,42.21,42.21,0,1,1,75.63,68ZM11.15,42.21A31.06,31.06,0,1,0,42.26,11.13,31,31,0,0,0,11.15,42.21Z"></path><path class="cls-1 search" d="M117.11,108.2a9,9,0,0,1-5.55,8.1,8.84,8.84,0,0,1-10.1-2c-4.83-4.78-9.62-9.6-14.43-14.41-1.88-1.88-3.8-3.73-5.63-5.66a9.09,9.09,0,0,1,5.79-15.42,8.34,8.34,0,0,1,6.9,2.43q10.34,10.23,20.57,20.56A9,9,0,0,1,117.11,108.2Z"></path><path class="cls-1 search" d="M14.83,41.43A27.43,27.43,0,0,1,41.44,14.82c1.92-.08,3,1.06,2.39,2.48-.45,1-1.33,1.06-2.26,1.1A23.93,23.93,0,0,0,18.41,41.68c-.08,1.73-1,2.65-2.32,2.19S14.77,42.5,14.83,41.43Z"></path></g></g></g></svg>';
echo '</a>';

echo '<div class="login-logout" style="padding-right:15px;">';


// Generate the logout URL
$logout_url = wp_logout_url(home_url());

$user_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width:15px;height:13px;fill:white;transition:none;"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M304 128a80 80 0 1 0 -160 0 80 80 0 1 0 160 0zM96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM49.3 464H398.7c-8.9-63.3-63.3-112-129-112H178.3c-65.7 0-120.1 48.7-129 112zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3z"/></svg>';

if(is_user_logged_in()) {
    // Echo the logout button
    echo '<a href="' . esc_url($logout_url) . '" class="text-white">' . $user_icon . ' Logout</a>';
} else {
    echo '<a href="/my-account/" class="text-white">' . $user_icon . ' Login</a>';
}


echo '</div>';

// echo '<div class="position-relative text-right d-inline-block d-flex align-items-center justify-content-end nav-icons" style="padding-left:10px;" id="">';


// Ensure WooCommerce is active
if ( class_exists( 'WooCommerce' ) ) {
    // Get the number of items in the cart
    $cart_count = WC()->cart->get_cart_contents_count();
    
    if(!is_page(8) && !is_page(9)) {
        // Display the cart count
        echo '<div class="cart-count">';
        echo '<a href="' . wc_get_cart_url() . '" class="text-white">Cart (' . esc_html( $cart_count ) . ') Items</a>';
        echo '</div>';
    }
}

// echo '<div style="padding-left:15px;">';
// echo get_search_form();
// echo '</div>';

// echo '</div>';

// echo '</div>'; // end of nav-icons-inner

echo '</div>'; // end of nav-icons

echo '</div>';

echo '<div class="col-lg-3 col-md-8 col-9 desktop-hidden order-3">';

echo '<div class="d-flex justify-content-end align-items-center">';
echo '<a class="position-relative openModalBtn nav-toggle text-center" style="padding:0px 9px;width:45px;" data-modal-id="searchMenu" title="search menu toggle" title="Enable search bar to search for products">';
echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 117.11 117.1" class="" style="height:20px;margin-bottom:-6px;"><defs><style>.cls-1.search{fill:white;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><g id="ehXgD7.tif"><path class="cls-1 search" d="M75.63,68l7.84,7.74a13.28,13.28,0,0,0-7.55,7.47L68.3,75.44C57,83.7,44.91,86.32,31.77,83.07A41,41,0,0,1,7.57,66.28,42.21,42.21,0,1,1,75.63,68ZM11.15,42.21A31.06,31.06,0,1,0,42.26,11.13,31,31,0,0,0,11.15,42.21Z"></path><path class="cls-1 search" d="M117.11,108.2a9,9,0,0,1-5.55,8.1,8.84,8.84,0,0,1-10.1-2c-4.83-4.78-9.62-9.6-14.43-14.41-1.88-1.88-3.8-3.73-5.63-5.66a9.09,9.09,0,0,1,5.79-15.42,8.34,8.34,0,0,1,6.9,2.43q10.34,10.23,20.57,20.56A9,9,0,0,1,117.11,108.2Z"></path><path class="cls-1 search" d="M14.83,41.43A27.43,27.43,0,0,1,41.44,14.82c1.92-.08,3,1.06,2.39,2.48-.45,1-1.33,1.06-2.26,1.1A23.93,23.93,0,0,0,18.41,41.68c-.08,1.73-1,2.65-2.32,2.19S14.77,42.5,14.83,41.43Z"></path></g></g></g></svg>';
echo '</a>';

wp_nav_menu(array(
    'menu' => 'Shopping Cart AJAX',
    'menu_class'=>'menu list-unstyled mb-0 d-flex justify-content-end m-0'
));

echo '</div>';


echo '<div>';
echo '<a id="mobileMenuToggle" class="openModalBtn nav-toggle" data-modal-id="mobileMenu" title="mobile menu nav toggle">';
echo '<div>';
echo '<div class="line-1 bg-accent-secondary"></div>';
echo '<div class="line-2 bg-accent-secondary"></div>';
echo '<div class="line-3 bg-accent-secondary"></div>';
echo '</div>';
echo '</a>';
echo '</div>';

// echo '<div class="text-right" style="">';
// echo '<a href="/customer-provided-apparel/" class="text-accent-secondary">Customer Provided Apparel</a>';

// // echo get_search_form();

// echo '</div>';

echo '</div>';



echo '</div>';
echo '</div>';
echo '</div>';

echo '</header>';
?>