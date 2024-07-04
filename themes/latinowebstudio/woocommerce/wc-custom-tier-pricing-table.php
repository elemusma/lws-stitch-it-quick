<?php

add_action('woocommerce_single_product_summary','desktop',15);
// add_action('woocommerce_after_single_product_summary','desktop',15);

function desktop() {
    // echo '<div class="d-md-block d-none">';
    product_tier_pricing_table();
    // echo '</div>';
}
function mobile() {
    echo '<div class="d-md-none d-block">';
    product_tier_pricing_table();
    echo '</div>';
}


function product_tier_pricing_table() {

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

}

?>