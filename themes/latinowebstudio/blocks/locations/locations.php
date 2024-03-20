<?php
echo '<section class="position-relative locations ' . get_field('classes') . '" style="padding:50px 0;' . get_field('style') . '" id="' . get_field('id') . '">';

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
        $custom_field = get_field( 'field_name', $featured_post->ID );
        // echo $featured_post->ID;
        // Get the ID of the featured image
        $featured_image_id = get_post_thumbnail_id($featured_post->ID);
        // echo $featured_image_id;
        // echo 'hello';


            // echo esc_url( $permalink );
            // echo esc_html( $title );
            //     echo esc_html( $custom_field );

            echo '<div class="col-md-6" style="padding:5px;">';
        echo '<div class="position-relative overflow-h img-hover">';
        echo '<a href="' . $permalink . '" style="" class="d-block">';

        echo wp_get_attachment_image($featured_image_id,'full','',[
            'class' => 'w-100 d-block img-gallery',
            'style' => 'height:350px;object-fit:cover;'
        ]);

        echo '</a>';
        echo '</div>';


        echo '<div class="position-absolute bg-accent" style="top:50%;left:50%;transform:translate(-50%,-50%);mix-blend-mode:multiply;width:250px;height:40px;"></div>';

        echo '<div class="position-absolute text-white" style="top:50%;left:50%;transform:translate(-50%,-50%);">';
        echo '<span style="font-size:1.5rem;" class="bold">' . $title . '</span>';
        echo '</div>';

        echo '</div>';

          endforeach;
          echo '</div>';
endif;

echo '</div>';

echo '</section>';

?>