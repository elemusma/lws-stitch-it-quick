<?php

function spacer_shortcode( $atts, $content = null ) {

    $a = shortcode_atts( array(
    
        'class' => '',
    
        'style' => ''
    
    ), $atts );
    
    return '<div class="spacer ' . esc_attr($a['class']) . '" style="' . esc_attr($a['style']) . '"></div>';
    
    // [spacer class="" style=""]
    }
    
    add_shortcode( 'spacer', 'spacer_shortcode' );

?>