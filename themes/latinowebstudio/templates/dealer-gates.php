<?php

/**
 * Template Name: Dealer - Gates
 */

 get_header();

    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    // print_r($user_roles) . ' $user_roles';

    // echo '<p>User Roles: ' . implode(', ', $user_roles) . '</p>';

    if ( in_array( 'client_gates_enterprises', $user_roles ) ) {
        if ( have_posts() ) : while ( have_posts() ) : the_post();
        the_content();
        endwhile; else:
        echo '<p>Sorry, no posts matched your criteria.</p>';
        endif;
    } else {
        echo get_template_part('partials/dealer-portal-login');
    }

    // if(in_array( currentUserGates(), currentUser()->roles )) {
    //     echo 'hello111';
    // } else {
    //     echo 'not gates';
    // }

 get_footer();

?>