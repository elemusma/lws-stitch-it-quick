<?php
if(have_rows('intro_content')): while(have_rows('intro_content')): the_row();
echo '<section class="pt-5 pb-5">';
echo '<div class="container">';
echo '<div class="row justify-content-center">';

echo '<div class="col-lg-6 col-md-9 text-center">';

echo get_sub_field('content');
$link = get_sub_field('link');

if( $link ): 
    $link_url = $link['url'];
    $link_title = $link['title'];
    $link_target = $link['target'] ? $link['target'] : '_self';
    
    if($link_url == '#'){
        echo '<span class="bg-accent-outline btn btn-lg btn-learn-more open-modal" id="btn-learn-more">' . esc_html( $link_title ) . '</span>';
    
    echo '<div class="modal-content btn-learn-more position-fixed w-100 h-100 z-3" style="opacity:0;">';
    echo '<div class="bg-overlay"></div>';
    echo '<div class="bg-content">';
    echo '<div class="bg-content-inner">';
    echo '<div class="close" id="">X</div>';
    echo '<div>';
    echo get_sub_field('popup');
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    
    } else {
        echo '<a class="bg-accent-outline btn btn-lg" href="' . esc_url( $link_url ) . '" target="' . esc_attr( $link_target ) . '">' . esc_html( $link_title ) . '</a>';
    }
    endif;
echo '</div>';

echo '</div>';
echo '</div>';
echo '</section>';
endwhile; endif;
?>