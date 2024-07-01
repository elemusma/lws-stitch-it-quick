<?php

function current_year( $atts, $content = null ) {

    $current_year = date("Y");
    
    return $current_year;
    
    // [currentyear]
}

add_shortcode( 'currentyear', 'current_year' );

?>