<?php

// // Example function to synchronize inventory between two specific products
// function sync_inventory_between_products($product_id_1, $product_id_2) {
//     // Get current stock levels
//     $stock_product_1 = get_post_meta($product_id_1, '_stock', true);
//     $stock_product_2 = get_post_meta($product_id_2, '_stock', true);

//     // Synchronize inventory (example: update product 2 with product 1's stock)
//     update_post_meta($product_id_2, '_stock', $stock_product_1);
// }

// // Hook into order completion to trigger synchronization (example)
// add_action('woocommerce_thankyou', 'custom_sync_on_order_completion');

// // Hook into product stock update to trigger synchronization
// add_action('woocommerce_update_product_stock', 'custom_sync_on_product_stock_update', 10, 1);

// function custom_sync_on_product_stock_update($product_id) {
//     // Example: define your product IDs here
//     $product_id_1 = 1245; // Replace with actual product IDs
//     $product_id_2 = 62; // Replace with actual product IDs

//     // Check if the product IDs match those being synchronized
//     if ($product_id == $product_id_1 || $product_id == $product_id_2) {
//         sync_inventory_between_products($product_id_1, $product_id_2);
//     }
// }

// // Function to synchronize inventory after order completion
// function custom_sync_on_order_completion($order_id) {
//     $order = wc_get_order($order_id);

//     // Example: synchronize inventory between specific products after order completion
//     $product_id_1 = 1245; // Replace with actual product IDs
//     $product_id_2 = 62; // Replace with actual product IDs
//     sync_inventory_between_products($product_id_1, $product_id_2);
// }


// add_action('woocommerce_product_set_stock', 'custom_inventory_update_notification', 10, 2);

// function custom_inventory_update_notification($product_id, $new_stock) {
//     // Perform actions when inventory is updated
//     $product = wc_get_product($product_id);
//     $product_name = $product->get_name();
//     $old_stock = $product->get_stock_quantity();

//     // Example notification or log message
//     $message = "Inventory of product '{$product_name}' updated from {$old_stock} to {$new_stock}.";
//     error_log($message); // Example: Log the inventory update
// }








// Function to synchronize inventory between two products based on order completion
function sync_inventory_on_order_completion($order_id) {
    $order = wc_get_order($order_id);

    // Define product IDs to synchronize
    $product_id_1 = 1245; // Replace with actual product IDs
    $product_id_2 = 62; // Replace with actual product IDs

    // Get quantities sold for each product in the order
    $items = $order->get_items();
    foreach ($items as $item) {
        $product_id = $item->get_product_id();
        $quantity = $item->get_quantity();

        // Check if product is a variation
        $product = wc_get_product($product_id);
        if ($product->is_type('variation')) {
            // Get the parent product ID
            $parent_product_id = $product->get_parent_id();

            // Update inventory based on variation sold
            if ($parent_product_id == $product_id_1 || $parent_product_id == $product_id_2) {
                // Update the specific variation's stock
                $variation_id = $product_id;
                $variation = wc_get_product($variation_id);
                $current_stock = $variation->get_stock_quantity();
                $variation->set_stock_quantity($current_stock - $quantity);
                $variation->save();
            }
        } else {
            // Update inventory for simple products (if needed)
            if ($product_id == $product_id_1) {
                $current_stock_1 = get_post_meta($product_id_1, '_stock', true);
                update_post_meta($product_id_2, '_stock', $current_stock_1);
            } elseif ($product_id == $product_id_2) {
                $current_stock_2 = get_post_meta($product_id_2, '_stock', true);
                update_post_meta($product_id_1, '_stock', $current_stock_2);
            }
        }
    }
}

// Hook into order completion to trigger synchronization
add_action('woocommerce_thankyou', 'sync_inventory_on_order_completion', 10, 1);















































// code below works properly upon order completion

// // Function to synchronize inventory between two products based on order completion
// function sync_inventory_on_order_completion($order_id) {
//     $order = wc_get_order($order_id);

//     // Define product IDs to synchronize
//     $product_id_1 = 1245; // Replace with actual product IDs
//     $product_id_2 = 62; // Replace with actual product IDs

//     // Get quantities sold for each product in the order
//     $items = $order->get_items();
//     foreach ($items as $item) {
//         $product_id = $item->get_product_id();
//         $quantity = $item->get_quantity();

//         // Update inventory based on product sold
//         if ($product_id == $product_id_1) {
//             $current_stock_1 = get_post_meta($product_id_1, '_stock', true);
//             update_post_meta($product_id_2, '_stock', $current_stock_1);
//         } elseif ($product_id == $product_id_2) {
//             $current_stock_2 = get_post_meta($product_id_2, '_stock', true);
//             update_post_meta($product_id_1, '_stock', $current_stock_2);
//         }
//     }
// }

// // Hook into order completion to trigger synchronization
// add_action('woocommerce_thankyou', 'sync_inventory_on_order_completion', 10, 1);


?>