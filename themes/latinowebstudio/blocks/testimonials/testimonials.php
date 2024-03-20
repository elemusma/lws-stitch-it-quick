<?php
echo '<section class="position-relative content-section ' . get_field('classes') . '" style="padding:50px 0;' . get_field('style') . '" id="' . get_field('id') . '">';

echo get_template_part('partials/bg-img');


echo get_field('code_block');


echo '<div class="container">';

echo '<div class="row justify-content-center">';

    echo get_template_part('partials/content-block');

    echo '<h2 class="bold text-white" style="margin:0px;padding-bottom:50px;">TESTIMONIALS</h2>';

echo '</div>';

if(have_rows('testimonials_repeater')):
//     echo '<div class="testimonial-carousel owl-carousel owl-theme">';
    echo '<div class="row">';
    while(have_rows('testimonials_repeater')): the_row();
    echo '<div class="col-lg-4 col-12 position-relative" style="color:#acacac;padding-left:3.5rem;">';

    echo '<div class="position-absolute" style="top:0;left:0;">';
    echo '<img src="https://insideoutcreative.io/wp-content/uploads/2022/11/Quotes.png" alt="" width="25px" height="25px">';
    echo '</div>';

    echo get_sub_field('content');
    echo '<div class="" style="padding-left:1.5rem;">';
    echo '<span class="bold" style="color:#cccccc;">' . get_sub_field('name') . '</span><br>';
    echo '<span class="">' . get_sub_field('job_title') . '</span>';
    echo '</div>';
    echo '</div>';

    endwhile;
    echo '</div>';
    endif;

echo '</div>';

echo '</section>';

?>