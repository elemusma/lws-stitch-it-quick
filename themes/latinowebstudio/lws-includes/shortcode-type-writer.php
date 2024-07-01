<?php

function type_writer_shortcode( $atts ) {
	wp_enqueue_script('typewriter-js',get_theme_file_uri('/js/typewriter.js'));
	wp_enqueue_style('typewriter-css',get_theme_file_uri('/css/sections/typewriter.css'));
    // Extract shortcode attributes
    $atts = shortcode_atts( array(
        'text' => '',
        'wait' => '1000',
        'words' => '',
		'style'=>'',
		'class'=>''
    ), $atts );

    // Sanitize attribute values
    $text = sanitize_text_field( $atts['text'] );
    $wait = intval( $atts['wait'] );
    $word_sets = array_map( 'trim', explode( ',', $atts['words'] ) );

    // Output HTML
    ob_start();
	echo '<div class="' . esc_attr($atts['class']) . '" style="' . esc_attr($atts['style']) . '">';
    ?>
	<span><?php echo esc_html( $text ); ?></span><span class="txt-type" style="" data-wait="<?php echo esc_attr( $wait ); ?>" data-words='<?php echo esc_attr( json_encode( $word_sets ) ); ?>'></span>
	</div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'type_writer', 'type_writer_shortcode' );

?>