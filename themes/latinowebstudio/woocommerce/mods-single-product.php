<?php

add_action('woocommerce_single_product_summary','testing_this', 5);
add_action('woocommerce_single_product_summary','testing_this_close', 65);

function testing_this() {
    echo '<div class="" style="">';
}
function testing_this_close() {
    echo '</div>';
}

?>