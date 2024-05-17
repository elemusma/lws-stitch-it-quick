<?php

if ( currentUser() && in_array( currentUserGates(), currentUser()->roles ) ) {
    // The current user has the specified role
    wp_nav_menu(array(
        'menu' => 'Gates Menu',
        'menu_class'=>'menu list-unstyled d-lg-flex h-100 align-items-center justify-content-end m-0'
    ));
} else {
    // The current user does not have the specified role
    wp_nav_menu(array(
        'menu' => 'Shop',
        'menu_class'=>'menu list-unstyled d-lg-flex h-100 align-items-center justify-content-end m-0'
    ));
}

?>