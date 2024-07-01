<?php

function custom_search_filter($query) {
    if ($query->is_search && !is_admin()) {
        $query->set('post_type', array('product')); // Replace 'product' with the name of your custom post type
    }
}
add_action('pre_get_posts','custom_search_filter');

?>