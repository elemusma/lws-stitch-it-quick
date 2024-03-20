<?php
// start of intro
if(have_rows('about_pencil_icons')): while(have_rows('about_pencil_icons')): the_row();


echo '<section class="pt-5 pb-5 position-relative">';

echo wp_get_attachment_image(49,'full','',['class'=>'position-absolute w-100 h-auto pl-4 pr-4','style'=>'top:20%;left:50%;transform:translate(-50%, 0);object-fit:contain;max-width:1200px;']);


echo '<div class="container">';
echo '<div class="row justify-content-around position-relative pt-4 pb-4">';
echo '<div class="position-absolute h-100 bg-accent" style="width:60%;opacity:.44;top:0;right:0;"></div>';
echo '<div class="position-absolute bg-accent-tertiary" style="height:90%;width:60%;opacity:.25;top:5%;left:0;"></div>';
echo '<div class="position-absolute bg-accent-secondary" style="height:90%;width:20px;opacity:.85;top:5%;right:-20px;"></div>';

if(have_rows('links')):
echo '<div class="col-md-3 d-flex flex-wrap align-items-center">';
echo '<div class="w-100">';
while(have_rows('links')): the_row();
$link = get_sub_field('link');
if( $link ): 
$link_url = $link['url'];
$link_title = $link['title'];
$link_target = $link['target'] ? $link['target'] : '_self';
// echo '<a class="bg-accent btn" href="' . esc_url( $link_url ) . '" target="' . esc_attr( $link_target ) . '">' . esc_html( $link_title ) . '</a>';
echo '<a href="' . esc_url( $link_url ) . '" target="' . esc_attr( $link_target ) . '" class="d-block text-black bg-white text-center box-shadow pt-3 pb-3 links-intro" style="box-shadow:inset 1px 1px 5px #e9e9e9;">' . esc_html( $link_title ) . '</a>';
endif;

endwhile;
echo '</div>';
echo '</div>';
endif;

echo '<div class="col-md-6">';

echo get_sub_field('content');


echo '</div>';
echo '</div>';
echo '</div>';
echo '</section>';
endwhile; endif;
// end of intro
?>