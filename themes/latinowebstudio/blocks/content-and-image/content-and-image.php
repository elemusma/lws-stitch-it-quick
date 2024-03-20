<?php

wp_enqueue_style('owl.carousel.min', get_theme_file_uri('/owl-carousel/owl.carousel.min.css'));
wp_enqueue_style('owl.theme.default', get_theme_file_uri('/owl-carousel/owl.theme.default.min.css'));

echo '<section class="position-relative content-section ' . get_field('classes') . '" style="padding:50px 0;' . get_field('style') . '">';
echo '<div class="position-absolute" style="top:-50px;" id="' . get_field('id') . '"></div>';
echo get_template_part('partials/bg-img');

echo get_field('code_block');

echo '<div class="container ' . get_field('container_classes') . '" style="' . get_field('container_style') . '">';

$image_side = get_field('image_side');

if($image_side == 'Right') {
    echo '<div class="row justify-content-center ' . get_field('row_classes') . '" style="' . get_field('row_style') . '">';
} else {
    echo '<div class="row flex-row-reverse justify-content-center ' . get_field('row_classes') . '" style="' . get_field('row_style') . '">';
}

echo '<div class="col-lg-6 ' . get_field('column_classes') . '" style="' . get_field('column_style') . '">';
    echo '<div data-aos="' . get_field('content_data_aos_animation') . '">';
        echo get_field('content');
    echo '</div>';
echo '</div>';

$image_or_gallery = get_field('image_or_gallery');

echo '<div class="col-lg-6">';
echo '<div data-aos="' . get_field('image_data_aos_animation') . '" data-aos-delay="300">';

if($image_or_gallery == 'Image') {
    $image = get_field('image_section');
    if($image) {
        echo wp_get_attachment_image($image['id'],'full','',[
            'class'=>'w-100 h-auto ' . get_field('image_section_class'),
            'style'=>'' . get_field('image_section_style')
        ]);
    }

} elseif ($image_or_gallery == 'Gallery') {
    $gallery = get_field('gallery');
if( $gallery ): 
    // echo '<div class="col-lg-6">';
    echo '<div class="listing-carousel owl-carousel owl-theme arrows-middle">';
    // echo '<ul class="slider">';
foreach( $gallery as $image ):

    echo '<div class="h-100 d-flex align-items-center">';
    echo wp_get_attachment_image($image['id'],'full','',[
        'class'=>'w-100 h-auto',
        'style'=>''
    ]);
    echo '</div>';
endforeach;
echo '</div>';
// echo '</div>';
endif;
}
echo '</div>';
echo '</div>';

echo '</div>';

echo '</div>';

echo '</section>';

wp_enqueue_script('jquery-min', get_theme_file_uri('/owl-carousel/jquery.min.js'));
wp_enqueue_script('owl-carousel', get_theme_file_uri('/owl-carousel/owl.carousel.min.js'));
wp_enqueue_script('owl-carousel-custom', get_theme_file_uri('/owl-carousel/owl-carousels.js'));

?>