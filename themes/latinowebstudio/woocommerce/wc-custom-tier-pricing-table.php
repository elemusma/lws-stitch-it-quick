<?php

add_action('woocommerce_single_product_summary','conditional_desktop',15);
// add_action('woocommerce_after_single_product_summary','desktop',15);

function conditional_desktop() {
    // Define the category slug or ID you want to exclude
    $excluded_category = 'flash-fashion'; // Replace 'your-category-slug' with the actual slug or ID of the category

    // Get the current product
    global $product;

    // Check if the product belongs to the excluded category
    if ( ! has_term( $excluded_category, 'product_cat', $product->get_id() ) ) {
        // If the product is not in the excluded category, run the function
        product_tier_pricing_table();
    }
}

function mobile() {
    echo '<div class="d-md-none d-block">';
    product_tier_pricing_table();
    echo '</div>';
}


function product_tier_pricing_table() {
echo '<div style="padding-bottom:15px;">';
echo '<b>Bulk Discount Pricing</b>';
echo '<div class="table-wrapper">';
echo '<table class="fl-table">';
echo '<thead>';
echo '<tr>';
echo '<th>11-20 Products</th>';
echo '<th>21-30 Products</th>';
echo '<th>31+ Products</th>';
// echo '<th>40-49 Products</th>';
// echo '<th>50+ Products</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
echo '<tr>';
echo '<td>10%</td>';
echo '<td>15%</td>';
echo '<td>20%</td>';
// echo '<td>40%</td>';
// echo '<td>50%</td>';
echo '</tr>';
echo '</tbody>';
echo '</table>';
echo '</div>';
echo '</div>';

}

?>