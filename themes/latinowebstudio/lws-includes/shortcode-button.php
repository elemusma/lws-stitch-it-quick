<?php

function btn_shortcode( $atts, $content = null ) {

$a = shortcode_atts( array(

'class' => '',

'href' => '',

'style' => '',

'target' => '',

'id' => '',

'aria-label' => ''

), $atts );

$id = esc_attr($a['id']);

// Check if the ID contains the word "modal"
if (strpos($id, 'modal') !== false) {
    return '<span class="btn-main ' . esc_attr($a['class']) . '" aria-label="' . esc_attr($a['aria-label']) . '" style="' . esc_attr($a['style']) . '" target="' . esc_attr($a['target']) . '" id="' . esc_attr($a['id']) . '">' . $content . '</span>';
} else {
    return '<a class="btn-main ' . esc_attr($a['class']) . '" href="' . esc_attr($a['href']) . '" style="' . esc_attr($a['style']) . '" target="' . esc_attr($a['target']) . '" id="' . esc_attr($a['id']) . '">' . $content . '</a>';
}

// [button href="#" class="btn-main" style=""]Learn More[/button]

}

add_shortcode( 'button', 'btn_shortcode' );

?>