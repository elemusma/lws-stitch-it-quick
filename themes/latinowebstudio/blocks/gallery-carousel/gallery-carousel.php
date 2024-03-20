<?php
echo '<section class="position-relative content-section ' . get_field('classes') . '" style="padding:50px 0;' . get_field('style') . '" id="' . get_field('id') . '">';

echo get_template_part('partials/bg-img');


echo get_field('code_block');


echo '<div class="container-fluid">';

echo '<div class="row justify-content-center">';

    echo get_template_part('partials/content-block');

echo '</div>';

$gallery = get_field('gallery');
if( $gallery ): 
    // echo '<div class="container">';
    echo '<div class="gallery-carousel owl-carousel owl-theme arrows-middle">';
foreach( $gallery as $image ):
// echo '<div class="" style="padding:5px;">';
echo '<div class="position-relative img-hover overflow-h">';
echo '<a href="' . wp_get_attachment_image_url($image['id'], 'full') . '" data-lightbox="image-set" style="" class="d-block">';

echo wp_get_attachment_image($image['id'], 'full','',[
    'class'=>'w-100 d-block',
    'style'=>'height:250px;object-fit:cover;'
] );
echo '</a>';
echo '</div>';
// echo '</div>';
endforeach; 
echo '</div>';
// echo '</div>';
endif;

echo '</div>';

echo '</section>';

?>