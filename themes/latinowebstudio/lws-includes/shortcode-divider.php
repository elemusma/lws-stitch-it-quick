<?php

function divider_shortcode( $atts, $content = null ) {

$a = shortcode_atts( array(

    'class' => '',

    'style' => ''

), $atts );

return '<div class="divider ' . esc_attr($a['class']) . '" style="' . esc_attr($a['style']) . '"></div>';

// [divider class="" style=""]
}

add_shortcode( 'divider', 'divider_shortcode' );

?>