<?php get_header();


// global $post;
if ( ! post_password_required( $post ) ) {

if ( have_posts() ) : while ( have_posts() ) : the_post();
the_content();
endwhile; else:
echo '<p>Sorry, no posts matched your criteria.</p>';
endif;

} else {
    // we will show password form here
    
    echo '<section class="pt-5 pb-5">';
    echo '<div class="container">';
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo get_the_password_form();
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</section>';
       
}

get_footer(); ?>