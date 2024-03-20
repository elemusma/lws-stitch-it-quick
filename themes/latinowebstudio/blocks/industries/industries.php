<?php
echo '<section class="' . get_field('classes') . '" style="' . get_field('style') . '" id="' . get_field('id') . '">';
echo '<div class="container-fluid">';
echo '<div class="row">';

if(have_rows('columns_repeater')): while(have_rows('columns_repeater')): the_row();
echo '<div class="' . get_sub_field('column_classes') . '" data-aos="fade-up" style="padding:0;">';
echo '<div class="bg-accent-green text-white text-center">';
echo '<h3 class="" style="' . get_sub_field('column_style') . 'margin:0;padding: 15px 0px;">' . get_sub_field('title') . '</h3>';
echo '</div>';
echo '<div class="overflow-h" style="height: 250px;">';

$img = get_sub_field('image');
echo wp_get_attachment_image($img['id'],'full','',[
    'class'=>'w-100 img-intro img-hover',
    'style'=>'height:250px;object-fit:cover;'
]);

echo '</div>';
echo '</div>';
endwhile; endif;



echo '</div>';
echo '</div>';
echo '</section>';

?>