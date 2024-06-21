<?php

add_action('woocommerce_before_main_content','add_container_class',9);
add_action('woocommerce_after_main_content','close_container_class',9);

function add_container_class(){
	wp_enqueue_style('woocommerce-css', get_theme_file_uri('/css/sections/woocommerce.css'));
	echo '<section style="padding:50px 0px;">';
	echo '<div class="container">';
	echo '<div class="row justify-content-center">';
    echo '<div class="col-md-12">';
}

function close_container_class(){
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</section>';
}

?>