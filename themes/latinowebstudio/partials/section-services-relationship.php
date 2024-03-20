<?php
wp_enqueue_style('services', get_theme_file_uri('/css/sections/services.css'));
$relationship = get_field('pages');

if( $relationship ):
    echo '<section class="pt-5 position-relative bg-accent-quaternary" style="">';
    echo '<div class="container-fluid">';
    echo '<div class="row justify-content-center">';

    echo '<div class="col-12 text-center pb-4">';
    echo '<h2 class="text-accent">SERVICES</h2>';
    echo '</div>';


        foreach( $relationship as $post ): 
            // Setup this post for WP functions (variable must be named $post).
            setup_postdata($post);
            echo '<a href="' . get_the_permalink() . '" class="col-md-4 text-center pl-1 pr-1 pt-2 col-services">';
            echo '<div class="position-relative overflow-h" style="padding-top:75px;padding-bottom:75px;">';
            the_post_thumbnail('full',array('class'=>'w-100 h-100 position-absolute','style'=>'top:0;left:0;object-fit:cover;'));
            echo '<div class="position-absolute w-100 h-100 bg-black col-services-overlay" style="top:0;left:0;opacity:.45;"></div>';
            
            echo '<div class="position-relative pl-4 pr-4 d-inline-block">';
            echo '<div class="position-absolute w-100 h-100 bg-accent-tertiary" style="top:0;left:0;mix-blend-mode:multiply;"></div>';
            echo '<h6 class="position-relative mb-0 text-white pt-2 pb-2">' . get_the_title() . '</h6>';
            echo '</div>';
            
            echo '</div>';
            echo '</a>';
        endforeach;
        
    echo '</div>';
    echo '</div>';
    echo '</section>';
    // Reset the global post object so that the rest of the page works correctly.
    wp_reset_postdata(); 
endif;
?>