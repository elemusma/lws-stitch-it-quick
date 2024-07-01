<?php

function my_phone_number() {
    return get_field('phone','options');
    // [phone_number]
    }
add_shortcode('phone_number', 'my_phone_number');

?>