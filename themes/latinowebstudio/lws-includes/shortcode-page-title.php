<?php

function my_page_title_shortcode() {
    return get_the_title();
    // [page_title]
    }
add_shortcode('page_title', 'my_page_title_shortcode');

?>