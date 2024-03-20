<?php
echo '<section class="position-relative ' . get_field('classes') . '" style="padding:50px 0px;' . get_field('style') . '" id="' . get_field('id') . '">';

echo get_template_part('partials/bg-img');

echo '<div class="container">';

echo '<div class="row justify-content-center">';

    echo get_template_part('partials/content-block');

// echo '<div class="col-lg-9 text-center">';
// echo '<h2 class="thin">INDUSTRIES SERVED</h2>';
// echo '</div>';
echo '</div>';


if(have_rows('tabs_repeater')):
echo '<div class="row justify-content-center ' . get_sub_field('row_classes') . '" style="padding-top:25px;' . get_sub_field('row_style') . '">';
$tabsCounter = 0;
while(have_rows('tabs_repeater')): the_row();
$tabsCounter++;
    // <div class="col-lg-4 col-md-6"></div>

    $link = get_sub_field('link');

    if( $link ): 
    $link_url = $link['url'];
    $link_title = $link['title'];
    $link_target = $link['target'] ? $link['target'] : '_self';

    if (strstr($link_url, '#')) {
        echo '<span class="col-lg-3 col-md-4 text-center bold position-relative col-tabs bg-accent text-white" style="text-decoration:none;padding:50px 25px;">';
    } else {   
        echo '<a class="col-lg-3 text-center bold position-relative col-tabs bg-accent-secondary text-white" style="text-decoration:none;padding:50px 25px;" href="' . esc_url( $link_url ) . '" target="' . esc_attr( $link_target ) . '">';
    }


    echo esc_html( $link_title );

    if($tabsCounter > 1){
        echo '<div class="position-absolute h-100 tabs-divider" style="
        left:0;
        top:0;
        width:3px;
        transform: translate(-50%, 0px);
        background: white;"></div>';

        echo '<div class="position-absolute tabs-divider" style="
        left:-50px;
        top:50%;
        height:3px;
        width:100px;
        transform: translate(0, -50%);
        background: white;"></div>';
    }

    if (strstr($link_url, '#')) {
        echo '</span>';
    } else {
        echo '</a>';
    }
    // } else {
        // echo '<a class="btn-main outline" href="' . esc_url( $link_url ) . '" target="' . esc_attr( $link_target ) . '">' . esc_html( $link_title ) . '</a>';
    // }
    endif;

endwhile;
echo '</div>';
endif;

// echo '<div class="row justify-content-center">';
// echo get_template_part('partials/content-block');
// echo '</div>';

echo '</div>';

echo '</section>';

?>