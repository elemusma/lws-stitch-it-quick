<?php
// echo 'hello';
// Hook to add custom fields to the WooCommerce product data tabs
add_action('woocommerce_product_options_general_product_data', 'custom_product_file_upload_field');

function custom_product_file_upload_field() {
    echo '<div class="options_group">';

    woocommerce_wp_file_input(array(
        'id'            => '_custom_file_upload',
        'label'         => __('Custom File Upload', 'woocommerce'),
        'description'   => __('Upload a file for this product.', 'woocommerce'),
        'placeholder'   => '',
        'required'      => false,
        'button_label'  => __('Choose File', 'woocommerce')
    ));

    echo '</div>';
}

// Save custom field value
add_action('woocommerce_process_product_meta', 'save_custom_product_file_upload_field');

function save_custom_product_file_upload_field($post_id) {
    $file_field = $_FILES['_custom_file_upload'];

    if ($file_field && isset($file_field['name'])) {
        $upload = wp_upload_bits($file_field['name'], null, file_get_contents($file_field['tmp_name']));

        if (!empty($upload['error'])) {
            wc_add_notice(__('File upload error: ', 'woocommerce') . $upload['error'], 'error');
        } else {
            update_post_meta($post_id, '_custom_file_upload', $upload);
        }
    }
}

?>