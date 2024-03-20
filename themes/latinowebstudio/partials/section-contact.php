<?php
// start of contact
if(have_rows('contact_section')): while(have_rows('contact_section')): the_row();
echo '<section class="pt-5 pb-5" id="contact">';
echo '<div class="bg-accent text-center pt-5 pb-5 pl-2 pr-2 text-white">';
echo get_sub_field('content_top');
echo '</div>';


echo '<div class="position-relative">';
echo '<div class="position-absolute w-100 h-50" style="top:50%;left:0;transform:translate(0,-50%);background:#ebebeb;"></div>';

echo '<div class="container pt-5 pb-5 position-relative">';


echo '<div class="row justify-content-center">';
echo '<div class="col-md-9">';

echo '<div class="position-relative bg-gray text-center p-5" style="border:10px solid white;box-shadow:inset 0px 0px 5px rgba(0,0,0,.5);">';

echo get_sub_field('content_bottom');

echo '</div>';

echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';

echo '</section>';
endwhile; endif;
// end of contact
?>