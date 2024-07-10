<?php
/**
 * Handle Woocommerce bundled products on cart and order page while calculate shipping and generate label 
 * Plugin link : https://woocommerce.com/products/product-bundles/
 */

// Skip the bundled products while generating the packages on order page

if( !function_exists('ph_ups_woocommerce_bundle_product_support_on_generate_label') ) {

	function ph_ups_woocommerce_bundle_product_support_on_generate_label( $package, $order) {

		if ( is_a( $order, 'wf_order' ) ) $order = wc_get_order( $order->get_id() );

		if( is_a( $order, 'WC_Order' ) ) {

			$line_items 	= $order->get_items();
			$package 		= array();

			foreach( $line_items as $line_item ) {

				if( is_a($line_item, 'WC_Order_Item_Product') ) {

					$require_shipping 	= $line_item->get_meta('_bundled_item_needs_shipping');

					if( empty($require_shipping) || $require_shipping == 'yes' ) {

						$product 	= $line_item->get_product();

						if( is_a( $product, 'WC_Product') ) {

							if($product->needs_shipping()) {

								// Check whether Price Individually set or not in case of bundle products,
								$priced_individually = $line_item->get_meta('_bundled_item_priced_individually');

								if( $priced_individually == 'yes' ) {

									$product_price = ( (float) $line_item->get_total() ) / $line_item->get_quantity();

									$product->set_price($product_price);
								}
								
								$product_id 	= $product->get_id();

								if( ! isset($package[$product_id])) {

									$package[$product_id] = array(
										'data'		=>	$product,
										'quantity'	=>	$line_item->get_quantity(),
									);

								} else {

									$package[$product_id]['quantity'] += $line_item->get_quantity();
								}
							}

						} else {
							$deleted_products[] = $line_item->get_name();
						}
					}
				}
			}
		}

		if( ! empty($deleted_products) && is_admin() && ! is_ajax() && class_exists('WC_Admin_Meta_Boxes') ) {

			WC_Admin_Meta_Boxes::add_error( __( "UPS Warning! One or more Ordered Products have been deleted from the Order. Please check these Products- ", 'ups-woocommerce-shipping' ).implode( ',', $deleted_products ).'.' );
		}
		
		return $package;
	}
}

add_filter( 'xa_ups_get_customized_package_items_from_order', 'ph_ups_woocommerce_bundle_product_support_on_generate_label', 9, 2 );

// End of skip bundled products and external products while generating the packages