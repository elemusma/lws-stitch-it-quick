<?php
echo '<section class="position-relative listings ' . get_field('classes') . '" style="padding:50px 0;' . get_field('style') . '" id="' . get_field('id') . '">';

echo get_template_part('partials/bg-img');


echo get_field('code_block');


echo '<div class="container">';

echo '<div class="row justify-content-center">';

    echo get_template_part('partials/content-block');

echo '</div>';

$featured_posts = get_field('locations');
if( $featured_posts ):
    echo '<div class="row justify-content-center">';
    foreach( $featured_posts as $featured_post ): 
        $permalink = get_permalink( $featured_post->ID );
        $title = get_the_title( $featured_post->ID );
        $price = get_field( 'price', $featured_post->ID );
        $location = get_field( 'location', $featured_post->ID );
        $size = get_field( 'size', $featured_post->ID );
        // echo $featured_post->ID;
        // Get the ID of the featured image
        $featured_image_id = get_post_thumbnail_id($featured_post->ID);
        // echo $featured_image_id;
        // echo 'hello';


            // echo esc_url( $permalink );
            // echo esc_html( $title );

        echo '<div class="col-md-4 col-listings" style="padding:5px;">';
        echo '<a href="' . $permalink . '" class="position-relative bg-white box-shadow d-block" style="text-decoration:none;">';

        echo '<div class="position-relative overflow-h img-hover">';
        // echo '<a href="' . $permalink . '" style="" class="">';

        echo wp_get_attachment_image($featured_image_id,'full','',[
            'class' => 'w-100 d-block img-gallery',
            'style' => 'height:265px;object-fit:cover;'
        ]);

echo '<div class="bg-black w-100 h-100 position-absolute listings-overlay" style="top:0px;left:0px;opacity:.45;transition:all .25s ease-in-out;pointer-events:none;"></div>';
        // echo '</a>';

        echo '<div style="font-size:1.25rem;top:25px;left:25px;background:#275918;padding:10px 20px;" class="position-absolute text-white">For Sale</div>';

        echo '<div style="font-size:1rem;bottom:25px;right:25px;background:#FF7F00;padding:5px 15px;" class="position-absolute text-white">' . esc_html( $price ) . '</div>';

        echo '</div>';

        


        // echo '<div class="position-absolute bg-accent" style="top:50%;left:50%;transform:translate(-50%,-50%);mix-blend-mode:multiply;width:250px;height:40px;"></div>';

        echo '<div class="" style="padding:15px 25px;">';
        echo '<h2 style="font-size:1.5rem;" class="bold">' . $title . '</h2>';

        echo '<p style="margin:0px;" class="bold text-accent">' . esc_html( $location ) . '</p>';

        echo '<div class="d-flex align-items-center">';
        echo '<div style="width:15px;margin-right:5px;">';

        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Free 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2022 Fonticons, Inc. --><path d="M210.6 5.9L62 169.4c-3.9 4.2-6 9.8-6 15.5C56 197.7 66.3 208 79.1 208H104L30.6 281.4c-4.2 4.2-6.6 10-6.6 16C24 309.9 34.1 320 46.6 320H80L5.4 409.5C1.9 413.7 0 419 0 424.5c0 13 10.5 23.5 23.5 23.5H192v32c0 17.7 14.3 32 32 32s32-14.3 32-32V448H424.5c13 0 23.5-10.5 23.5-23.5c0-5.5-1.9-10.8-5.4-15L368 320h33.4c12.5 0 22.6-10.1 22.6-22.6c0-6-2.4-11.8-6.6-16L344 208h24.9c12.7 0 23.1-10.3 23.1-23.1c0-5.7-2.1-11.3-6-15.5L237.4 5.9C234 2.1 229.1 0 224 0s-10 2.1-13.4 5.9z"/></svg>';
        echo '</div>';
        echo '<p style="margin:0px;" class="bold text-black">' . esc_html( $size ) . '</p>';

        echo '</div>';


        echo '</div>';

        echo '</a>'; // end of position-relative

        echo '</div>';

          endforeach;
          echo '</div>';
endif;

echo '</div>';

echo '</section>';

?>