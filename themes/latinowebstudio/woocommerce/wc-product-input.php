<?php

// Step 1: Add Custom Field to Product General Tab
function custom_product_field() {
    // Print HTML for your custom field
    ?>
    <div class="options_group">
        <?php
        woocommerce_wp_text_input(
            array(
                'id' => 'product_url_origin',
                'label' => __('Product Origin URL', 'woocommerce'),
                'placeholder' => __('Enter product origin here', 'woocommerce'),
				'type'=>'url',
                'desc_tip' => 'true',
                'description' => __('Enter your product origin description here.', 'woocommerce')
            )
        );
        ?>
    </div>
    <?php
}
add_action('woocommerce_product_options_general_product_data', 'custom_product_field');

// Step 2: Save Custom Field Data
function save_custom_product_field($product_id) {
    // Save custom field data
    $custom_field = isset($_POST['product_url_origin']) ? sanitize_text_field($_POST['product_url_origin']) : '';
    update_post_meta($product_id, 'product_url_origin', $custom_field);
}
add_action('woocommerce_process_product_meta', 'save_custom_product_field');

add_action('cs_framework_options', 'custom_cs_framework_options');
function custom_cs_framework_options($options){
    $options = array(); // Initialize the options array

    // Add your Codestar option fields here
    $options[] = array(
        'id'      => 'opt-text',
        'type'    => 'text',
        'title'   => 'Text',
        'default' => 'Hello world.'
    );

    return $options; // Return the modified options array
}

?>